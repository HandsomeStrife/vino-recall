<?php

declare(strict_types=1);

use Domain\Admin\Data\AdminData;
use Domain\Admin\Models\Admin;
use Domain\Admin\Repositories\AdminRepository;

test('admin repository can find admin by email', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
    ]);

    $repository = new AdminRepository();
    $result = $repository->findByEmail('admin@test.com');

    expect($result)
        ->toBeInstanceOf(AdminData::class)
        ->and($result->id)->toBe($admin->id)
        ->and($result->email)->toBe('admin@test.com');
});

test('admin repository returns null for non-existent email', function () {
    $repository = new AdminRepository();
    $result = $repository->findByEmail('nonexistent@test.com');

    expect($result)->toBeNull();
});

test('admin repository can find admin by id', function () {
    $admin = Admin::factory()->create();

    $repository = new AdminRepository();
    $result = $repository->findById($admin->id);

    expect($result)
        ->toBeInstanceOf(AdminData::class)
        ->and($result->id)->toBe($admin->id);
});

test('admin repository returns null for non-existent id', function () {
    $repository = new AdminRepository();
    $result = $repository->findById(99999);

    expect($result)->toBeNull();
});

test('admin repository can get logged in admin', function () {
    $admin = actingAsAdmin();

    $repository = new AdminRepository();
    $result = $repository->getLoggedInAdmin();

    expect($result)
        ->toBeInstanceOf(AdminData::class)
        ->and($result->id)->toBe($admin->id);
});

test('admin repository throws exception when no admin is logged in', function () {
    $repository = new AdminRepository();
    $repository->getLoggedInAdmin();
})->throws(RuntimeException::class, 'No authenticated admin found');

test('admin repository can get all admins', function () {
    Admin::factory()->count(3)->create();

    $repository = new AdminRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(AdminData::class);
});

test('admin repository returns empty collection when no admins exist', function () {
    $repository = new AdminRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(0);
});

test('admin data correctly maps from model', function () {
    $admin = Admin::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
    ]);

    $adminData = AdminData::fromModel($admin);

    expect($adminData)
        ->toBeInstanceOf(AdminData::class)
        ->and($adminData->id)->toBe($admin->id)
        ->and($adminData->name)->toBe('Test Admin')
        ->and($adminData->email)->toBe('admin@test.com')
        ->and($adminData->created_at)->toBeString()
        ->and($adminData->updated_at)->toBeString();
});

