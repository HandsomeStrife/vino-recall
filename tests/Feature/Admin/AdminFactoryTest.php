<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;

test('admin factory creates admin with valid data', function () {
    $admin = Admin::factory()->create();

    expect($admin)
        ->toBeInstanceOf(Admin::class)
        ->and($admin->name)->toBeString()
        ->and($admin->email)->toBeString()
        ->and($admin->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($admin->password)->toBeString()
        ->and($admin->remember_token)->toBeString();
});

test('admin factory can create unverified admin', function () {
    $admin = Admin::factory()->unverified()->create();

    expect($admin->email_verified_at)->toBeNull();
});

test('admin factory creates unique emails', function () {
    $admin1 = Admin::factory()->create();
    $admin2 = Admin::factory()->create();

    expect($admin1->email)->not->toBe($admin2->email);
});

test('admin factory can create multiple admins', function () {
    $admins = Admin::factory()->count(5)->create();

    expect($admins)->toHaveCount(5)
        ->and($admins->first())->toBeInstanceOf(Admin::class);
});

test('admin factory can create admin with specific attributes', function () {
    $admin = Admin::factory()->create([
        'name' => 'Specific Admin',
        'email' => 'specific@test.com',
    ]);

    expect($admin->name)->toBe('Specific Admin')
        ->and($admin->email)->toBe('specific@test.com');
});

test('admin factory hashes password', function () {
    $admin = Admin::factory()->create();

    // Password should be hashed, not plain text
    expect($admin->password)->not->toBe('password')
        ->and(strlen($admin->password))->toBeGreaterThan(20);
});

test('admin factory password can authenticate', function () {
    $admin = Admin::factory()->create();

    // Default factory password is 'password'
    expect(\Illuminate\Support\Facades\Hash::check('password', $admin->password))->toBeTrue();
});

