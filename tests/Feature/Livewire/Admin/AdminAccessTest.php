<?php

declare(strict_types=1);

test('admin routes are accessible to admin users', function () {
    $admin = actingAsAdmin();

    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);

    $response = $this->get(route('admin.users'));
    $response->assertStatus(200);

    $response = $this->get(route('admin.decks'));
    $response->assertStatus(200);

    $response = $this->get(route('admin.cards'));
    $response->assertStatus(200);
});

test('admin routes are not accessible to regular users', function () {
    $user = actingAsUser();

    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));
});

test('admin routes redirect guests to admin login', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));
});
