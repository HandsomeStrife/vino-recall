<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Actions\UnenrollUserFromDeckAction;
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

    public function enrollInDeck(int $deckId, EnrollUserInDeckAction $enrollAction, UserRepository $userRepository): void
    {
        $user = $userRepository->getLoggedInUser();
        $enrollAction->execute($user->id, $deckId);

        $this->dispatch('deck-enrolled');
    }

    public function unenrollFromDeck(int $deckId, UnenrollUserFromDeckAction $unenrollAction, UserRepository $userRepository): void
    {
        $user = $userRepository->getLoggedInUser();
        $unenrollAction->execute($user->id, $deckId);

        $this->dispatch('deck-unenrolled');
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
        $decksWithStats = $decksQuery->map(function ($deck) use ($cardRepository, $user, $deckRepository) {
            $cards = $cardRepository->getByDeckId($deck->id);
            $totalCards = $cards->count();

            // Count reviewed cards
            $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $user->id)
                ->pluck('card_id')
                ->toArray();
            $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

            // Check if user is enrolled
            $isEnrolled = $deckRepository->isUserEnrolledInDeck($user->id, $deck->id);

            return [
                'deck' => $deck,
                'totalCards' => $totalCards,
                'reviewedCount' => $reviewedCount,
                'progress' => $totalCards > 0 ? (int) (($reviewedCount / $totalCards) * 100) : 0,
                'isEnrolled' => $isEnrolled,
            ];
        });

        return view('livewire.library', [
            'decksWithStats' => $decksWithStats,
            'categories' => $categories,
            'selectedCategory' => $this->category,
        ]);
    }
}
