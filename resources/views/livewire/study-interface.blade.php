<div class="relative min-h-screen bg-cream-50"
     x-data="studyInterface()"
     x-init="init()"
     @keydown.window="handleKeydown($event)">
    <div class="absolute top-4 right-4 z-10">
        <a href="{{ $exitUrl }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Exit
        </a>
    </div>
    
    <!-- Auto-progress checkbox (bottom left) -->
    @if($card)
    <div class="fixed bottom-4 left-4 z-40">
        <label class="flex items-center gap-2 bg-white/90 backdrop-blur-sm px-3 py-2 rounded-lg shadow-md cursor-pointer select-none">
            <input type="checkbox" 
                   x-model="autoProgress" 
                   class="w-4 h-4 text-burgundy-600 border-gray-300 rounded focus:ring-burgundy-500 cursor-pointer">
            <span class="text-sm text-gray-700 font-medium">Auto-progress</span>
        </label>
    </div>
    @endif
    
    <div class="p-8 flex items-center justify-center min-h-screen">
        @if($card)
            <div class="max-w-2xl w-full">
                <!-- Session Type Badge and Progress -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($deck)
                            <x-badge.badge variant="primary">{{ $deck->name }}</x-badge.badge>
                        @endif
                        
                        @if($sessionConfig)
                            @if($sessionConfig->type->value === 'deep_study')
                                <x-badge.badge variant="info">Deep Study</x-badge.badge>
                            @elseif($sessionConfig->type->value === 'practice')
                                <x-badge.badge variant="warning">Practice Session</x-badge.badge>
                            @else
                                <x-badge.badge variant="success">Normal Review</x-badge.badge>
                            @endif
                        @endif
                    </div>
                    
                    @if($progress)
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">
                                {{ $progress['current'] }} / {{ $progress['total'] }}
                            </div>
                            <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                                <div class="h-2 bg-burgundy-500 rounded-full transition-all" 
                                     style="width: {{ $progress['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="bg-white rounded-lg border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-8 transition-all duration-300" 
                     wire:key="card-{{ $card->id }}-{{ $revealed ? 'revealed' : 'hidden' }}">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-burgundy-900 mb-4">{{ $card->question }}</h2>
                        @if($card->image_path)
                            <img src="{{ asset('storage/' . $card->image_path) }}" alt="Card image" class="mx-auto max-h-64 rounded-lg mb-4" loading="lazy">
                        @endif
                        
                        @if($card->hasMultipleCorrectAnswers())
                            <p class="text-sm text-burgundy-600 font-medium mt-2">Select all that apply</p>
                        @endif
                    </div>

                    @if(!$revealed)
                        <div class="space-y-3">
                            @foreach($shuffledAnswers as $displayIndex => $answerData)
                                <button type="button"
                                        @click="toggleAnswer('{{ addslashes($answerData['choice']) }}')" 
                                        :class="isSelected('{{ addslashes($answerData['choice']) }}') 
                                            ? 'bg-burgundy-100 border-2 border-burgundy-500 scale-[1.01]' 
                                            : 'bg-gray-100 hover:bg-burgundy-50 border-2 border-transparent hover:border-burgundy-300 hover:scale-[1.01] hover:shadow-md'"
                                        class="w-full px-6 py-3 text-left rounded-lg transition-all duration-150 flex items-center cursor-pointer">
                                    <span :class="isSelected('{{ addslashes($answerData['choice']) }}') 
                                                ? 'bg-burgundy-500 border-burgundy-500' 
                                                : 'border-gray-400'"
                                          class="flex-shrink-0 w-6 h-6 mr-3 rounded border-2 flex items-center justify-center transition-all duration-150">
                                        <svg x-show="isSelected('{{ addslashes($answerData['choice']) }}')" 
                                             x-cloak
                                             class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </span>
                                    <span class="font-semibold text-burgundy-900">{{ chr(65 + $displayIndex) }}.</span>
                                    <span class="ml-2">{{ $answerData['choice'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        
                        <div class="text-center mt-6">
                            <button type="button"
                                    @click="submitAnswers()" 
                                    :disabled="selectedAnswers.length === 0"
                                    :class="selectedAnswers.length > 0 
                                        ? 'bg-burgundy-500 text-white hover:bg-burgundy-600 hover:scale-105' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                    class="px-8 py-3 rounded-lg transition font-semibold transform"
                                    title="Press Space or Enter">
                                Submit Answer{{ $card->hasMultipleCorrectAnswers() ? 's' : '' }}
                            </button>
                            <p class="text-sm text-gray-500 mt-4" x-text="selectedAnswers.length > 0 ? 'Press Space or Enter to submit' : 'Select an answer to continue'"></p>
                        </div>
                    @else
                        <div class="space-y-3 mb-8">
                            @foreach($shuffledAnswers as $displayIndex => $answerData)
                                @php
                                    $isCorrectAnswer = in_array($answerData['originalIndex'], $card->correct_answer_indices ?? []);
                                    $wasSelected = in_array($answerData['choice'], $selectedAnswers);
                                @endphp
                                <div class="w-full px-6 py-3 text-left rounded-lg border-2 flex items-center
                                            {{ $isCorrectAnswer ? 'bg-green-100 border-green-500' : 'bg-gray-100 border-gray-300' }}
                                            {{ $wasSelected && !$isCorrectAnswer ? 'bg-red-100 border-red-500' : '' }}">
                                    <span class="flex-shrink-0 w-6 h-6 mr-3 rounded border-2 flex items-center justify-center
                                                 {{ $isCorrectAnswer ? 'bg-green-500 border-green-500' : ($wasSelected ? 'bg-red-500 border-red-500' : 'border-gray-400') }}">
                                        @if($isCorrectAnswer)
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @elseif($wasSelected)
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="font-semibold">{{ chr(65 + $displayIndex) }}.</span>
                                    <span class="ml-2 flex-1">{{ $answerData['choice'] }}</span>
                                    @if($isCorrectAnswer)
                                        <!-- Correct icon -->
                                        <svg class="w-5 h-5 text-green-600 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @elseif($wasSelected)
                                        <!-- Incorrect icon -->
                                        <svg class="w-5 h-5 text-red-600 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-6">
                            <button type="button"
                                    @click="continueToNext()" 
                                    class="px-8 py-3 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold transform hover:scale-105"
                                    title="Press Space or Enter">
                                <span x-show="!autoProgress || countdown === 0">Continue</span>
                                <span x-show="autoProgress && countdown > 0" x-text="'Continue (' + countdown + ')'"></span>
                            </button>
                            <p class="text-center text-sm text-gray-500 mt-4" x-text="autoProgress && countdown > 0 ? 'Auto-advancing in ' + countdown + '...' : 'Press Space or Enter to continue'"></p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Report Problem Chat Widget -->
            @if($showReportWidget)
                <div class="fixed bottom-4 right-4 w-80 z-50">
                    <div class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
                        <!-- Header -->
                        <div class="bg-burgundy-600 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="text-white font-semibold">Report a Problem</span>
                            </div>
                            <button wire:click="toggleReportWidget" class="text-white/80 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-4">
                            @if($reportSubmitted)
                                <div class="text-center py-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-800 font-medium mb-1">Thank you!</p>
                                    <p class="text-gray-500 text-sm">Your report has been submitted. We'll review it shortly.</p>
                                    <button wire:click="toggleReportWidget" class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                                        Close
                                    </button>
                                </div>
                            @else
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 mb-2">
                                        Reporting card: <span class="font-mono font-semibold text-burgundy-600">{{ $card->shortcode }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Please describe the issue (e.g., incorrect answer, typo, unclear question)
                                    </p>
                                </div>
                                
                                <form wire:submit.prevent="submitReport">
                                    <textarea wire:model="reportMessage" 
                                              rows="4"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500 resize-none"
                                              placeholder="Describe the problem with this card..."></textarea>
                                    @error('reportMessage') 
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                                    @enderror
                                    
                                    <div class="flex justify-end gap-2 mt-3">
                                        <button type="button" wire:click="toggleReportWidget"
                                                class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-1.5 bg-burgundy-600 text-white text-sm font-medium rounded-lg hover:bg-burgundy-700 transition"
                                                wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="submitReport">Submit Report</span>
                                            <span wire:loading wire:target="submitReport">Sending...</span>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Report Button (collapsed state) -->
                <button wire:click="toggleReportWidget" 
                        class="fixed bottom-4 right-4 z-50 bg-burgundy-600 text-white p-3 rounded-full shadow-lg hover:bg-burgundy-700 transition hover:scale-105 cursor-pointer"
                        title="Report a problem with this card">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </button>
            @endif
        @else
            <div class="bg-white rounded-lg border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-8 text-center max-w-md">
                <div class="mb-6">
                    <img src="{{ asset('img/celebrate.png') }}" alt="Celebration" class="h-32 w-32 mx-auto">
                </div>
                
                @if($hasMoreCards && $sessionConfig && $sessionConfig->type->value === 'normal')
                    {{-- More cards available - ask user if they want to continue --}}
                    <h2 class="text-2xl font-bold text-burgundy-900 mb-2">
                        Batch Complete!
                    </h2>
                    
                    <p class="text-gray-600 mb-6">
                        You've reviewed {{ $cardsReviewedThisSession }} cards. There are more cards available.
                        Would you like to continue?
                    </p>
                    
                    <div class="flex flex-col gap-3">
                        <button wire:click="loadMoreCards" 
                                class="w-full bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold">
                            Continue with More Cards
                        </button>
                        <a href="{{ $exitUrl }}" class="w-full border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition font-semibold inline-block">
                            I'm Done for Now
                        </a>
                    </div>
                @else
                    {{-- No more cards or not a normal session --}}
                    <h2 class="text-2xl font-bold text-burgundy-900 mb-2">
                        @if($sessionConfig && $sessionConfig->type->value === 'practice')
                            Practice Complete!
                        @elseif($sessionConfig && $sessionConfig->type->value === 'deep_study')
                            Deep Study Complete!
                        @elseif($sessionComplete && $cardsReviewedThisSession > 0)
                            All Caught Up!
                        @elseif($deck)
                            Session Complete!
                        @else
                            All Caught Up!
                        @endif
                    </h2>
                    
                    <p class="text-gray-600 mb-6">
                        @if($sessionConfig && $sessionConfig->type->value === 'practice')
                            You've completed your practice session. Your SRS schedule remains unchanged.
                        @elseif($sessionConfig && $sessionConfig->type->value === 'deep_study')
                            Great work! You've reviewed all available cards in this deck.
                        @elseif($sessionComplete && $cardsReviewedThisSession > 0)
                            Great job! You've reviewed {{ $cardsReviewedThisSession }} cards. No more cards due right now.
                        @elseif($deck)
                            You've completed all cards in this session. Great job!
                        @else
                            Great job! Come back later for more reviews.
                        @endif
                    </p>
                    
                    <div class="flex gap-3 justify-center">
                        @if($deck && $deckShortcode)
                            <a href="{{ route('deck.stats', ['shortcode' => $deckShortcode]) }}" class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold">
                                View Stats
                            </a>
                        @endif
                        <a href="{{ $exitUrl }}" class="inline-block border-2 border-burgundy-500 text-burgundy-500 px-6 py-2 rounded-lg hover:bg-burgundy-50 transition font-semibold">
                            Exit
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
// Global countdown interval reference to prevent duplicates
window.vinoStudyCountdown = null;
window.vinoCountdownCardId = null;

function studyInterface() {
    return {
        selectedAnswers: [],
        autoProgress: true,
        countdown: 0,
        revealed: @js($revealed),
        isSubmitting: false,
        cardId: @js($card?->id ?? null),
        
        init() {
            // Load auto-progress preference from localStorage
            const savedAutoProgress = localStorage.getItem('vinorecall_auto_progress');
            if (savedAutoProgress !== null) {
                this.autoProgress = savedAutoProgress === 'true';
            }
            
            // Watch for auto-progress changes to save preference
            this.$watch('autoProgress', (value) => {
                localStorage.setItem('vinorecall_auto_progress', value.toString());
                if (!value) {
                    this.cancelCountdown();
                }
            });
            
            // If this is a different card than the one with active countdown, cancel it
            if (window.vinoCountdownCardId !== null && window.vinoCountdownCardId !== this.cardId) {
                this.cancelCountdown();
            }
        },
        
        toggleAnswer(answer) {
            const index = this.selectedAnswers.indexOf(answer);
            if (index > -1) {
                this.selectedAnswers.splice(index, 1);
            } else {
                this.selectedAnswers.push(answer);
            }
        },
        
        isSelected(answer) {
            return this.selectedAnswers.includes(answer);
        },
        
        async submitAnswers() {
            if (this.selectedAnswers.length === 0) return;
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            this.cancelCountdown();
            
            try {
                await @this.call('submitAnswers', this.selectedAnswers);
                this.revealed = true;
                
                // Start countdown after successful submission
                if (this.autoProgress) {
                    // Small delay to ensure DOM has updated
                    setTimeout(() => {
                        this.startCountdown();
                    }, 100);
                }
            } finally {
                this.isSubmitting = false;
            }
        },
        
        async continueToNext() {
            if (this.isSubmitting) return;
            
            this.cancelCountdown();
            this.isSubmitting = true;
            
            try {
                this.selectedAnswers = [];
                this.revealed = false;
                await @this.call('continue');
            } finally {
                this.isSubmitting = false;
            }
        },
        
        startCountdown() {
            // Always cancel any existing countdown first
            this.cancelCountdown();
            
            // Track which card this countdown is for
            window.vinoCountdownCardId = this.cardId;
            
            this.countdown = 3;
            const self = this;
            
            window.vinoStudyCountdown = setInterval(() => {
                // Verify we're still on the same card
                if (window.vinoCountdownCardId !== self.cardId) {
                    self.cancelCountdown();
                    return;
                }
                
                self.countdown--;
                if (self.countdown <= 0) {
                    self.continueToNext();
                }
            }, 1000);
        },
        
        cancelCountdown() {
            if (window.vinoStudyCountdown) {
                clearInterval(window.vinoStudyCountdown);
                window.vinoStudyCountdown = null;
            }
            window.vinoCountdownCardId = null;
            this.countdown = 0;
        },
        
        handleKeydown(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (this.isSubmitting) return;
            
            if ((e.key === ' ' || e.key === 'Enter') && !this.revealed) {
                e.preventDefault();
                this.submitAnswers();
            } else if (this.revealed && (e.key === ' ' || e.key === 'Enter')) {
                e.preventDefault();
                this.continueToNext();
            }
        }
    }
}
</script>
