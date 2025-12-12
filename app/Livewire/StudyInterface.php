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
use Domain\Material\Repositories\MaterialRepository;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
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

    // Study phase tracking
    public string $phase = 'flashcards'; // materials, intermediary, flashcards

    public int $current_material_index = 0;

    public function mount(
        string $type,
        string $deck,
        UserRepository $user_repository,
        CardReviewRepository $card_review_repository,
        CardRepository $card_repository,
        DeckRepository $deck_repository,
        MaterialRepository $material_repository
    ): void {
        // Validate session type
        if (! in_array($type, ['normal', 'deep_study', 'practice'])) {
            $this->redirect(route('library'));

            return;
        }

        // Validate deck shortcode format
        if (! preg_match('/^[A-Za-z0-9]{8}$/', $deck)) {
            $this->redirect(route('library'));

            return;
        }

        $shortcode = $deck;

        // Find deck by shortcode
        $user = $user_repository->getLoggedInUser();
        $deck_data = $deck_repository->findByShortcode($user->id, $shortcode);

        if (! $deck_data) {
            $this->redirect(route('library'));

            return;
        }

        $this->deckId = $deck_data->id;
        $this->deckShortcode = $shortcode;

        // Determine exit URL based on deck's parent
        $this->exitUrl = $this->determineExitUrl($deck_data, $deck_repository);

        // Parse session configuration from route param
        $this->initializeSessionConfig($type);

        // Check if we should show materials first
        $this->determineStartingPhase($user->id, $deck_data->id, $material_repository);

        // Load cards for the session
        $this->loadSessionCards($user_repository, $card_repository);
    }

    private function determineExitUrl($deckData, DeckRepository $deckRepository): string
    {
        // If deck has a parent (is a child deck), return to collection page
        if ($deckData->parent_deck_id !== null) {
            // Get parent deck to find its identifier
            $parentDeck = $deckRepository->findById($deckData->parent_deck_id);
            if ($parentDeck !== null) {
                return route('collection.show', ['identifier' => $parentDeck->identifier]);
            }
        }

        // Otherwise return to deck stats page
        return route('deck.stats', ['shortcode' => $this->deckShortcode]);
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

    private function determineStartingPhase(int $user_id, int $deck_id, MaterialRepository $material_repository): void
    {
        // Check if deck has materials and user hasn't viewed them
        $has_materials = $material_repository->hasMaterials($deck_id);

        if ($has_materials) {
            $has_viewed = DB::table('deck_user')
                ->where('user_id', $user_id)
                ->where('deck_id', $deck_id)
                ->value('has_viewed_materials');

            if (! $has_viewed) {
                $this->phase = 'materials';

                return;
            }
        }

        $this->phase = 'flashcards';
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
        if (! empty($this->sessionCards)) {
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
     * @param  array<string>  $answers  The selected answers from the client
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
        $isPractice = ! $this->sessionConfig->trackSrs;
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
        if ($this->shuffledForCardId === $cardId && ! empty($this->shuffledAnswersCache)) {
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
        $this->showReportWidget = ! $this->showReportWidget;
        if (! $this->showReportWidget) {
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

    // Material phase methods
    public function nextMaterial(MaterialRepository $material_repository): void
    {
        if ($this->deckId === null) {
            return;
        }

        $materials = $material_repository->getByDeckId($this->deckId);
        if ($this->current_material_index < $materials->count() - 1) {
            $this->current_material_index++;
        }
    }

    public function previousMaterial(): void
    {
        if ($this->current_material_index > 0) {
            $this->current_material_index--;
        }
    }

    public function completeMaterials(): void
    {
        $this->markMaterialsViewed();
        $this->phase = 'intermediary';
    }

    public function skipMaterials(): void
    {
        $this->markMaterialsViewed();
        $this->phase = 'intermediary';
    }

    public function beginFlashcards(): void
    {
        $this->phase = 'flashcards';
    }

    private function markMaterialsViewed(): void
    {
        if ($this->deckId === null) {
            return;
        }

        $user_repository = app(UserRepository::class);
        $user = $user_repository->getLoggedInUser();

        DB::table('deck_user')
            ->where('user_id', $user->id)
            ->where('deck_id', $this->deckId)
            ->update(['has_viewed_materials' => true]);
    }

    public function render(CardRepository $card_repository, DeckRepository $deck_repository, MaterialRepository $material_repository)
    {
        $card = null;
        $deck = null;
        $is_correct = null;
        $progress = null;
        $shuffled_answers = [];
        $materials = collect();
        $current_material = null;

        // Get deck info
        if ($this->deckId) {
            $deck = $deck_repository->findById($this->deckId);
        }

        // Materials phase
        if ($this->phase === 'materials' && $this->deckId) {
            $materials = $material_repository->getByDeckId($this->deckId);
            $current_material = $materials->get($this->current_material_index);
        }

        // Flashcard phase
        if ($this->phase === 'flashcards' && $this->currentCardId !== null) {
            $card = $card_repository->findById($this->currentCardId);
            if ($card) {
                $deck = $deck_repository->findById($card->deck_id);

                // Get shuffled answers
                $answer_choices = $card->answer_choices ?? [];
                $shuffled_answers = $this->getShuffledAnswers($answer_choices, $card->id);

                // Determine if answer is correct (for display after reveal)
                if ($this->revealed && count($this->selectedAnswers) > 0) {
                    $correct_indices = $card->correct_answer_indices ?? [];

                    // Get correct answers as strings
                    $correct_answers = [];
                    foreach ($correct_indices as $index) {
                        if (isset($answer_choices[$index])) {
                            $correct_answers[] = $answer_choices[$index];
                        }
                    }

                    // Sort both for comparison
                    $sorted_selected = $this->selectedAnswers;
                    $sorted_correct = $correct_answers;
                    sort($sorted_selected);
                    sort($sorted_correct);

                    $is_correct = $sorted_selected === $sorted_correct;
                }
            }
        } elseif ($this->deckId) {
            $deck = $deck_repository->findById($this->deckId);
        }

        // Calculate progress
        if (! empty($this->sessionCards)) {
            $progress = [
                'current' => $this->currentCardIndex + 1,
                'total' => count($this->sessionCards),
                'percentage' => (int) ((($this->currentCardIndex + 1) / count($this->sessionCards)) * 100),
            ];
        }

        return view('livewire.study-interface', [
            'card' => $card,
            'deck' => $deck,
            'isCorrect' => $is_correct,
            'sessionConfig' => $this->sessionConfig,
            'progress' => $progress,
            'deckShortcode' => $this->deckShortcode,
            'exitUrl' => $this->exitUrl,
            'shuffledAnswers' => $shuffled_answers,
            'sessionComplete' => $this->sessionComplete,
            'hasMoreCards' => $this->hasMoreCards,
            'cardsReviewedThisSession' => $this->cardsReviewedThisSession,
            'phase' => $this->phase,
            'materials' => $materials,
            'current_material' => $current_material,
            'current_material_index' => $this->current_material_index,
        ]);
    }
}
