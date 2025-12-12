<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Exceptions\DeckHierarchyException;
use Domain\Deck\Models\Deck;
use Illuminate\Support\Str;

class UpdateDeckAction
{
    public function execute(
        int $deckId,
        ?string $name = null,
        ?string $description = null,
        ?bool $is_active = null,
        ?string $image_path = null,
        ?array $categoryIds = null,
        ?int $parent_deck_id = null,
        bool $clear_parent = false,
        ?bool $is_collection = null
    ): DeckData {
        $deck = Deck::findOrFail($deckId);
        $previousParentId = $deck->parent_deck_id;

        $updateData = [];

        if ($name !== null) {
            $updateData['name'] = $name;
        }

        if ($description !== null) {
            $updateData['description'] = $description;
        }

        if ($is_active !== null) {
            $updateData['is_active'] = $is_active;
        }

        if ($image_path !== null) {
            $updateData['image_path'] = $image_path;
        }

        // Handle collection status change
        if ($is_collection !== null) {
            $this->validateCollectionChange($deck, $is_collection);
            $updateData['is_collection'] = $is_collection;

            // If becoming a collection, clear any parent
            if ($is_collection) {
                $updateData['parent_deck_id'] = null;
            }
        }

        // Handle parent deck assignment (only if not a collection)
        $willBeCollection = $is_collection ?? $deck->is_collection;
        $newParentId = null;

        if ($clear_parent && ! $willBeCollection) {
            $updateData['parent_deck_id'] = null;
        } elseif ($parent_deck_id !== null && ! $willBeCollection) {
            $this->validateParentAssignment($deck, $parent_deck_id);
            $updateData['parent_deck_id'] = $parent_deck_id;
            $newParentId = $parent_deck_id;
        }

        $deck->update($updateData);

        // Sync categories if provided
        if ($categoryIds !== null) {
            $deck->categories()->sync($categoryIds);
        }

        // Auto-enroll users if deck was added to a collection
        if ($newParentId !== null && $previousParentId !== $newParentId) {
            $this->autoEnrollCollectionSubscribers($deck, $newParentId);
        }

        return DeckData::fromModel($deck->fresh()->load(['categories', 'parent', 'children']));
    }

    /**
     * Auto-enroll all users subscribed to the parent collection in the new child deck.
     */
    private function autoEnrollCollectionSubscribers(Deck $childDeck, int $parentDeckId): void
    {
        $parentDeck = Deck::find($parentDeckId);

        if ($parentDeck === null) {
            return;
        }

        // Get all users enrolled in the parent collection
        $enrolledUsers = $parentDeck->enrolledUsers()->get();

        foreach ($enrolledUsers as $user) {
            // Check if user is already enrolled in this child deck
            $alreadyEnrolled = $user->enrolledDecks()->where('deck_id', $childDeck->id)->exists();

            if (! $alreadyEnrolled) {
                // Generate unique shortcode
                $shortcode = $this->generateUniqueShortcode();

                $user->enrolledDecks()->attach($childDeck->id, [
                    'enrolled_at' => now(),
                    'shortcode' => $shortcode,
                ]);
            }
        }
    }

    private function generateUniqueShortcode(): string
    {
        do {
            $shortcode = strtoupper(Str::random(8));
        } while (\DB::table('deck_user')->where('shortcode', $shortcode)->exists());

        return $shortcode;
    }

    private function validateCollectionChange(Deck $deck, bool $isCollection): void
    {
        // Can't become a collection if it has a parent
        if ($isCollection && $deck->parent_deck_id !== null) {
            throw DeckHierarchyException::collectionCannotHaveParent();
        }

        // Can't become a collection if it has cards
        if ($isCollection && $deck->cards()->exists()) {
            throw DeckHierarchyException::collectionCannotHaveCards();
        }

        // Can't stop being a collection if it has children
        if (! $isCollection && $deck->is_collection && $deck->children()->exists()) {
            throw DeckHierarchyException::cannotRemoveCollectionWithChildren();
        }
    }

    private function validateParentAssignment(Deck $deck, int $parentDeckId): void
    {
        // Cannot be own parent
        if ($deck->id === $parentDeckId) {
            throw DeckHierarchyException::cannotBeOwnParent();
        }

        // Collections cannot have a parent
        if ($deck->is_collection) {
            throw DeckHierarchyException::collectionCannotHaveParent();
        }

        $parentDeck = Deck::find($parentDeckId);

        if ($parentDeck === null) {
            throw new \InvalidArgumentException('Parent deck not found.');
        }

        // Parent deck must be a collection
        if (! $parentDeck->is_collection) {
            throw DeckHierarchyException::parentMustBeCollection();
        }
    }
}
