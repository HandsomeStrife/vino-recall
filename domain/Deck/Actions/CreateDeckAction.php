<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Exceptions\DeckHierarchyException;
use Domain\Deck\Models\Deck;

class CreateDeckAction
{
    public function execute(
        string $name,
        ?string $description = null,
        bool $is_active = true,
        ?int $created_by = null,
        ?string $image_path = null,
        array $categoryIds = [],
        ?int $parent_deck_id = null,
        bool $is_collection = false
    ): DeckData {
        // Collections cannot have a parent
        if ($is_collection && $parent_deck_id !== null) {
            throw DeckHierarchyException::collectionCannotHaveParent();
        }

        // Validate parent deck if provided
        if ($parent_deck_id !== null) {
            $parentDeck = Deck::find($parent_deck_id);
            
            if ($parentDeck === null) {
                throw new \InvalidArgumentException('Parent deck not found.');
            }

            // Parent deck must be a collection
            if (!$parentDeck->is_collection) {
                throw DeckHierarchyException::parentMustBeCollection();
            }
        }

        $deck = Deck::create([
            'name' => $name,
            'description' => $description,
            'image_path' => $image_path,
            'is_active' => $is_active,
            'is_collection' => $is_collection,
            'created_by' => $created_by,
            'parent_deck_id' => $parent_deck_id,
        ]);

        // Sync categories
        if (!empty($categoryIds)) {
            $deck->categories()->sync($categoryIds);
        }

        return DeckData::fromModel($deck->load(['categories', 'parent', 'children']));
    }
}
