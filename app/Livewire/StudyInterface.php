<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Actions\CreateCardReportAction;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Data\StudySessionConfigData;
use Domain\Card\Enums\StudySessionType;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class StudyInterface extends Component
{
    public ?int $currentCardId = null;

    public bool $revealed = false;

    public ?int $deckId = null;

    public ?string $deckShortcode = null;

    public string $exitUrl = '';

    /** @var array<string> */
    public array $selectedAnswers = [];

    public ?StudySessionConfigData $sessionConfig = null;

    public array $sessionCards = [];

    public int $currentCardIndex = 0;

    // Report widget state
    public bool $showReportWidget = false;

    public string $reportMessage = '';

    public bool $reportSubmitted = false;

    // Shuffled answers cache (persists per card to avoid re-shuffling)
    public array $shuffledAnswersCache = [];

    public ?int $shuffledForCardId = null;

    // Session completion state
    public bool $sessionComplete = false;

    public bool $hasMoreCards = false;

    public int $cardsReviewedThisSession = 0;

    public function mount(
        string $type,
        string $deck,
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository,
        DeckRepository $deckRepository
    ): void {
        // Validate session type
        if (!in_array($type, ['normal', 'deep_study', 'practice'])) {
            $this->redirect(route('enrolled'));
            return;
        }

        // Validate deck shortcode format
        if (!preg_match('/^[A-Za-z0-9]{8}$/', $deck)) {
            $this->redirect(route('enrolled'));
            return;
        }

        $shortcode = $deck;

        // Find deck by shortcode
        $user = $userRepository->getLoggedInUser();
        $deckData = $deckRepository->findByShortcode($user->id, $shortcode);

        if (!$deckData) {
            $this->redirect(route('enrolled'));
            return;
        }

        $this->deckId = $deckData->id;
        $this->deckShortcode = $shortcode;

        // Determine exit URL based on deck's parent
        $this->exitUrl = $this->determineExitUrl($deckData, $deckRepository);

        // Parse session configuration from route param
        $this->initializeSessionConfig($type);

        // Load cards for the session
        $this->loadSessionCards($userRepository, $cardRepository);
    }

    private function determineExitUrl($deckData, DeckRepository $deckRepository): string
    {
        // If deck has a parent (is a child deck), return to collection page
        if ($deckData->parent_deck_id !== null) {
            return route('collection.show', ['id' => $deckData->parent_deck_id]);
        }

        // Otherwise return to enrolled page
        return route('enrolled');
    }

    private function initializeSessionConfig(string $sessionType): void
    {
        $type = match ($sessionType) {
            'deep_study' => StudySessionType::DEEP_STUDY,
            'practice' => StudySessionType::PRACTICE,
            default => StudySessionType::NORMAL,
        };

        $trackSrs = $type !== StudySessionType::PRACTICE;

        $this->sessionConfig = new StudySessionConfigData(
            type: $type,
            cardLimit: null,
            statusFilters: null,
            trackSrs: $trackSrs,
            randomOrder: false,
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
        $this->cardsReviewedThisSession++;

        if ($this->currentCardIndex < count($this->sessionCards)) {
            $this->currentCardId = $this->sessionCards[$this->currentCardIndex];
        } else {
            // Session complete - check if there are more cards available
            $this->currentCardId = null;
            $this->sessionComplete = true;
            $this->checkForMoreCards();
        }
    }

    private function checkForMoreCards(): void
    {
        // Only check for more cards in normal review sessions
        if ($this->sessionConfig->type !== StudySessionType::NORMAL) {
            $this->hasMoreCards = false;
            return;
        }

        $userRepository = app(UserRepository::class);
        $cardRepository = app(CardRepository::class);

        $user = $userRepository->getLoggedInUser();

        // Check if there are more cards available
        $moreCards = $cardRepository->getCardsForSession(
            $user->id,
            $this->deckId,
            $this->sessionConfig
        );

        $this->hasMoreCards = $moreCards->count() > 0;
    }

    public function loadMoreCards(): void
    {
        $userRepository = app(UserRepository::class);
        $cardRepository = app(CardRepository::class);

        // Reset session state
        $this->sessionComplete = false;
        $this->hasMoreCards = false;
        $this->currentCardIndex = 0;
        $this->cardsReviewedThisSession = 0;

        // Load new batch of cards
        $this->loadSessionCards($userRepository, $cardRepository);
    }

    public function reveal(): void
    {
        $this->revealed = true;
        $this->dispatch('card-revealed');
    }

    /**
     * Submit the selected answers, create review, and reveal the correct answer.
     * Accepts answers from client-side Alpine state to avoid round-trips on selection.
     *
     * @param array<string> $answers The selected answers from the client
     */
    public function submitAnswers(array $answers, ReviewCardAction $reviewCardAction, UserRepository $userRepository): void
    {
        // Store the answers from Alpine
        $this->selectedAnswers = $answers;

        if (count($this->selectedAnswers) === 0) {
            return;
        }

        if ($this->currentCardId === null) {
            return;
        }

        // Create the review immediately on submission (not on continue)
        // This ensures the card is marked as reviewed even if user refreshes
        $user = $userRepository->getLoggedInUser();
        $isPractice = !$this->sessionConfig->trackSrs;
        $reviewCardAction->execute($user->id, $this->currentCardId, $this->selectedAnswers, $isPractice);

        $this->revealed = true;
    }

    public function continue(): void
    {
        // Simply advance to the next card (review was already created in submitAnswers)
        $this->revealed = false;
        $this->selectedAnswers = [];
        $this->resetReportWidget();
        $this->resetShuffledAnswers();
        $this->loadNextCard();
    }

    private function resetShuffledAnswers(): void
    {
        $this->shuffledAnswersCache = [];
        $this->shuffledForCardId = null;
    }

    private function getShuffledAnswers(array $answerChoices, int $cardId): array
    {
        // Return cached shuffle if for the same card
        if ($this->shuffledForCardId === $cardId && !empty($this->shuffledAnswersCache)) {
            return $this->shuffledAnswersCache;
        }

        // Create array with original indices
        $answersWithIndices = [];
        foreach ($answerChoices as $index => $choice) {
            $answersWithIndices[] = [
                'originalIndex' => $index,
                'choice' => $choice,
            ];
        }

        // Shuffle the array
        shuffle($answersWithIndices);

        // Cache the result
        $this->shuffledAnswersCache = $answersWithIndices;
        $this->shuffledForCardId = $cardId;

        return $answersWithIndices;
    }

    public function toggleReportWidget(): void
    {
        $this->showReportWidget = !$this->showReportWidget;
        if (!$this->showReportWidget) {
            $this->resetReportWidget();
        }
    }

    public function submitReport(CreateCardReportAction $createCardReportAction, UserRepository $userRepository): void
    {
        if ($this->currentCardId === null || empty(trim($this->reportMessage))) {
            return;
        }

        $this->validate([
            'reportMessage' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $user = $userRepository->getLoggedInUser();
        $createCardReportAction->execute($this->currentCardId, $user->id, $this->reportMessage);

        $this->reportSubmitted = true;
        $this->reportMessage = '';
    }

    private function resetReportWidget(): void
    {
        $this->showReportWidget = false;
        $this->reportMessage = '';
        $this->reportSubmitted = false;
    }

    public function render(CardRepository $cardRepository, DeckRepository $deckRepository)
    {
        $card = null;
        $deck = null;
        $isCorrect = null;
        $progress = null;
        $shuffledAnswers = [];

        if ($this->currentCardId !== null) {
            $card = $cardRepository->findById($this->currentCardId);
            if ($card) {
                $deck = $deckRepository->findById($card->deck_id);

                // Get shuffled answers
                $answerChoices = $card->answer_choices ?? [];
                $shuffledAnswers = $this->getShuffledAnswers($answerChoices, $card->id);

                // Determine if answer is correct (for display after reveal)
                if ($this->revealed && count($this->selectedAnswers) > 0) {
                    $correctIndices = $card->correct_answer_indices ?? [];

                    // Get correct answers as strings
                    $correctAnswers = [];
                    foreach ($correctIndices as $index) {
                        if (isset($answerChoices[$index])) {
                            $correctAnswers[] = $answerChoices[$index];
                        }
                    }

                    // Sort both for comparison
                    $sortedSelected = $this->selectedAnswers;
                    $sortedCorrect = $correctAnswers;
                    sort($sortedSelected);
                    sort($sortedCorrect);

                    $isCorrect = $sortedSelected === $sortedCorrect;
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
            'deckShortcode' => $this->deckShortcode,
            'exitUrl' => $this->exitUrl,
            'shuffledAnswers' => $shuffledAnswers,
            'sessionComplete' => $this->sessionComplete,
            'hasMoreCards' => $this->hasMoreCards,
            'cardsReviewedThisSession' => $this->cardsReviewedThisSession,
        ]);
    }
}
