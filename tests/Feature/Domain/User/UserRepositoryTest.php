<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Repositories\UserRepository;

test('get logged in user returns authenticated user', function () {
    $user = actingAsUser();
    $repository = new UserRepository;

    $userData = $repository->getLoggedInUser();

    expect($userData->id)->toBe($user->id);
    expect($userData->email)->toBe($user->email);
});

test('get logged in user throws exception when not authenticated', function () {
    $repository = new UserRepository;

    expect(fn () => $repository->getLoggedInUser())
        ->toThrow(\Illuminate\Auth\AuthenticationException::class);
});

test('find by id returns user data', function () {
    $user = User::factory()->create();
    $repository = new UserRepository;

    $userData = $repository->findById($user->id);

    expect($userData)->not->toBeNull();
    expect($userData->id)->toBe($user->id);
});

test('find by id returns null for non-existent user', function () {
    $repository = new UserRepository;

    $userData = $repository->findById(99999);

    expect($userData)->toBeNull();
});

test('find by email returns user data', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $repository = new UserRepository;

    $userData = $repository->findByEmail('test@example.com');

    expect($userData)->not->toBeNull();
    expect($userData->email)->toBe('test@example.com');
});

test('get all returns collection of user data', function () {
    User::factory()->count(3)->create();
    $repository = new UserRepository;

    $users = $repository->getAll();

    expect($users)->toHaveCount(3);
    expect($users->first())->toBeInstanceOf(\Domain\User\Data\UserData::class);
});
