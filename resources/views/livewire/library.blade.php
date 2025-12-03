<div class="min-h-screen bg-cream-50">
    <!-- Hero Section -->
    <div class="relative bg-cover bg-center py-12" style="background-image: linear-gradient(rgba(78, 35, 48, 0.85), rgba(78, 35, 48, 0.85)), url('{{ asset('img/defaults/3.jpg') }}');">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
            <h1 class="text-4xl md:text-5xl font-bold font-heading mb-2">{{ __('library.title') }}</h1>
            <p class="text-lg md:text-xl text-white/90">{{ __('library.browse_decks') }}</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($decksWithStats as $deckStat)
                    @php
                        // Define deck colors (can be stored in deck table later)
                        $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                        $deckColor = $deckStat['deck']->category ? $colors[crc32($deckStat['deck']->category) % count($colors)] : $colors[$deckStat['deck']->id % count($colors)];
                        $image = \Domain\Deck\Helpers\DeckImageHelper::getImagePath($deckStat['deck']);
                    @endphp
                    
                    <x-library-card 
                        :deck="$deckStat['deck']"
                        :deckColor="$deckColor"
                        :image="$image"
                        :totalCards="$deckStat['totalCards']"
                        :description="$deckStat['deck']->description"
                        :isEnrolled="$deckStat['isEnrolled']"
                        :progress="$deckStat['progress']"
                    >
                        @if($deckStat['isEnrolled'])
                            <x-slot:enrolledBadge>
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 font-medium">
                                    ✓ Enrolled
                                </span>
                            </x-slot:enrolledBadge>
                            <x-slot:bottomAction>
                                <button wire:click="unenrollFromDeck({{ $deckStat['deck']->id }})" 
                                        class="inline-block bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                    Remove from Library
                                </button>
                            </x-slot:bottomAction>
                        @else
                            <x-slot:bottomAction>
                                <button wire:click="enrollInDeck({{ $deckStat['deck']->id }})" 
                                        class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                    Add to My Library
                                </button>
                            </x-slot:bottomAction>
                        @endif
                    </x-library-card>
                @endforeach
            </div>
        @else
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <p class="text-gray-600">{{ __('library.no_decks') }}</p>
            </div>
        @endif
    </div>
</div>

