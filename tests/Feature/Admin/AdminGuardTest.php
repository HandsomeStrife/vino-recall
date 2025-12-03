<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;

use function Pest\Laravel\get;

test('admin routes require admin authentication', function () {
    get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    get(route('admin.users'))
        ->assertRedirect(route('admin.login'));

    get(route('admin.decks'))
        ->assertRedirect(route('admin.login'));

    get(route('admin.cards'))
        ->assertRedirect(route('admin.login'));

    get(route('admin.decks.import'))
        ->assertRedirect(route('admin.login'));
});

test('authenticated admin can access admin routes', function () {
    actingAsAdmin();

    get(route('admin.dashboard'))->assertOk();
    get(route('admin.users'))->assertOk();
    get(route('admin.decks'))->assertOk();
    get(route('admin.cards'))->assertOk();
    get(route('admin.decks.import'))->assertOk();
});

test('regular user cannot access admin routes', function () {
    actingAsUser();

    get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    get(route('admin.users'))
        ->assertRedirect(route('admin.login'));
});

test('admin middleware redirects to admin login', function () {
    $response = get(route('admin.dashboard'));

    $response->assertRedirect(route('admin.login'));
});

test('admin can access their own profile data through repository', function () {
    $admin = actingAsAdmin();

    $repository = new \Domain\Admin\Repositories\AdminRepository();
    $adminData = $repository->getLoggedInAdmin();

    expect($adminData->id)->toBe($admin->id)
        ->and($adminData->email)->toBe($admin->email);
});

test('user guard and admin guard are separate', function () {
    // Login as user
    $user = actingAsUser();

    // User is authenticated on web guard
    expect(auth()->guard('web')->check())->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($user->id);

    // But not on admin guard
    expect(auth()->guard('admin')->check())->toBeFalse();

    // Now login as admin (in same test to verify independence)
    $admin = Admin::factory()->create();
    auth()->guard('admin')->login($admin);

    // Both should be authenticated on their respective guards
    expect(auth()->guard('web')->check())->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($user->id)
        ->and(auth()->guard('admin')->check())->toBeTrue()
        ->and(auth()->guard('admin')->id())->toBe($admin->id);
});

