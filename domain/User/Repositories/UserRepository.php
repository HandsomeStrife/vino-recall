<?php

declare(strict_types=1);

namespace Domain\User\Repositories;

use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRepository
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getLoggedInUser(): UserData
    {
        $user = Auth::user();

        if ($user === null) {
            throw new \Illuminate\Auth\AuthenticationException('User is not authenticated.');
        }

        return UserData::fromModel($user);
    }

    public function findById(int $id): ?UserData
    {
        $user = User::find($id);

        if ($user === null) {
            return null;
        }

        return UserData::fromModel($user);
    }

    /**
     * @return \Illuminate\Support\Collection<int, UserData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return User::all()->map(fn (User $user) => UserData::fromModel($user));
    }

    public function findByEmail(string $email): ?UserData
    {
        $user = User::where('email', $email)->first();

        if ($user === null) {
            return null;
        }

        return UserData::fromModel($user);
    }
}
