<?php

declare(strict_types=1);

use Domain\User\Actions\UpdateUserAction;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

test('update user action can update name', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        name: 'Updated Name'
    );

    expect($result->name)->toBe('Updated Name');
    
    $user->refresh();
    expect($user->name)->toBe('Updated Name');
});

test('update user action can update email', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        email: 'new@example.com'
    );

    expect($result->email)->toBe('new@example.com');
    
    $user->refresh();
    expect($user->email)->toBe('new@example.com');
});

test('update user action can update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);

    $action = new UpdateUserAction();
    $action->execute(
        userId: $user->id,
        password: 'newpassword123'
    );

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('update user action can update locale', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        locale: 'es'
    );

    expect($result->locale)->toBe('es');
    
    $user->refresh();
    expect($user->locale)->toBe('es');
});

test('update user action can update multiple fields at once', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'locale' => 'en',
    ]);

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        name: 'New Name',
        email: 'new@example.com',
        locale: 'es'
    );

    expect($result->name)->toBe('New Name')
        ->and($result->email)->toBe('new@example.com')
        ->and($result->locale)->toBe('es');
});

test('update user action leaves unchanged fields intact', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        name: 'Updated Name'
    );

    expect($result->name)->toBe('Updated Name')
        ->and($result->email)->toBe('original@example.com');
});

test('update user action returns user data object', function () {
    $user = User::factory()->create();

    $action = new UpdateUserAction();
    $result = $action->execute(
        userId: $user->id,
        name: 'Test Name'
    );

    expect($result)->toBeInstanceOf(\Domain\User\Data\UserData::class);
});

test('update user action hashes password before storing', function () {
    $user = User::factory()->create();

    $action = new UpdateUserAction();
    $action->execute(
        userId: $user->id,
        password: 'plainpassword'
    );

    $user->refresh();
    
    // Password should be hashed, not stored as plain text
    expect($user->password)->not->toBe('plainpassword')
        ->and(Hash::check('plainpassword', $user->password))->toBeTrue();
});

test('update user action handles user with minimal changes', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $action = new UpdateUserAction();
    $result = $action->execute(userId: $user->id);

    // Should return the user even if no changes made
    expect($result)->toBeInstanceOf(\Domain\User\Data\UserData::class)
        ->and($result->name)->toBe('Test User')
        ->and($result->email)->toBe('test@example.com');
});

