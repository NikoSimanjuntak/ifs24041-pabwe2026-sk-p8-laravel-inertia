<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama', 'username' => 'adminutama']);
    }

    public function test_admin_lists_and_searches_users(): void
    {
        $writer = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi']);
        Post::factory()->count(2)->for($writer)->create();

        $this->actingAs($this->admin)->get('/admin/users')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Admin/UserList')->has('users.data', 2));

        $this->actingAs($this->admin)->get('/admin/users?search=BUDI')->assertInertia(fn (Assert $p) => $p
            ->has('users.data', 1)->where('users.data.0.posts_count', 2));
    }

    public function test_admin_monitors_posts_of_a_user(): void
    {
        $writer = User::factory()->create();
        Post::factory()->for($writer)->create(['title' => 'Cari Aku']);
        Post::factory()->for($writer)->create();

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts")->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Admin/UserPostList')->has('posts.data', 2)->where('owner.id', $writer->id));

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts?search=cari")
            ->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
    }

    public function test_admin_monitors_all_posts_read_only(): void
    {
        Post::factory()->count(3)->create();
        Post::factory()->draft()->create(['title' => 'Draf Rahasia']);

        $this->actingAs($this->admin)->get('/admin/posts')->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Admin/PostList')->has('posts.data', 4));
        $this->actingAs($this->admin)->get('/admin/posts?search=rahasia')
            ->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));

        $post = Post::first();
        $this->actingAs($this->admin)->put("/dashboard/posts/{$post->id}", [])->assertForbidden();
    }

    public function test_members_cannot_access_admin_area(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts/export')->assertForbidden();
    }
}
