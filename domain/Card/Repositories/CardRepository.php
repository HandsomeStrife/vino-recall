<?php

declare(strict_types=1);

namespace Domain\Card\Repositories;

use Domain\Card\Data\CardData;
use Domain\Card\Models\Card;

class CardRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getByDeckId(int $deckId): \Illuminate\Support\Collection
    {
        return Card::where('deck_id', $deckId)->get()->map(fn (Card $card) => CardData::fromModel($card));
    }

    public function findById(int $id): ?CardData
    {
        $card = Card::find($id);

        if ($card === null) {
            return null;
        }

        return CardData::fromModel($card);
    }

    /**
     * Find a card by its shortcode.
     */
    public function findByShortcode(string $shortcode): ?CardData
    {
        $card = Card::where('shortcode', strtoupper($shortcode))->first();

        if ($card === null) {
            return null;
        }

        return CardData::fromModel($card);
    }

    /**
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Card::all()->map(fn (Card $card) => CardData::fromModel($card));
    }

    /**
     * Get cards that the user hasn't reviewed yet (only from enrolled decks)
     *
     * @param int $userId
     * @param int|null $deckId Optional specific deck ID to filter by
     * @param int|null $limit Optional limit on number of cards returned
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getNewCardsForUser(int $userId, ?int $deckId = null, ?int $limit = null): \Illuminate\Support\Collection
    {
        $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->pluck('card_id')
            ->toArray();

        // Get enrolled deck IDs for the user
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        // If specific deck requested, validate it's enrolled
        $targetDeckIds = $enrolledDeckIds;
        if ($deckId !== null) {
            if (!in_array($deckId, $enrolledDeckIds)) {
                return collect();
            }
            $targetDeckIds = [$deckId];
        }

        $query = Card::whereNotIn('id', $reviewedCardIds)
            ->whereIn('deck_id', $targetDeckIds)
            ->orderBy('id', 'asc');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn (Card $card) => CardData::fromModel($card));
    }

    /**
     * Get cards for a study session based on configuration
     *
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getCardsForSession(
        int $userId,
        ?int $deckId,
        \Domain\Card\Data\StudySessionConfigData $config
    ): \Illuminate\Support\Collection {
        $cards = collect();

        // Get enrolled deck IDs
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        if ($deckId !== null && !in_array($deckId, $enrolledDeckIds)) {
            return collect();
        }

        $targetDeckIds = $deckId !== null ? [$deckId] : $enrolledDeckIds;

        // Filter based on session type
        if ($config->type === \Domain\Card\Enums\StudySessionType::PRACTICE) {
            $cards = $this->getCardsForPracticeSession($userId, $targetDeckIds, $config);
        } elseif ($config->type === \Domain\Card\Enums\StudySessionType::DEEP_STUDY) {
            $cards = $this->getCardsForDeepStudy($userId, $targetDeckIds);
        } else {
            // NORMAL session
            $cards = $this->getCardsForNormalSession($userId, $targetDeckIds);
        }

        // Apply card limit if specified
        if ($config->cardLimit !== null && $config->cardLimit > 0) {
            $cards = $cards->take($config->cardLimit);
        }

        // Apply random order if specified
        if ($config->randomOrder) {
            $cards = $cards->shuffle();
        }

        return $cards;
    }

    /**
     * Get cards for practice session with status filters
     *
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    private function getCardsForPracticeSession(
        int $userId,
        array $deckIds,
        \Domain\Card\Data\StudySessionConfigData $config
    ): \Illuminate\Support\Collection {
        $statusFilters = $config->statusFilters ?? [];

        if (empty($statusFilters)) {
            // No filters, return all cards from enrolled decks
            return Card::whereIn('deck_id', $deckIds)
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn (Card $card) => CardData::fromModel($card));
        }

        $allCards = collect();

        foreach ($statusFilters as $filter) {
            switch ($filter) {
                case 'mistakes':
                    // Cards marked as incorrect in reviews
                    $mistakeCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
                        ->where('is_correct', false)
                        ->whereHas('card', fn ($q) => $q->whereIn('deck_id', $deckIds))
                        ->pluck('card_id')
                        ->toArray();

                    $mistakeCards = Card::whereIn('id', $mistakeCardIds)
                        ->get()
                        ->map(fn (Card $card) => CardData::fromModel($card));

                    $allCards = $allCards->merge($mistakeCards);
                    break;

                case 'mastered':
                    // Cards with high ease factor (>= 2.5)
                    $masteredCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
                        ->where('ease_factor', '>=', 2.5)
                        ->whereHas('card', fn ($q) => $q->whereIn('deck_id', $deckIds))
                        ->pluck('card_id')
                        ->toArray();

                    $masteredCards = Card::whereIn('id', $masteredCardIds)
                        ->get()
                        ->map(fn (Card $card) => CardData::fromModel($card));

                    $allCards = $allCards->merge($masteredCards);
                    break;

                case 'new':
                    // Cards never reviewed
                    $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
                        ->pluck('card_id')
                        ->toArray();

                    $newCards = Card::whereNotIn('id', $reviewedCardIds)
                        ->whereIn('deck_id', $deckIds)
                        ->get()
                        ->map(fn (Card $card) => CardData::fromModel($card));

                    $allCards = $allCards->merge($newCards);
                    break;
            }
        }

        // Remove duplicates based on card ID
        return $allCards->unique('id')->values();
    }

    /**
     * Get all available cards for deep study (due + new)
     *
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    private function getCardsForDeepStudy(int $userId, array $deckIds): \Illuminate\Support\Collection
    {
        // Get all cards from the deck(s)
        $allCards = Card::whereIn('deck_id', $deckIds)
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (Card $card) => CardData::fromModel($card));

        // Prioritize due cards first, then new cards
        $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->whereHas('card', fn ($q) => $q->whereIn('deck_id', $deckIds))
            ->pluck('card_id')
            ->toArray();

        $dueCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->where('next_review_at', '<=', now())
            ->whereHas('card', fn ($q) => $q->whereIn('deck_id', $deckIds))
            ->pluck('card_id')
            ->toArray();

        // Sort: due cards first, then new cards, then reviewed but not due
        return $allCards->sort(function ($a, $b) use ($dueCardIds, $reviewedCardIds) {
            $aIsDue = in_array($a->id, $dueCardIds);
            $bIsDue = in_array($b->id, $dueCardIds);
            $aIsReviewed = in_array($a->id, $reviewedCardIds);
            $bIsReviewed = in_array($b->id, $reviewedCardIds);

            // Due cards come first
            if ($aIsDue && !$bIsDue) {
                return -1;
            }
            if (!$aIsDue && $bIsDue) {
                return 1;
            }

            // New cards come next
            if (!$aIsReviewed && $bIsReviewed) {
                return -1;
            }
            if ($aIsReviewed && !$bIsReviewed) {
                return 1;
            }

            return 0;
        })->values();
    }

    /**
     * Get cards for normal SRS session (limited due + limited new)
     *
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    private function getCardsForNormalSession(int $userId, array $deckIds): \Illuminate\Support\Collection
    {
        $cards = collect();

        // Get due cards
        $dueCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->where('next_review_at', '<=', now())
            ->whereHas('card', fn ($q) => $q->whereIn('deck_id', $deckIds))
            ->orderBy('next_review_at', 'asc')
            ->pluck('card_id')
            ->toArray();

        $dueCards = Card::whereIn('id', $dueCardIds)
            ->get()
            ->map(fn (Card $card) => CardData::fromModel($card));

        $cards = $cards->merge($dueCards);

        // Add some new cards (up to 10 total cards for the session)
        $remainingSlots = max(0, 10 - $cards->count());
        if ($remainingSlots > 0) {
            // Get new cards specifically for the target decks
            $newCards = $this->getNewCardsForDeckIds($userId, $deckIds, $remainingSlots);
            $cards = $cards->merge($newCards);
        }

        return $cards->values();
    }

    /**
     * Get new cards for specific deck IDs (helper for normal session)
     *
     * @param int $userId
     * @param array<int> $deckIds
     * @param int|null $limit
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    private function getNewCardsForDeckIds(int $userId, array $deckIds, ?int $limit = null): \Illuminate\Support\Collection
    {
        $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->pluck('card_id')
            ->toArray();

        $query = Card::whereNotIn('id', $reviewedCardIds)
            ->whereIn('deck_id', $deckIds)
            ->orderBy('id', 'asc');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn (Card $card) => CardData::fromModel($card));
    }
}
