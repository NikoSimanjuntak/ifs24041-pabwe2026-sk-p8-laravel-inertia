<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_current_data(): void
    {
        $user = User::factory()->create(['name' => 'Ani']);

        $this->actingAs($user)->get('/profile')->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Profile/Edit')->where('profile.name', 'Ani'));
    }

    public function test_profile_can_be_updated_with_and_without_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        $this->actingAs($user)->put('/profile', ['name' => 'Baru', 'username' => 'baru_1', 'email' => 'baru@example.com'])->assertRedirect();
        $user->refresh();
        $this->assertSame('Baru', $user->name);
        $this->assertTrue(Hash::check('lama12345', $user->password));

        $this->actingAs($user)->put('/profile', [
            'name' => 'Baru', 'username' => 'baru_1', 'email' => 'baru@example.com',
            'password' => 'sandibaru1', 'password_confirmation' => 'sandibaru1',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('sandibaru1', $user->refresh()->password));
    }

    public function test_profile_validates_uniqueness(): void
    {
        User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => 'dipakai', 'email' => 'dipakai@example.com'])
            ->assertSessionHasErrors(['username', 'email']);
    }
}
