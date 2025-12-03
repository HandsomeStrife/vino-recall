<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Data\StudySessionConfigData;
use Domain\Card\Enums\StudySessionType;
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

    public ?StudySessionConfigData $sessionConfig = null;

    public array $sessionCards = [];

    public int $currentCardIndex = 0;

    public function mount(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository,
        \Domain\Deck\Repositories\DeckRepository $deckRepository
    ): void {
        // Validate query parameters
        $validated = request()->validate([
            'deck' => ['required', 'string', 'size:8', 'alpha_num'],
            'session_type' => ['sometimes', 'string', 'in:normal,deep_study,practice'],
            'card_limit' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'status_filters' => ['sometimes', 'string'],
            'random_order' => ['sometimes', 'in:0,1'],
        ]);

        $shortcode = $validated['deck'];
        
        // Find deck by shortcode
        $user = $userRepository->getLoggedInUser();
        $deck = $deckRepository->findByShortcode($user->id, $shortcode);
        
        if (!$deck) {
            $this->redirect(route('library'));
            
            return;
        }
        
        $this->deckId = $deck->id;

        // Parse session configuration from query params
        $this->initializeSessionConfig();

        // Load cards for the session
        $this->loadSessionCards($userRepository, $cardRepository);
    }

    private function initializeSessionConfig(): void
    {
        // Query parameters are already validated in mount()
        $sessionType = request()->query('session_type', 'normal');
        $cardLimit = request()->query('card_limit') ? (int) request()->query('card_limit') : null;
        $statusFiltersString = request()->query('status_filters');
        
        // Parse and validate status filters
        $statusFilters = null;
        if ($statusFiltersString) {
            $filters = array_filter(explode(',', $statusFiltersString));
            // Validate each filter is an allowed value
            $allowedFilters = ['new', 'due', 'reviewed'];
            $validFilters = array_intersect($filters, $allowedFilters);
            $statusFilters = !empty($validFilters) ? array_values($validFilters) : null;
        }
        
        $randomOrder = request()->query('random_order', '0') === '1';

        $type = match ($sessionType) {
            'deep_study' => StudySessionType::DEEP_STUDY,
            'practice' => StudySessionType::PRACTICE,
            default => StudySessionType::NORMAL,
        };

        $trackSrs = $type !== StudySessionType::PRACTICE;

        $this->sessionConfig = new StudySessionConfigData(
            type: $type,
            cardLimit: $cardLimit,
            statusFilters: $statusFilters,
            trackSrs: $trackSrs,
            randomOrder: $randomOrder,
        );
    }

    private function loadSessionCards(
        UserRepository $userRepository,
        CardRepository $cardRepository
    ): void {
        $user = $userRepository->getLoggedInUser();

        // Get cards based on session configuration
        $cards = $cardRepository->getCardsForSession(
            $user->id,
            $this->deckId,
            $this->sessionConfig
        );

        $this->sessionCards = $cards->pluck('id')->toArray();
        $this->currentCardIndex = 0;
        
        // Load first card
        if (!empty($this->sessionCards)) {
            $this->currentCardId = $this->sessionCards[0];
        }
    }

    private function loadNextCard(): void
    {
        $this->currentCardIndex++;
        
        if ($this->currentCardIndex < count($this->sessionCards)) {
            $this->currentCardId = $this->sessionCards[$this->currentCardIndex];
        } else {
            $this->currentCardId = null;
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

    public function continue(ReviewCardAction $reviewCardAction, UserRepository $userRepository): void
    {
        if ($this->currentCardId === null) {
            return;
        }

        $user = $userRepository->getLoggedInUser();

        // ReviewCardAction now handles practice mode
        $isPractice = !$this->sessionConfig->trackSrs;
        $reviewCardAction->execute($user->id, $this->currentCardId, $this->selectedAnswer, $isPractice);

        $this->revealed = false;
        $this->selectedAnswer = null;
        $this->loadNextCard();
    }

    public function render(CardRepository $cardRepository, \Domain\Deck\Repositories\DeckRepository $deckRepository)
    {
        $card = null;
        $deck = null;
        $isCorrect = null;
        $progress = null;

        if ($this->currentCardId !== null) {
            $card = $cardRepository->findById($this->currentCardId);
            if ($card) {
                $deck = $deckRepository->findById($card->deck_id);
                
                // Determine if answer is correct (for display after reveal)
                if ($this->revealed && $this->selectedAnswer !== null && $card->card_type->value === 'multiple_choice') {
                    $answerChoices = $card->answer_choices;
                    $isCorrect = $this->selectedAnswer === $answerChoices[$card->correct_answer_index];
                }
            }
        } elseif ($this->deckId) {
            $deck = $deckRepository->findById($this->deckId);
        }

        // Calculate progress
        if (!empty($this->sessionCards)) {
            $progress = [
                'current' => $this->currentCardIndex + 1,
                'total' => count($this->sessionCards),
                'percentage' => (int) ((($this->currentCardIndex + 1) / count($this->sessionCards)) * 100),
            ];
        }

        return view('livewire.study-interface', [
            'card' => $card,
            'deck' => $deck,
            'isCorrect' => $isCorrect,
            'sessionConfig' => $this->sessionConfig,
            'progress' => $progress,
        ]);
    }
}
