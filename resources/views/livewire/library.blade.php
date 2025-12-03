<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">{{ __('library.title') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('library.browse_decks') }}</p>

        @if($categories->isNotEmpty())
            <div class="mb-6 flex flex-wrap gap-2">
                <button wire:click="filterByCategory(null)" 
                        class="px-4 py-2 rounded-lg transition {{ $selectedCategory === null ? 'bg-burgundy-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    {{ __('library.all_decks') }}
                </button>
                @foreach($categories as $cat)
                    <button wire:click="filterByCategory('{{ $cat }}')" 
                            class="px-4 py-2 rounded-lg transition {{ $selectedCategory === $cat ? 'bg-burgundy-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ ucfirst($cat) }}
                    </button>
                @endforeach
            </div>
        @endif

        @if($decksWithStats->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($decksWithStats as $deckStat)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-burgundy-900">{{ $deckStat['deck']->name }}</h3>
                                @if($deckStat['deck']->category)
                                    <span class="px-2 py-1 text-xs rounded bg-burgundy-100 text-burgundy-800">
                                        {{ ucfirst($deckStat['deck']->category) }}
                                    </span>
                                @endif
                            </div>
                            @if($deckStat['deck']->description)
                                <p class="text-gray-600 mb-4">{{ $deckStat['deck']->description }}</p>
                            @endif
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>{{ __('library.progress') }}</span>
                                    <span>{{ $deckStat['reviewedCount'] }} / {{ $deckStat['totalCards'] }} {{ __('library.cards') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-burgundy-500 h-2 rounded-full transition-all" style="width: {{ $deckStat['progress'] }}%"></div>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <a href="{{ route('study', ['deck' => $deckStat['deck']->id]) }}" class="flex-1 text-center bg-burgundy-500 text-white px-4 py-2 rounded-lg hover:bg-burgundy-600 transition">
                                    {{ __('library.study_deck') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <p class="text-gray-600">{{ __('library.no_decks') }}</p>
            </div>
        @endif
    </div>

