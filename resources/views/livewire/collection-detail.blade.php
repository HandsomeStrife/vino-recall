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
                
                @if($isEnrolled)
                    <span class="px-4 py-2 bg-green-500/20 text-green-200 rounded-lg font-medium">
                        Enrolled
                    </span>
                @endif
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($childDecksWithStats as $deckStat)
                    @php
                        $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                        $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                    @endphp
                    
                    <div class="bg-white rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
                        <!-- Deck Header -->
                        <div class="px-6 py-3 flex items-center justify-between" style="background-color: {{ $deckColor }};">
                            <h3 class="text-lg font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                            @if($deckStat['isEnrolled'] && $deckStat['shortcode'])
                                <a href="{{ route('deck.stats', ['shortcode' => $deckStat['shortcode']]) }}" 
                                   class="p-2 hover:bg-white/20 rounded-lg transition-colors" 
                                   title="View Stats">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        
                        <!-- Deck Content -->
                        <div class="p-6">
                            @if($deckStat['deck']->description)
                                <p class="text-gray-600 text-sm mb-4">{{ $deckStat['deck']->description }}</p>
                            @endif
                            
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold {{ $deckStat['dueCards'] > 0 ? 'text-burgundy-500' : 'text-gray-600' }}">
                                        {{ $deckStat['dueCards'] }}
                                    </div>
                                    <div class="text-xs text-gray-500">Due</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-600">{{ $deckStat['newCards'] }}</div>
                                    <div class="text-xs text-gray-500">New</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-600">{{ $deckStat['totalCards'] }}</div>
                                    <div class="text-xs text-gray-500">Total</div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Progress</span>
                                    <span>{{ $deckStat['progress'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $deckStat['progress'] }}%; background-color: {{ $deckColor }};"></div>
                                </div>
                            </div>

                            <!-- Retention Rate -->
                            @if($deckStat['reviewedCount'] > 0)
                                <div class="text-sm text-gray-600 mb-4">
                                    {{ $deckStat['retentionRate'] }}% Retention Rate
                                </div>
                            @endif

                            <!-- Action Button -->
                            @if($deckStat['isEnrolled'] && $deckStat['shortcode'])
                                <x-study-session-modal 
                                    :deckId="$deckStat['shortcode']" 
                                    :deckName="$deckStat['deck']->name"
                                    :dueCount="$deckStat['dueCards']"
                                    :newCount="$deckStat['newCards']"
                                    :reviewedCount="$deckStat['reviewedCount']"
                                    :totalCount="$deckStat['dueCards'] + $deckStat['newCards']"
                                    class="w-full" />
                            @else
                                <div class="text-center text-gray-500 text-sm py-2">
                                    Enroll in the collection to study
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

