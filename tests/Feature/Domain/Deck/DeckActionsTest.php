<?php

declare(strict_types=1);

use Domain\Deck\Actions\CreateDeckAction;
use Domain\Deck\Actions\DeleteDeckAction;
use Domain\Deck\Actions\UpdateDeckAction;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('create deck action creates a new deck', function () {
    $user = User::factory()->create();
    $action = new CreateDeckAction;

    $deckData = $action->execute(
        name: 'Test Deck',
        description: 'Test Description',
        is_active: true,
        created_by: $user->id
    );

    expect($deckData->name)->toBe('Test Deck');
    expect($deckData->description)->toBe('Test Description');
    expect($deckData->is_active)->toBeTrue();
    expect($deckData->created_by)->toBe($user->id);

    $deck = Deck::find($deckData->id);
    expect($deck)->not->toBeNull();
});

test('update deck action updates deck properties', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['name' => 'Original Name']);
    $action = new UpdateDeckAction;

    $deckData = $action->execute(
        deckId: $deck->id,
        name: 'Updated Name',
        description: 'Updated Description'
    );

    expect($deckData->name)->toBe('Updated Name');
    expect($deckData->description)->toBe('Updated Description');

    $deck->refresh();
    expect($deck->name)->toBe('Updated Name');
});

test('delete deck action removes deck', function () {
    $deck = Deck::factory()->create();
    $action = new DeleteDeckAction;

    $action->execute($deck->id);

    expect(Deck::find($deck->id))->toBeNull();
});
