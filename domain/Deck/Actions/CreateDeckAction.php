<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class CreateDeckAction
{
    public function execute(
        string $name,
        ?string $description = null,
        bool $is_active = true,
        ?int $created_by = null,
        ?string $image_path = null,
        array $categoryIds = []
    ): DeckData {
        $deck = Deck::create([
            'name' => $name,
            'description' => $description,
            'image_path' => $image_path,
            'is_active' => $is_active,
            'created_by' => $created_by,
        ]);

        // Sync categories
        if (!empty($categoryIds)) {
            $deck->categories()->sync($categoryIds);
        }

        return DeckData::fromModel($deck->load('categories'));
    }
}
