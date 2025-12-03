<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(string $name, string $email, string $password): UserData
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        return UserData::fromModel($user);
    }
}
