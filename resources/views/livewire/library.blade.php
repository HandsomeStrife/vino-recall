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
        <!-- Category Filter -->
        @if($categories->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Filter by Category</h3>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="filterByCategory(null)" 
                            class="px-4 py-2 rounded-lg transition {{ $selectedCategoryId === null ? 'bg-burgundy-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                        All
                    </button>
                    @foreach($categories as $category)
                        <button wire:click="filterByCategory({{ $category->id }})" 
                                class="px-4 py-2 rounded-lg transition {{ $selectedCategoryId === $category->id ? 'bg-burgundy-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Collections Section -->
        @if($collections->isNotEmpty())
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-purple-100 rounded-lg p-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Collections</h2>
                    <span class="text-sm text-gray-500">({{ $collections->count() }})</span>
                </div>
                
                <div class="space-y-6">
                    @foreach($collections as $deckStat)
                        @php
                            $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                            $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                        @endphp
                        
                        <div class="bg-white rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
                            <!-- Collection Header -->
                            <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $deckColor }};">
                                <div class="flex items-center gap-3">
                                    <div class="bg-white/20 rounded-lg p-2">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                                        <span class="text-sm text-white/80">{{ $deckStat['childCount'] }} decks - {{ $deckStat['totalCards'] }} cards total</span>
                                    </div>
                                </div>
                                @if($deckStat['isEnrolled'])
                                    <span class="px-3 py-1 text-sm rounded-lg bg-white/20 text-white font-medium">
                                        Enrolled
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Collection Description & Actions -->
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        @if($deckStat['deck']->description)
                                            <p class="text-gray-600 text-sm mb-2">{{ $deckStat['deck']->description }}</p>
                                        @endif
                                        @if($deckStat['isEnrolled'] && $deckStat['progress'] > 0)
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 max-w-xs bg-gray-200 rounded-full h-2">
                                                    <div class="bg-burgundy-500 h-2 rounded-full" style="width: {{ $deckStat['progress'] }}%"></div>
                                                </div>
                                                <span class="text-sm text-gray-600">{{ $deckStat['progress'] }}% complete</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        @if($deckStat['isEnrolled'])
                                            <button wire:click="unenrollFromDeck({{ $deckStat['deck']->id }})" 
                                                    class="inline-block bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                                Remove Collection
                                            </button>
                                        @else
                                            <button wire:click="enrollInDeck({{ $deckStat['deck']->id }})" 
                                                    class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                Add Collection to Library
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Child Decks Grid -->
                            @if($deckStat['childCount'] > 0)
                                <div class="p-6">
                                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Included Decks</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($deckStat['children'] as $childStat)
                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-burgundy-300 transition">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-16 h-16 rounded-lg bg-cover bg-center flex-shrink-0 border border-gray-200" 
                                                         style="background-image: url('{{ $childStat['image'] }}');"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <h5 class="font-semibold text-gray-900 truncate">{{ $childStat['deck']->name }}</h5>
                                                        <p class="text-sm text-gray-500">{{ $childStat['totalCards'] }} cards</p>
                                                        @if($childStat['isEnrolled'] && $childStat['progress'] > 0)
                                                            <div class="mt-2 flex items-center gap-2">
                                                                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                                                    <div class="bg-burgundy-500 h-1.5 rounded-full" style="width: {{ $childStat['progress'] }}%"></div>
                                                                </div>
                                                                <span class="text-xs text-gray-500">{{ $childStat['progress'] }}%</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="p-6 text-center text-gray-500">
                                    <p>No decks in this collection yet.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Standalone Decks Section -->
        @if($standaloneDecks->isNotEmpty())
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gray-100 rounded-lg p-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Standalone Decks</h2>
                    <span class="text-sm text-gray-500">({{ $standaloneDecks->count() }})</span>
                </div>
                
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    @foreach($standaloneDecks as $deckStat)
                        @php
                            $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                            $deckColor = $colors[$deckStat['deck']->id % count($colors)];
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
                                        Enrolled
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
            </div>
        @endif

        <!-- Empty State -->
        @if($collections->isEmpty() && $standaloneDecks->isEmpty())
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                @if($selectedCategoryId !== null)
                    <p class="text-gray-600">No decks found in this category.</p>
                    <button wire:click="filterByCategory(null)" class="mt-4 text-burgundy-500 hover:text-burgundy-600 font-medium">
                        Clear filter
                    </button>
                @else
                    <p class="text-gray-600">{{ __('library.no_decks') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
