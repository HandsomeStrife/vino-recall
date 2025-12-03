<?php

declare(strict_types=1);

use App\Livewire\Admin\UserManagement;
use Domain\User\Models\User;
use Livewire\Livewire;

test('admin can view user management page', function () {
    $admin = actingAsAdmin();

    Livewire::test(UserManagement::class)
        ->assertStatus(200)
        ->assertSee('User Management');
});

test('user management displays list of users', function () {
    $admin = actingAsAdmin();
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    Livewire::test(UserManagement::class)
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('Jane Smith')
        ->assertSee('jane@example.com');
});

test('non-admin cannot access user management', function () {
    $user = actingAsUser();

    $response = $this->get(route('admin.users'));
    $response->assertRedirect(route('admin.login'));
});

test('guest cannot access user management', function () {
    $response = $this->get(route('admin.users'));
    $response->assertRedirect(route('admin.login'));
});

test('user management shows users', function () {
    $admin = actingAsAdmin();
    User::factory()->count(5)->create();

    Livewire::test(UserManagement::class)
        ->assertSee('User');
});

