<?php

namespace Tests\Unit;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_falls_back_when_title_has_no_letters(): void
    {
        $user = User::factory()->create();
        $service = app(PostService::class);

        $a = $service->create($user, ['title' => '!!!', 'category' => 'Umum', 'content' => 'x']);
        $b = $service->create($user, ['title' => '???', 'category' => 'Umum', 'content' => 'x']);

        $this->assertSame('artikel', $a->slug);
        $this->assertSame('artikel-2', $b->slug);
    }

    public function test_public_listing_and_detail(): void
    {
        $post = Post::factory()->create();
        $service = app(PostService::class);

        $this->assertSame(1, $service->publicList(null, null)->total());
        $this->assertTrue($service->publicDetail($post->slug)->is($post));
        $this->assertNull($service->publicDetail('x'));
    }

    public function test_post_summary_and_cover_url(): void
    {
        $post = Post::factory()->create(['cover_image_path' => 'covers/a.jpg']);

        $this->assertArrayNotHasKey('content', $post->toSummary());
        $this->assertArrayHasKey('content', $post->toSummary(true));
        $this->assertStringEndsWith('storage/covers/a.jpg', $post->coverUrl());
        $this->assertNull(Post::factory()->create()->coverUrl());
    }

    public function test_policy_and_user_role_helpers(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $policy = new PostPolicy;

        $this->assertTrue($policy->manage($owner, $post));
        $this->assertFalse($policy->manage(User::factory()->create(), $post));
        $this->assertFalse($owner->isAdmin());
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
        $this->assertCount(1, $owner->posts);
    }
}
