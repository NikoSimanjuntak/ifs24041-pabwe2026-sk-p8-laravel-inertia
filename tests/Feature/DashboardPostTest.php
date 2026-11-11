<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class DashboardPostTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function payload(array $extra = []): array
    {
        return array_merge(['title' => 'Judul Baru', 'category' => 'Teknologi', 'excerpt' => 'Ringkas.', 'content' => '# Isi'], $extra);
    }

    public function test_index_shows_own_posts_with_statistics(): void
    {
        Post::factory()->count(2)->for($this->user)->create();
        Post::factory()->draft()->for($this->user)->create(['title' => 'Draf Khusus']);
        Post::factory()->create();

        $this->actingAs($this->user)->get('/dashboard/posts')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Post/Index')->has('posts.data', 3)
            ->where('stats', ['total' => 3, 'published' => 2, 'draft' => 1]));

        $this->actingAs($this->user)->get('/dashboard/posts?search=khusus')
            ->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
    }

    public function test_store_creates_draft_or_published_post_with_unique_slug(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload())->assertRedirect();
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['is_published' => true]))->assertRedirect();

        $posts = Post::orderBy('id')->get();
        $this->assertSame('judul-baru', $posts[0]->slug);
        $this->assertSame('judul-baru-2', $posts[1]->slug);
        $this->assertFalse($posts[0]->is_published);
        $this->assertNull($posts[0]->published_at);
        $this->assertTrue($posts[1]->is_published);
        $this->assertNotNull($posts[1]->published_at);
    }

    public function test_store_accepts_cover_and_validates(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['cover' => UploadedFile::fake()->image('c.jpg')]))->assertRedirect();
        Storage::disk('public')->assertExists(Post::first()->cover_image_path);

        $this->actingAs($this->user)->post('/dashboard/posts', ['category' => 'Salah'])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_show_update_and_ownership(): void
    {
        $post = Post::factory()->draft()->for($this->user)->create();

        $this->actingAs($this->user)->get("/dashboard/posts/{$post->id}")->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Post/Show')->where('post.id', $post->id));

        $this->actingAs($this->user)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => true, 'cover' => UploadedFile::fake()->image('a.png')]))->assertRedirect();
        $post->refresh();
        $this->assertSame('Judul Baru', $post->title);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->cover_image_path);

        // tanpa is_published: status lama dipertahankan
        $this->actingAs($this->user)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => 'Ubah Lagi']))->assertRedirect();
        $this->assertTrue($post->refresh()->is_published);

        // menjadikan draf
        $this->actingAs($this->user)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => false]))->assertRedirect();
        $this->assertNull($post->refresh()->published_at);
    }

    public function test_other_users_cannot_access_a_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->user)->get("/dashboard/posts/{$post->id}")->assertForbidden();
        $this->actingAs($this->user)->put("/dashboard/posts/{$post->id}", $this->payload())->assertForbidden();
        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}")->assertForbidden();
        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertForbidden();
        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover")->assertForbidden();
        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}/cover")->assertForbidden();
    }

    public function test_admin_cannot_use_member_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/posts')->assertForbidden();
    }

    public function test_destroy_removes_post_and_cover(): void
    {
        $post = Post::factory()->for($this->user)->create(['cover_image_path' => 'covers/x.jpg']);
        Storage::disk('public')->put('covers/x.jpg', 'img');

        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}")->assertRedirect('/dashboard/posts');

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing('covers/x.jpg');
    }

    public function test_publish_toggles_status(): void
    {
        $post = Post::factory()->draft()->for($this->user)->create();

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertRedirect();
        $this->assertTrue($post->refresh()->is_published);

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertRedirect();
        $this->assertFalse($post->refresh()->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_cover_can_be_uploaded_replaced_and_removed(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('1.jpg')])->assertRedirect();
        $first = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($first);
        $this->assertNotNull($post->coverUrl());

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('2.png')])->assertRedirect();
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => 'bukan-gambar'])->assertSessionHasErrors('cover');

        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}/cover")->assertRedirect();
        $this->assertNull($post->refresh()->cover_image_path);

        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}/cover")->assertRedirect();
    }

    public function test_ai_generate_returns_items_and_handles_errors(): void
    {
        $this->app->bind(AiClientInterface::class, fn () => new class implements AiClientInterface
        {
            public function generate(string $topic, int $quantity, ?string $category = null): array
            {
                return [new GeneratedPostItem('Judul AI', 'Teknologi', 'Ringkas', '# Isi')];
            }
        });

        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'Laravel', 'quantity' => 1, 'category' => 'Teknologi'])
            ->assertOk()->assertJsonPath('items.0.title', 'Judul AI');

        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'Laravel', 'quantity' => 9])
            ->assertStatus(422)->assertJsonValidationErrors('quantity');

        $this->app->bind(AiClientInterface::class, fn () => new class implements AiClientInterface
        {
            public function generate(string $topic, int $quantity, ?string $category = null): array
            {
                throw new RuntimeException('Gagal AI');
            }
        });

        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'Laravel', 'quantity' => 2])
            ->assertStatus(422)->assertJsonPath('message', 'Gagal AI');
    }

    public function test_ai_save_stores_batch_as_drafts(): void
    {
        $items = [
            ['title' => 'A', 'category' => 'Umum', 'excerpt' => 'x', 'content' => 'isi a'],
            ['title' => 'B', 'category' => 'Opini', 'content' => 'isi b'],
        ];

        $this->actingAs($this->user)->post('/dashboard/posts/ai-save', ['items' => $items])->assertRedirect();
        $this->assertSame(2, Post::where('user_id', $this->user->id)->where('is_published', false)->count());

        $this->actingAs($this->user)->post('/dashboard/posts/ai-save', ['items' => []])->assertSessionHasErrors('items');
    }
}
