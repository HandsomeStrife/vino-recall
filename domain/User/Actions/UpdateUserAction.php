<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function execute(int $userId, ?string $name = null, ?string $email = null, ?string $password = null, ?string $locale = null): UserData
    {
        $user = User::findOrFail($userId);

        $updateData = [];

        if ($name !== null) {
            $updateData['name'] = $name;
        }

        if ($email !== null) {
            $updateData['email'] = $email;
        }

        if ($password !== null) {
            $updateData['password'] = Hash::make($password);
        }

        if ($locale !== null) {
            $updateData['locale'] = $locale;
        }

        $user->update($updateData);

        return UserData::fromModel($user->fresh());
    }
}
