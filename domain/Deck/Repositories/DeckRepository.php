<?php

declare(strict_types=1);

namespace Domain\Deck\Repositories;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class DeckRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Deck::with(['categories', 'parent', 'children'])
            ->withCount('cards')
            ->orderByRaw('COALESCE(parent_deck_id, id), parent_deck_id IS NOT NULL, name')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    public function findById(int $id): ?DeckData
    {
        $deck = Deck::with(['categories', 'parent', 'children'])->find($id);

        if ($deck === null) {
            return null;
        }

        return DeckData::fromModel($deck);
    }

    public function findByShortcode(int $user_id, string $shortcode): ?DeckData
    {
        $user = \Domain\User\Models\User::find($user_id);

        if (! $user) {
            return null;
        }

        $deck = $user->enrolledDecks()
            ->wherePivot('shortcode', $shortcode)
            ->first();

        if ($deck === null) {
            return null;
        }

        return DeckData::fromModel($deck);
    }

    public function findByIdentifier(string $identifier): ?DeckData
    {
        $deck = Deck::with(['categories', 'parent', 'children'])
            ->where('identifier', $identifier)
            ->first();

        return $deck ? DeckData::fromModel($deck) : null;
    }

    /**
     * Get active decks that are either parent decks or standalone (no parent).
     * Child decks are not returned directly - they are accessed through their parent.
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getActive(): \Illuminate\Support\Collection
    {
        return Deck::where('is_active', true)
            ->whereNull('parent_deck_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getUserEnrolledDecks(int $user_id): \Illuminate\Support\Collection
    {
        $user = \Domain\User\Models\User::find($user_id);

        if (! $user) {
            return collect();
        }

        return $user->enrolledDecks()
            ->with(['parent', 'children'])
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    public function isUserEnrolledInDeck(int $user_id, int $deck_id): bool
    {
        $deck = Deck::find($deck_id);

        if ($deck === null) {
            return false;
        }

        return $deck->enrolledUsers()->where('user_id', $user_id)->exists();
    }

    /**
     * Get decks the user is NOT enrolled in (available to join).
     * Only returns parent decks and standalone decks - not child decks.
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAvailableDecks(int $userId): \Illuminate\Support\Collection
    {
        $user = \Domain\User\Models\User::find($userId);

        if (! $user) {
            return collect();
        }

        $enrolledDeckIds = $user->enrolledDecks()->pluck('decks.id')->toArray();

        return Deck::where('is_active', true)
            ->whereNull('parent_deck_id')
            ->whereNotIn('id', $enrolledDeckIds)
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get decks by category
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getByCategory(int $categoryId): \Illuminate\Support\Collection
    {
        return Deck::whereHas('categories', function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })
            ->with(['categories', 'parent', 'children'])
            ->withCount('cards')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get parent decks (decks that have children).
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getParentDecks(): \Illuminate\Support\Collection
    {
        return Deck::whereHas('children')
            ->with(['children', 'categories'])
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get standalone decks (decks with no parent and no children).
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getStandaloneDecks(): \Illuminate\Support\Collection
    {
        return Deck::whereNull('parent_deck_id')
            ->whereDoesntHave('children')
            ->with('categories')
            ->withCount('cards')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get child decks of a specific parent.
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getChildDecks(int $parentId): \Illuminate\Support\Collection
    {
        return Deck::where('parent_deck_id', $parentId)
            ->with('categories')
            ->withCount('cards')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get decks that can be selected as parents (collections) for a given deck.
     * Only returns decks explicitly marked as collections.
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAvailableParents(?int $excludeId = null): \Illuminate\Support\Collection
    {
        $query = Deck::where('is_collection', true);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('name')->get()->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get decks that can be assigned as children to a parent deck.
     * Excludes:
     * - The parent deck itself
     * - Decks that already have children (to enforce single-level hierarchy)
     * - Decks that already have a different parent
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAvailableChildren(?int $parentId = null): \Illuminate\Support\Collection
    {
        $query = Deck::whereDoesntHave('children');

        if ($parentId !== null) {
            $query->where('id', '!=', $parentId)
                ->where(function ($q) use ($parentId) {
                    $q->whereNull('parent_deck_id')
                        ->orWhere('parent_deck_id', $parentId);
                });
        } else {
            $query->whereNull('parent_deck_id');
        }

        return $query->withCount('cards')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * Get child decks that the user has enrolled in individually
     * (without being enrolled in the parent collection).
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getIndividuallyEnrolledChildDecks(int $userId): \Illuminate\Support\Collection
    {
        $user = \Domain\User\Models\User::find($userId);

        if (! $user) {
            return collect();
        }

        // Get all decks the user is enrolled in that have a parent
        $enrolledChildDecks = $user->enrolledDecks()
            ->whereNotNull('parent_deck_id')
            ->with(['parent', 'categories'])
            ->get();

        // Filter out children whose parent collection the user is also enrolled in
        return $enrolledChildDecks->filter(function (Deck $deck) use ($user) {
            // If the parent exists and the user is NOT enrolled in the parent, include this deck
            if ($deck->parent_deck_id !== null) {
                return ! $this->isUserEnrolledInDeck($user->id, $deck->parent_deck_id);
            }

            return false;
        })->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }
}
