<?php

declare(strict_types=1);

use Domain\User\Actions\RegisterUserAction;

test('register user action creates a new user', function () {
    $action = new RegisterUserAction;

    $userData = $action->execute(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
    );

    expect($userData->name)->toBe('Test User');
    expect($userData->email)->toBe('test@example.com');

    $user = \Domain\User\Models\User::find($userData->id);
    expect($user)->not->toBeNull();
    expect(\Illuminate\Support\Facades\Hash::check('password', $user->password))->toBeTrue();
});

test('register user action hashes password', function () {
    $action = new RegisterUserAction;

    $userData = $action->execute(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
    );

    $user = \Domain\User\Models\User::find($userData->id);
    
    // Password should be hashed
    expect($user->password)->not->toBe('password')
        ->and(\Illuminate\Support\Facades\Hash::check('password', $user->password))->toBeTrue();
});

test('register user action returns user data', function () {
    $action = new RegisterUserAction;

    $userData = $action->execute(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
    );

    expect($userData)->toBeInstanceOf(\Domain\User\Data\UserData::class);
});
