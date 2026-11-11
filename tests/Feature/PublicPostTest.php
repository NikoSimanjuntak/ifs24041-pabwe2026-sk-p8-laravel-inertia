<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_only_published_posts(): void
    {
        Post::factory()->count(2)->create();
        Post::factory()->draft()->create();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostIndex')->has('posts.data', 2)->has('categories', 6));
    }

    public function test_articles_can_be_searched_and_filtered(): void
    {
        Post::factory()->create(['title' => 'Belajar Laravel', 'category' => 'Teknologi']);
        Post::factory()->create(['title' => 'Resep Kopi', 'content' => 'seduh kopi', 'category' => 'Gaya Hidup']);

        $this->get('/articles?search=laravel')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->get('/articles?search=SEDUH')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->get('/articles?category=Gaya Hidup')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
    }

    public function test_published_article_can_be_read_by_guest(): void
    {
        $post = Post::factory()->create();

        $this->get('/articles/'.$post->slug)->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostShow')->where('post.title', $post->title)->has('post.content'));
    }

    public function test_draft_or_unknown_article_returns_404(): void
    {
        $draft = Post::factory()->draft()->create();

        $this->get('/articles/'.$draft->slug)->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }

    public function test_flash_messages_and_auth_user_are_shared(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['success' => 'Halo', 'error' => 'Oops'])->get('/')
            ->assertInertia(fn (Assert $p) => $p->where('flash.success', 'Halo')->where('flash.error', 'Oops')
                ->where('auth.user.username', $user->username));
    }
}
