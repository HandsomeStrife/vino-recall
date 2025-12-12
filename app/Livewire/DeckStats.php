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

    public string $deckShortcode;

    public bool $showResetConfirmation = false;

    public function mount(string $shortcode, UserRepository $userRepository, DeckRepository $deckRepository): void
    {
        // Find deck by shortcode
        $user = $userRepository->getLoggedInUser();
        $deck = $deckRepository->findByShortcode($user->id, $shortcode);

        if (! $deck) {
            $this->redirect(route('library'));

            return;
        }

        $this->deckId = $deck->id;
        $this->deckShortcode = $shortcode;
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
        $user = (new UserRepository)->getLoggedInUser();

        $result = (new ResetDeckReviewsAction)->execute($user->id, $this->deckId);

        $this->showResetConfirmation = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Deck progress has been reset. You can start fresh!',
        ]);
    }

    public function render(
        DeckRepository $deck_repository,
        CardRepository $card_repository,
        CardReviewRepository $card_review_repository,
        UserRepository $user_repository,
        \Domain\Material\Repositories\MaterialRepository $material_repository
    ) {
        $deck = $deck_repository->findById($this->deckId);
        $user = $user_repository->getLoggedInUser();

        if (! $deck) {
            return redirect()->route('library');
        }

        $cards = $card_repository->getByDeckId($this->deckId);
        $total_cards = $cards->count();

        // Check if deck has materials
        $has_materials = $material_repository->hasMaterials($this->deckId);

        // Get SRS state for cards in this deck
        $card_reviews = CardReview::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->get();

        $reviewed_card_ids = $card_reviews->pluck('card_id')->toArray();
        $reviewed_count = count($reviewed_card_ids);
        $new_cards_count = $total_cards - $reviewed_count;

        // Count mastered cards (srs_stage >= MASTERED_THRESHOLD)
        $mastered_count = $card_reviews->filter(fn ($review) => SrsStage::isMastered($review->srs_stage))->count();

        // Count learning cards (reviewed but not mastered)
        $learning_count = $reviewed_count - $mastered_count;

        // Calculate due cards
        $due_cards = $card_review_repository->getDueCardsForUser($user->id)
            ->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            });
        $due_cards_count = $due_cards->count();

        // Calculate progress using stage-based formula
        // progress = sum(srs_stage / STAGE_MAX) / total_cards * 100
        $progress = $card_review_repository->getProgress($user->id, $this->deckId, $total_cards);

        // Calculate accuracy rate from review history (excludes practice)
        $accuracy_rate = $card_review_repository->getAccuracy($user->id, $this->deckId);

        // Calculate mastery rate (cards with stage >= 7 / total cards)
        $mastery_rate = $card_review_repository->getMasteryRate($user->id, $this->deckId, $total_cards);

        // Recent activity from review history for this deck
        $recent_activity = ReviewHistory::where('user_id', $user->id)
            ->whereHas('card', function ($query) {
                $query->where('deck_id', $this->deckId);
            })
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($history) use ($card_repository) {
                return [
                    'history' => \Domain\Card\Data\ReviewHistoryData::fromModel($history),
                    'card' => $card_repository->findById($history->card_id),
                ];
            });

        return view('livewire.deck-stats', [
            'deck' => $deck,
            'totalCards' => $total_cards,
            'reviewedCount' => $reviewed_count,
            'newCardsCount' => $new_cards_count,
            'learningCount' => $learning_count,
            'masteredCount' => $mastered_count,
            'dueCardsCount' => $due_cards_count,
            'progress' => $progress,
            'accuracyRate' => $accuracy_rate,
            'masteryRate' => $mastery_rate,
            'recentActivity' => $recent_activity,
            'hasMaterials' => $has_materials,
            'deckShortcode' => $this->deckShortcode,
        ]);
    }
}
