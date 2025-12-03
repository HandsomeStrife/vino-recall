<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Enums\CardRating;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class StudyInterface extends Component
{
    public ?int $currentCardId = null;

    public bool $revealed = false;

    public ?int $deckId = null;

    public ?string $selectedAnswer = null;

    public function mount(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository
    ): void {
        $this->deckId = request()->query('deck');
        $this->loadNextCard($userRepository, $cardReviewRepository, $cardRepository);
    }

    private function loadNextCard(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository
    ): void {
        $user = $userRepository->getLoggedInUser();

        if ($this->deckId) {
            // Study specific deck
            $dueCards = $cardReviewRepository->getDueCardsForUser($user->id)
                ->filter(function ($review) use ($cardRepository) {
                    $card = $cardRepository->findById($review->card_id);

                    return $card && $card->deck_id === $this->deckId;
                });

            if ($dueCards->isNotEmpty()) {
                $this->currentCardId = $dueCards->first()->card_id;

                return;
            }

            // Get new cards from this deck
            $newCards = $cardRepository->getNewCardsForUser($user->id)
                ->filter(fn ($card) => $card->deck_id === $this->deckId);

            if ($newCards->isNotEmpty()) {
                $this->currentCardId = $newCards->first()->id;

                return;
            }
        } else {
            // Study all cards (default behavior)
            $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);

            if ($dueCards->isNotEmpty()) {
                $this->currentCardId = $dueCards->first()->card_id;

                return;
            }

            // If no due cards, get a new card
            $newCards = $cardRepository->getNewCardsForUser($user->id);
            if ($newCards->isNotEmpty()) {
                $this->currentCardId = $newCards->first()->id;

                return;
            }
        }
    }

    public function reveal(): void
    {
        $this->revealed = true;
        $this->dispatch('card-revealed');
    }

    public function selectAnswer(string $answer): void
    {
        $this->selectedAnswer = $answer;
        $this->revealed = true;
        $this->dispatch('card-revealed');
    }

    public function rate(string $rating, ReviewCardAction $reviewCardAction, UserRepository $userRepository, CardReviewRepository $cardReviewRepository, CardRepository $cardRepository): void
    {
        if ($this->currentCardId === null) {
            return;
        }

        $user = $userRepository->getLoggedInUser();
        $cardRating = CardRating::from($rating);

        $reviewCardAction->execute($user->id, $this->currentCardId, $cardRating, $this->selectedAnswer);

        $this->revealed = false;
        $this->selectedAnswer = null;
        $this->loadNextCard($userRepository, $cardReviewRepository, $cardRepository);
    }

    public function render(CardRepository $cardRepository, \Domain\Deck\Repositories\DeckRepository $deckRepository)
    {
        $card = null;
        $deck = null;

        if ($this->currentCardId !== null) {
            $card = $cardRepository->findById($this->currentCardId);
            if ($card) {
                $deck = $deckRepository->findById($card->deck_id);
            }
        } elseif ($this->deckId) {
            $deck = $deckRepository->findById($this->deckId);
        }

        return view('livewire.study-interface', [
            'card' => $card,
            'deck' => $deck,
        ]);
    }
}
