<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Library extends Component
{
    public ?string $category = null;

    public function filterByCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function render(
        DeckRepository $deckRepository,
        CardRepository $cardRepository,
        UserRepository $userRepository
    ) {
        $decksQuery = $this->category
            ? \Domain\Deck\Models\Deck::where('is_active', true)
                ->where('category', $this->category)
                ->get()
                ->map(fn ($deck) => \Domain\Deck\Data\DeckData::from($deck))
            : $deckRepository->getActive();

        $user = $userRepository->getLoggedInUser();

        // Get unique categories for filter
        $categories = \Domain\Deck\Models\Deck::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Get card counts and review status for each deck
        $decksWithStats = $decksQuery->map(function ($deck) use ($cardRepository, $user) {
            $cards = $cardRepository->getByDeckId($deck->id);
            $totalCards = $cards->count();

            // Count reviewed cards
            $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $user->id)
                ->pluck('card_id')
                ->toArray();
            $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

            return [
                'deck' => $deck,
                'totalCards' => $totalCards,
                'reviewedCount' => $reviewedCount,
                'progress' => $totalCards > 0 ? (int) (($reviewedCount / $totalCards) * 100) : 0,
            ];
        });

        return view('livewire.library', [
            'decksWithStats' => $decksWithStats,
            'categories' => $categories,
            'selectedCategory' => $this->category,
        ]);
    }
}
