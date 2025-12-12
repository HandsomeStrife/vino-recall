<div class="min-h-screen bg-cream-50">
    <!-- Hero Section -->
    <div class="relative bg-cover bg-center py-12" style="background-image: linear-gradient(rgba(78, 35, 48, 0.85), rgba(78, 35, 48, 0.85)), url('{{ $image }}');">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Link -->
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-white/80 hover:text-white mb-6 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
            
            <div class="flex items-start justify-between">
                <div>
                    <!-- Collection Badge -->
                    <div class="inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-lg mb-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="text-white text-sm font-medium">Collection</span>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">{{ $collection->name }}</h1>
                    @if($collection->description)
                        <p class="text-lg text-white/90 max-w-2xl">{{ $collection->description }}</p>
                    @endif
                </div>
                
                <div class="flex flex-col items-end gap-2">
                    @if($isEnrolled)
                        <span class="px-4 py-2 bg-green-500/20 text-green-200 rounded-lg font-medium">
                            Enrolled
                        </span>
                    @else
                        <button wire:click="enrollInCollection" 
                                class="bg-white text-burgundy-600 px-6 py-3 rounded-lg hover:bg-cream-50 transition font-semibold shadow-md hover:shadow-lg">
                            Enroll in Entire Collection
                        </button>
                    @endif
                </div>
            </div>

            <!-- Collection Stats -->
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/10 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ $childDecksWithStats->count() }}</div>
                    <div class="text-sm text-white/70">Decks</div>
                </div>
                <div class="bg-white/10 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ $totalCards }}</div>
                    <div class="text-sm text-white/70">Total Cards</div>
                </div>
                <div class="bg-white/10 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold {{ $dueCards > 0 ? 'text-yellow-300' : 'text-white' }}">{{ $dueCards }}</div>
                    <div class="text-sm text-white/70">Due Today</div>
                </div>
                <div class="bg-white/10 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-white">{{ $progress }}%</div>
                    <div class="text-sm text-white/70">Complete</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Child Decks -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Included Decks</h2>
        
        @if($childDecksWithStats->isEmpty())
            <div class="bg-white p-8 rounded-xl shadow-md text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="text-gray-600">No decks in this collection yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @foreach($childDecksWithStats as $deckStat)
                    @php
                        $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                        $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                    @endphp
                    
                    <div class="bg-white rounded-lg overflow-hidden relative border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
                        <!-- Colored Title Banner -->
                        <div class="px-6 py-3" style="background-color: {{ $deckColor }};">
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                            </div>
                        </div>
                        
                        <div class="flex relative flex-1">
                            <!-- Content Section -->
                            <div class="flex-1 p-6 pr-64 flex flex-col">
                                <div class="flex-1">
                                    @if($deckStat['deck']->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $deckStat['deck']->description }}</p>
                                    @endif
                                    
                                    @if($deckStat['reviewedCount'] === 0)
                                        <!-- Deck not started yet -->
                                        <div class="text-sm text-gray-600 mb-2">Ready to begin</div>
                                        <div class="text-2xl font-bold text-burgundy-500 mb-4">{{ $deckStat['newCards'] }}</div>
                                        <div class="text-xs text-gray-500">Cards ready to learn</div>
                                    @else
                                        <!-- Deck already started - show stats grid -->
                                        <div class="grid grid-cols-3 gap-2 mb-4">
                                            <div class="text-center">
                                                <div class="text-xl font-bold {{ $deckStat['dueCards'] > 0 ? 'text-burgundy-500' : 'text-gray-600' }}">
                                                    {{ $deckStat['dueCards'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">Due</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-xl font-bold text-gray-600">{{ $deckStat['newCards'] }}</div>
                                                <div class="text-xs text-gray-500">New</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-xl font-bold text-gray-600">{{ $deckStat['totalCards'] }}</div>
                                                <div class="text-xs text-gray-500">Total</div>
                                            </div>
                                        </div>
                                        
                                        @if($deckStat['progress'] > 0)
                                            <div class="text-sm text-gray-600 mb-2">{{ $deckStat['progress'] }}% complete</div>
                                        @endif
                                        
                                        <div class="text-sm text-gray-500">{{ $deckStat['retentionRate'] }}% Retention Rate</div>
                                    @endif
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="mt-auto pt-4 flex flex-col gap-2">
                                    @if($deckStat['isEnrolled'] && $deckStat['shortcode'])
                                        @if($deckStat['reviewedCount'] === 0)
                                            <!-- Start Now button for unstarted decks -->
                                            <a href="{{ route('study', ['type' => 'normal', 'deck' => $deckStat['shortcode']]) }}" 
                                               class="inline-flex items-center justify-center text-center bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                </svg>
                                                Start Now
                                            </a>
                                            @if(!$isEnrolled)
                                                <button wire:click="unenrollFromChildDeck({{ $deckStat['deck']->id }})" 
                                                        class="inline-block text-center bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                                    Remove from My Decks
                                                </button>
                                            @endif
                                        @else
                                            <!-- Standard buttons for started decks -->
                                            <a href="{{ route('study', ['type' => 'normal', 'deck' => $deckStat['shortcode']]) }}" 
                                               class="inline-block text-center bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                Start Session
                                            </a>
                                            <a href="{{ route('deck.stats', $deckStat['shortcode']) }}" 
                                               class="inline-block text-center bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                                                View Stats
                                            </a>
                                            @if(!$isEnrolled)
                                                <button wire:click="unenrollFromChildDeck({{ $deckStat['deck']->id }})" 
                                                        class="inline-block text-center bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                                    Remove from My Decks
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        @if(!$isEnrolled)
                                            <button wire:click="enrollInChildDeck({{ $deckStat['deck']->id }})" 
                                                    class="inline-block text-center bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                Add to My Decks
                                            </button>
                                        @else
                                            <div class="text-center text-gray-500 text-sm py-2">
                                                Part of enrolled collection
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Diagonal Image Section -->
                            <div class="absolute top-0 right-0 bottom-0 w-56 overflow-hidden pointer-events-none">
                                <div class="absolute inset-0 bg-cover bg-center" 
                                     style="background-image: url('{{ $deckStat['image'] }}'); 
                                            clip-path: polygon(20% 0, 100% 0, 100% 100%, 0 100%);">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

