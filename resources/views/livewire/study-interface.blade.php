<div class="p-8 flex items-center justify-center min-h-screen">
        @if($card)
            <div class="max-w-2xl w-full">
                @if($deck)
                    <div class="mb-4 text-center">
                        <x-badge.badge variant="primary">{{ $deck->name }}</x-badge.badge>
                    </div>
                @endif
                <div class="bg-white rounded-lg shadow-xl p-8 transition-all duration-300" 
                     x-data="{ revealed: @js($revealed) }"
                     x-init="
                        $watch('revealed', value => { revealed = value; });
                        window.addEventListener('keydown', function(e) {
                            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                            if ((e.key === ' ' || e.key === 'Enter') && !revealed) {
                                e.preventDefault();
                                @this.call('reveal');
                            } else if (revealed) {
                                if (e.key === '1' || e.key.toLowerCase() === 'a') {
                                    e.preventDefault();
                                    @this.call('rate', 'again');
                                } else if (e.key === '2' || e.key.toLowerCase() === 'h') {
                                    e.preventDefault();
                                    @this.call('rate', 'hard');
                                } else if (e.key === '3' || e.key.toLowerCase() === 'g') {
                                    e.preventDefault();
                                    @this.call('rate', 'good');
                                } else if (e.key === '4' || e.key.toLowerCase() === 'e') {
                                    e.preventDefault();
                                    @this.call('rate', 'easy');
                                }
                            }
                        });
                     "
                     wire:key="card-{{ $card->id }}-{{ $revealed ? 'revealed' : 'hidden' }}">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-burgundy-900 mb-4">{{ $card->question }}</h2>
                        @if($card->image_path)
                            <img src="{{ asset('storage/' . $card->image_path) }}" alt="Card image" class="mx-auto max-h-64 rounded-lg mb-4" loading="lazy">
                        @endif
                    </div>

                    @if($card->card_type->value === 'multiple_choice')
                        @if(!$revealed)
                            <div class="space-y-3">
                                @foreach($card->answer_choices as $index => $choice)
                                    <button wire:click="selectAnswer('{{ $choice }}')" 
                                            class="w-full px-6 py-3 text-left bg-gray-100 hover:bg-burgundy-100 border-2 border-transparent hover:border-burgundy-500 rounded-lg transition">
                                        <span class="font-semibold text-burgundy-900">{{ chr(65 + $index) }}.</span> {{ $choice }}
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="space-y-3 mb-8">
                                @foreach($card->answer_choices as $index => $choice)
                                    <div class="w-full px-6 py-3 text-left rounded-lg border-2 
                                                {{ $index === $card->correct_answer_index ? 'bg-green-100 border-green-500' : 'bg-gray-100 border-gray-300' }}
                                                {{ $selectedAnswer === $choice && $index !== $card->correct_answer_index ? 'bg-red-100 border-red-500' : '' }}">
                                        <span class="font-semibold">{{ chr(65 + $index) }}.</span> {{ $choice }}
                                        @if($index === $card->correct_answer_index)
                                            <span class="text-green-600 font-semibold ml-2">✓ Correct</span>
                                        @elseif($selectedAnswer === $choice)
                                            <span class="text-red-600 font-semibold ml-2">✗ Your Answer</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button wire:click="rate('again')" 
                                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition transform hover:scale-105"
                                        title="Press 1 or A">
                                    Again <span class="text-xs opacity-75">(1/A)</span>
                                </button>
                                <button wire:click="rate('hard')" 
                                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition transform hover:scale-105"
                                        title="Press 2 or H">
                                    Hard <span class="text-xs opacity-75">(2/H)</span>
                                </button>
                                <button wire:click="rate('good')" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition transform hover:scale-105"
                                        title="Press 3 or G">
                                    Good <span class="text-xs opacity-75">(3/G)</span>
                                </button>
                                <button wire:click="rate('easy')" 
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition transform hover:scale-105"
                                        title="Press 4 or E">
                                    Easy <span class="text-xs opacity-75">(4/E)</span>
                                </button>
                            </div>
                            <p class="text-center text-sm text-gray-500 mt-4">Use keyboard shortcuts: 1-4 or A/H/G/E to rate</p>
                        @endif
                    @else
                        @if($revealed)
                            <div class="text-center mb-8 transition-all duration-300">
                                <p class="text-xl text-gray-700">{{ $card->answer }}</p>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button wire:click="rate('again')" 
                                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition transform hover:scale-105"
                                        title="Press 1 or A">
                                    Again <span class="text-xs opacity-75">(1/A)</span>
                                </button>
                                <button wire:click="rate('hard')" 
                                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition transform hover:scale-105"
                                        title="Press 2 or H">
                                    Hard <span class="text-xs opacity-75">(2/H)</span>
                                </button>
                                <button wire:click="rate('good')" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition transform hover:scale-105"
                                        title="Press 3 or G">
                                    Good <span class="text-xs opacity-75">(3/G)</span>
                                </button>
                                <button wire:click="rate('easy')" 
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition transform hover:scale-105"
                                        title="Press 4 or E">
                                    Easy <span class="text-xs opacity-75">(4/E)</span>
                                </button>
                            </div>
                            <p class="text-center text-sm text-gray-500 mt-4">Use keyboard shortcuts: Space/Enter to reveal, 1-4 or A/H/G/E to rate</p>
                        @else
                            <div class="text-center">
                                <button wire:click="reveal" 
                                        class="px-8 py-3 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold transform hover:scale-105"
                                        title="Press Space or Enter">
                                    Reveal Answer
                                </button>
                                <p class="text-sm text-gray-500 mt-4">Press Space or Enter to reveal</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @else
            <div class="text-center max-w-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">
                    @if($deck)
                        No cards available in this deck
                    @else
                        No cards due for review
                    @endif
                </h2>
                <p class="text-gray-600 mb-6">
                    @if($deck)
                        You've completed all cards in this deck. Great job!
                    @else
                        Great job! You're all caught up.
                    @endif
                </p>
                <a href="{{ route('library') }}" class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">
                    Browse Library
                </a>
            </div>
        @endif
    </div>

