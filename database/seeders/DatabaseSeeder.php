<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@blogverse.test', 'password' => 'admin12345',
        ]);

        $writer = User::factory()->create([
            'name' => 'Penulis Demo', 'username' => 'penulis', 'email' => 'penulis@blogverse.test', 'password' => 'penulis12345',
        ]);

        Post::factory()->count(8)->for($writer)->create();
        Post::factory()->count(3)->draft()->for($writer)->create();
    }
}
