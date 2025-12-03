<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class DeckStats extends Component
{
    public int $deckId;

    public function mount(string $shortcode, UserRepository $userRepository, DeckRepository $deckRepository): void
    {
        // Find deck by shortcode
        $user = $userRepository->getLoggedInUser();
        $deck = $deckRepository->findByShortcode($user->id, $shortcode);
        
        if (!$deck) {
            $this->redirect(route('library'));
            
            return;
        }
        
        $this->deckId = $deck->id;
    }

    public function render(
        DeckRepository $deckRepository,
        CardRepository $cardRepository,
        CardReviewRepository $cardReviewRepository,
        UserRepository $userRepository
    ) {
        $deck = $deckRepository->findById($this->deckId);
        $user = $userRepository->getLoggedInUser();

        if (!$deck) {
            return redirect()->route('library');
        }

        $cards = $cardRepository->getByDeckId($this->deckId);
        $totalCards = $cards->count();

        // Get all reviews for this deck
        $allReviews = \Domain\Card\Models\CardReview::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->get();

        $reviewedCardIds = $allReviews->pluck('card_id')->toArray();
        $reviewedCount = count($reviewedCardIds);
        $newCardsCount = $totalCards - $reviewedCount;

        // Count mastered cards (ease_factor >= 2.0)
        $masteredCount = $allReviews->where('ease_factor', '>=', 2.0)->count();

        // Count learning cards (reviewed but not mastered)
        $learningCount = $reviewedCount - $masteredCount;

        // Calculate due cards
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id)
            ->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            });
        $dueCardsCount = $dueCards->count();

        // Calculate progress percentage
        $progress = $totalCards > 0 ? (int) (($reviewedCount / $totalCards) * 100) : 0;

        // Calculate accuracy rate (correct answers / total reviews)
        $totalReviewActions = $allReviews->count();
        $correctReviews = $allReviews->whereIn('rating', ['good', 'easy'])->count();
        $accuracyRate = $totalReviewActions > 0 ? (int) (($correctReviews / $totalReviewActions) * 100) : 0;

        // Recent activity for this deck
        $recentActivity = \Domain\Card\Models\CardReview::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($review) use ($cardRepository) {
                return [
                    'review' => \Domain\Card\Data\CardReviewData::fromModel($review),
                    'card' => $cardRepository->findById($review->card_id),
                ];
            });

        return view('livewire.deck-stats', [
            'deck' => $deck,
            'totalCards' => $totalCards,
            'newCardsCount' => $newCardsCount,
            'learningCount' => $learningCount,
            'masteredCount' => $masteredCount,
            'dueCardsCount' => $dueCardsCount,
            'progress' => $progress,
            'accuracyRate' => $accuracyRate,
            'recentActivity' => $recentActivity,
        ]);
    }
}
