<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class CreateDeckAction
{
    public function execute(string $name, ?string $description = null, bool $is_active = true, ?int $created_by = null, ?string $category = null): DeckData
    {
        $deck = Deck::create([
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'is_active' => $is_active,
            'created_by' => $created_by,
        ]);

        return DeckData::fromModel($deck);
    }
}
