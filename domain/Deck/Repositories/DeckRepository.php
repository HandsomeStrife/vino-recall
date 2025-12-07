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
        return Deck::with('categories')->withCount('cards')->get()->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    public function findById(int $id): ?DeckData
    {
        $deck = Deck::with('categories')->find($id);

        if ($deck === null) {
            return null;
        }

        return DeckData::fromModel($deck);
    }

    public function findByShortcode(int $user_id, string $shortcode): ?DeckData
    {
        $user = \Domain\User\Models\User::find($user_id);
        
        if (!$user) {
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

    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getActive(): \Illuminate\Support\Collection
    {
        return Deck::where('is_active', true)->get()->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getUserEnrolledDecks(int $user_id): \Illuminate\Support\Collection
    {
        $user = \Domain\User\Models\User::find($user_id);
        
        if (!$user) {
            return collect();
        }

        return $user->enrolledDecks()->get()->map(fn (Deck $deck) => DeckData::fromModel($deck));
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
     * Get decks the user is NOT enrolled in (available to join)
     *
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAvailableDecks(int $userId): \Illuminate\Support\Collection
    {
        $user = \Domain\User\Models\User::find($userId);
        
        if (!$user) {
            return collect();
        }

        $enrolledDeckIds = $user->enrolledDecks()->pluck('decks.id')->toArray();

        return Deck::where('is_active', true)
            ->whereNotIn('id', $enrolledDeckIds)
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
            ->with('categories')
            ->withCount('cards')
            ->get()
            ->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }
}
