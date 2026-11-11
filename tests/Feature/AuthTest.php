<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_member_can_login_with_username(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia123']);

        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123', 'remember' => true])
            ->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_admin_is_redirected_to_admin_panel(): void
    {
        User::factory()->admin()->create(['username' => 'root', 'password' => 'rahasia123']);

        $this->post('/login', ['username' => 'root', 'password' => 'rahasia123'])->assertRedirect('/admin/users');
    }

    public function test_login_fails_with_wrong_credentials_or_missing_fields(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia123']);

        $this->post('/login', ['username' => 'budi', 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Siti', 'username' => 'siti_01', 'email' => 'siti@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard/posts');

        $this->assertDatabaseHas('users', ['username' => 'siti_01', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_registration_validates_input(): void
    {
        User::factory()->create(['username' => 'dipakai']);

        $this->post('/register', ['name' => '', 'username' => 'dipakai', 'email' => 'x', 'password' => '123'])
            ->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login_for_protected_pages(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard/posts');
        auth()->logout();
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect('/admin/users');
    }
}
