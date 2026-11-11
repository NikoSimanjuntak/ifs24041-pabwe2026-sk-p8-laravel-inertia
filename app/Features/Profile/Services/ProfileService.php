<?php

namespace App\Features\Profile\Services;

use App\Models\User;

class ProfileService
{
    /** @param array{name: string, username: string, email: string, password?: ?string} $data */
    public function update(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user;
    }
}
