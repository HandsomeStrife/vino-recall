<div class="relative min-h-screen">
    <div class="absolute top-4 right-4 z-10">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Exit
        </a>
    </div>
    
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
                
                <div class="bg-white rounded-lg shadow-xl p-8 transition-all duration-300" 
                     x-data="{ revealed: @js($revealed) }"
                     x-init="
                        $watch('revealed', value => { revealed = value; });
                        window.addEventListener('keydown', function(e) {
                            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                            if ((e.key === ' ' || e.key === 'Enter') && !revealed) {
                                e.preventDefault();
                                @this.call('submitAnswers');
                            } else if (revealed && (e.key === ' ' || e.key === 'Enter')) {
                                e.preventDefault();
                                @this.call('continue');
                            }
                        });
                     "
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
                            @foreach($card->answer_choices as $index => $choice)
                                <button wire:click="toggleAnswer('{{ $choice }}')" 
                                        class="w-full px-6 py-3 text-left rounded-lg transition flex items-center
                                               {{ in_array($choice, $selectedAnswers) 
                                                  ? 'bg-burgundy-100 border-2 border-burgundy-500' 
                                                  : 'bg-gray-100 hover:bg-burgundy-50 border-2 border-transparent hover:border-burgundy-300' }}">
                                    <span class="flex-shrink-0 w-6 h-6 mr-3 rounded border-2 flex items-center justify-center
                                                 {{ in_array($choice, $selectedAnswers) 
                                                    ? 'bg-burgundy-500 border-burgundy-500' 
                                                    : 'border-gray-400' }}">
                                        @if(in_array($choice, $selectedAnswers))
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="font-semibold text-burgundy-900">{{ chr(65 + $index) }}.</span>
                                    <span class="ml-2">{{ $choice }}</span>
                                </button>
                            @endforeach
                        </div>
                        
                        <div class="text-center mt-6">
                            <button wire:click="submitAnswers" 
                                    class="px-8 py-3 rounded-lg transition font-semibold transform
                                           {{ count($selectedAnswers) > 0 
                                              ? 'bg-burgundy-500 text-white hover:bg-burgundy-600 hover:scale-105' 
                                              : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                                    {{ count($selectedAnswers) === 0 ? 'disabled' : '' }}
                                    title="Press Space or Enter">
                                Submit Answer{{ $card->hasMultipleCorrectAnswers() ? 's' : '' }}
                            </button>
                            <p class="text-sm text-gray-500 mt-4">
                                {{ count($selectedAnswers) > 0 ? 'Press Space or Enter to submit' : 'Select an answer to continue' }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-3 mb-8">
                            @foreach($card->answer_choices as $index => $choice)
                                @php
                                    $isCorrectAnswer = in_array($index, $card->correct_answer_indices ?? []);
                                    $wasSelected = in_array($choice, $selectedAnswers);
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
                                    <span class="font-semibold">{{ chr(65 + $index) }}.</span>
                                    <span class="ml-2">{{ $choice }}</span>
                                    @if($isCorrectAnswer)
                                        <span class="text-green-600 font-semibold ml-auto">Correct</span>
                                    @elseif($wasSelected)
                                        <span class="text-red-600 font-semibold ml-auto">Your Answer</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        @if($isCorrect !== null)
                            <div class="text-center mb-6">
                                @if($isCorrect)
                                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg font-semibold text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Correct!
                                    </div>
                                @else
                                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-lg font-semibold text-lg">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Incorrect
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="text-center">
                            <button wire:click="continue" 
                                    class="px-8 py-3 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold transform hover:scale-105"
                                    title="Press Space or Enter">
                                Continue
                            </button>
                            <p class="text-center text-sm text-gray-500 mt-4">Press Space or Enter to continue</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center max-w-md">
                <div class="mb-6">
                    <svg class="h-20 w-20 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">
                    @if($sessionConfig && $sessionConfig->type->value === 'practice')
                        Practice Session Complete!
                    @elseif($sessionConfig && $sessionConfig->type->value === 'deep_study')
                        Deep Study Complete!
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
                    <a href="{{ route('dashboard') }}" class="inline-block border-2 border-burgundy-500 text-burgundy-500 px-6 py-2 rounded-lg hover:bg-burgundy-50 transition font-semibold">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
