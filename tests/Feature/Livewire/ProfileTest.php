<?php

declare(strict_types=1);

use App\Livewire\Profile;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('profile component can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->assertStatus(200)
        ->assertSee('Profile', false);
});

test('profile component loads user information', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->assertSet('name', 'John Doe')
        ->assertSet('email', 'john@example.com');
});

test('user can update profile information', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com');
});

test('profile update validates name is required', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->set('name', '')
        ->set('email', 'test@example.com')
        ->call('updateProfile')
        ->assertHasErrors(['name' => 'required']);
});

test('profile update validates email is required', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', '')
        ->call('updateProfile')
        ->assertHasErrors(['email' => 'required']);
});

test('profile update validates email format', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'invalid-email')
        ->call('updateProfile')
        ->assertHasErrors(['email' => 'email']);
});

test('user can update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('password update validates current password is required', function () {
    $user = actingAsUser();

    Livewire::test(Profile::class)
        ->set('current_password', '')
        ->set('password', 'newpassword')
        ->set('password_confirmation', 'newpassword')
        ->call('updatePassword')
        ->assertHasErrors(['current_password' => 'required']);
});

test('password update validates current password is correct', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correctpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'wrongpassword')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('updatePassword')
        ->assertHasErrors(['current_password' => 'Current password is incorrect.']);
});

test('password update validates new password is required', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('updatePassword')
        ->assertHasErrors(['password' => 'required']);
});

test('password update validates new password minimum length', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('password', 'short')
        ->set('password_confirmation', 'short')
        ->call('updatePassword')
        ->assertHasErrors(['password' => 'min']);
});

test('password update validates password confirmation matches', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'differentpassword')
        ->call('updatePassword')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('password fields are cleared after successful update', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('updatePassword')
        ->assertSet('current_password', '')
        ->assertSet('password', '')
        ->assertSet('password_confirmation', '');
});

test('profile displays subscription when user has active subscription', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create([
        'name' => 'Premium Plan',
        'price' => 19.99,
    ]);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_end' => now()->addMonth(),
    ]);

    Livewire::test(Profile::class)
        ->assertSee('Premium Plan')
        ->assertSee('Active')
        ->assertSee('Renews:');
});

test('profile displays plans when user has no subscription', function () {
    $user = actingAsUser();
    Plan::factory()->create(['name' => 'Basic Plan', 'price' => 9.99]);
    Plan::factory()->create(['name' => 'Premium Plan', 'price' => 19.99]);

    Livewire::test(Profile::class)
        ->assertSee('You don', false)
        ->assertSee('Basic Plan')
        ->assertSee('Premium Plan');
});

test('profile displays subscription status badge with correct variant', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create(['name' => 'Test Plan']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'past_due',
    ]);

    Livewire::test(Profile::class)
        ->assertSee('Past_due');
});

