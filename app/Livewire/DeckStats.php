<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Actions\ResetDeckReviewsAction;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class DeckStats extends Component
{
    public int $deckId;

    public bool $showResetConfirmation = false;

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

    public function confirmReset(): void
    {
        $this->showResetConfirmation = true;
    }

    public function cancelReset(): void
    {
        $this->showResetConfirmation = false;
    }

    public function resetDeckProgress(): void
    {
        $user = (new UserRepository())->getLoggedInUser();

        $result = (new ResetDeckReviewsAction())->execute($user->id, $this->deckId);

        $this->showResetConfirmation = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Deck progress has been reset. You can start fresh!',
        ]);
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

        // Get SRS state for cards in this deck
        $cardReviews = CardReview::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->get();

        $reviewedCardIds = $cardReviews->pluck('card_id')->toArray();
        $reviewedCount = count($reviewedCardIds);
        $newCardsCount = $totalCards - $reviewedCount;

        // Count mastered cards (srs_stage >= MASTERED_THRESHOLD)
        $masteredCount = $cardReviews->filter(fn ($review) => SrsStage::isMastered($review->srs_stage))->count();

        // Count learning cards (reviewed but not mastered)
        $learningCount = $reviewedCount - $masteredCount;

        // Calculate due cards
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id)
            ->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            });
        $dueCardsCount = $dueCards->count();

        // Calculate progress using stage-based formula
        // progress = sum(srs_stage / STAGE_MAX) / total_cards * 100
        $progress = $cardReviewRepository->getProgress($user->id, $this->deckId, $totalCards);

        // Calculate accuracy rate from review history (excludes practice)
        $accuracyRate = $cardReviewRepository->getAccuracy($user->id, $this->deckId);

        // Calculate mastery rate (cards with stage >= 7 / total cards)
        $masteryRate = $cardReviewRepository->getMasteryRate($user->id, $this->deckId, $totalCards);

        // Recent activity from review history for this deck
        $recentActivity = ReviewHistory::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($history) use ($cardRepository) {
                return [
                    'history' => \Domain\Card\Data\ReviewHistoryData::fromModel($history),
                    'card' => $cardRepository->findById($history->card_id),
                ];
            });

        return view('livewire.deck-stats', [
            'deck' => $deck,
            'totalCards' => $totalCards,
            'reviewedCount' => $reviewedCount,
            'newCardsCount' => $newCardsCount,
            'learningCount' => $learningCount,
            'masteredCount' => $masteredCount,
            'dueCardsCount' => $dueCardsCount,
            'progress' => $progress,
            'accuracyRate' => $accuracyRate,
            'masteryRate' => $masteryRate,
            'recentActivity' => $recentActivity,
        ]);
    }
}
