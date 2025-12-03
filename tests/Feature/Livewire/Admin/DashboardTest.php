<?php

declare(strict_types=1);

use App\Livewire\Admin\Dashboard;
use Domain\Card\Models\Card;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Livewire\Livewire;

test('admin can view dashboard', function () {
    $admin = actingAsAdmin();

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Admin Dashboard');
});

test('admin dashboard displays user count', function () {
    $admin = actingAsAdmin();
    User::factory()->count(5)->create();

    Livewire::test(Dashboard::class)
        ->assertSee('Users')
        ->assertSee('6'); // 5 + 1 admin
});

test('admin dashboard displays deck count', function () {
    $admin = actingAsAdmin();
    Deck::factory()->count(3)->create();

    Livewire::test(Dashboard::class)
        ->assertSee('Decks')
        ->assertSee('3');
});

test('admin dashboard displays card count', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    Card::factory()->count(10)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('Cards')
        ->assertSee('10');
});

test('admin dashboard displays zero counts when no data', function () {
    $admin = actingAsAdmin();

    Livewire::test(Dashboard::class)
        ->assertSee('0');
});

test('non-admin cannot access admin dashboard', function () {
    $user = actingAsUser();

    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(403);
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('admin dashboard displays all statistics together', function () {
    $admin = actingAsAdmin();
    User::factory()->count(2)->create();
    Deck::factory()->count(3)->create();
    $deck = Deck::factory()->create();
    Card::factory()->count(15)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('Users')
        ->assertSee('Decks')
        ->assertSee('Cards');
});

