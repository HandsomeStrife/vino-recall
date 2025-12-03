@props([
    'deck',
    'deckColor' => '#9E3B4D',
    'image',
    'dueCards' => 0,
    'newCards' => 0,
    'reviewedCount' => 0,
    'retentionRate' => 0,
    'nextReviewTime' => null,
])

<div class="bg-white rounded-lg overflow-hidden relative border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
    <!-- Colored Title Banner -->
    <div class="px-6 py-3 flex items-center justify-between" style="background-color: {{ $deckColor }};">
        <h3 class="text-lg font-bold text-white">{{ $deck->name }}</h3>
        <a href="{{ route('deck.stats', ['shortcode' => $deck->shortcode]) }}" 
           class="p-2 hover:bg-white/20 rounded-lg transition-colors" 
           title="View Stats">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
        </a>
    </div>
    
    <div class="flex relative flex-1">
        <!-- Content Section - with proper padding to avoid image overlap -->
        <div class="flex-1 p-6 pr-64 flex flex-col justify-between">
            <div class="mb-4">
                @if($dueCards > 0 && $newCards > 0)
                    <!-- Both due and new cards -->
                    <div class="text-xs font-semibold text-gray-600 uppercase mb-1">Available Today:</div>
                    <div class="text-3xl font-bold text-burgundy-500">
                        {{ $dueCards + $newCards }} <span class="text-lg font-normal text-gray-600">Cards</span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">{{ $dueCards }} due · {{ $newCards }} new</div>
                @elseif($dueCards > 0)
                    <!-- Only due cards -->
                    <div class="text-xs font-semibold text-gray-600 uppercase mb-1">Due Today:</div>
                    <div class="text-3xl font-bold text-burgundy-500">
                        {{ $dueCards }} <span class="text-lg font-normal text-gray-600">Cards</span>
                    </div>
                @elseif($newCards > 0)
                    <!-- Only new cards -->
                    <div class="text-xs font-semibold text-gray-600 uppercase mb-1">New Cards:</div>
                    <div class="text-3xl font-bold text-burgundy-500">
                        {{ $newCards }} <span class="text-lg font-normal text-gray-600">Cards</span>
                    </div>
                @else
                    <!-- No cards available -->
                    <div class="text-xs font-semibold text-gray-600 uppercase mb-1">Status:</div>
                    <div class="text-xl font-bold text-green-600">
                        All Caught Up!
                    </div>
                    @if($nextReviewTime)
                        <div class="text-sm text-gray-500 mt-1">Next review in {{ $nextReviewTime }}</div>
                    @endif
                @endif
            </div>

            <div class="max-w-xs mb-4">
                @if($reviewedCount > 0)
                    <div class="text-sm text-gray-600 mb-2 font-semibold">{{ $retentionRate }}% Retention Rate</div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all" style="width: {{ $retentionRate }}%; background-color: {{ $deckColor }};"></div>
                    </div>
                @else
                    <div class="text-sm text-gray-600 mb-2 font-semibold">Start Learning</div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 bg-gray-400 rounded-full" style="width: 100%;"></div>
                    </div>
                @endif
            </div>

            <x-study-session-modal 
                :deckId="$deck->shortcode" 
                :deckName="$deck->name"
                :dueCount="$dueCards"
                :newCount="$newCards"
                :reviewedCount="$reviewedCount"
                :totalCount="$dueCards + $newCards"
                class="w-full" />
        </div>
        
        <!-- Diagonal Image Section - full height -->
        <div class="absolute top-0 right-0 bottom-0 w-56 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-cover bg-center" 
                 style="background-image: url('{{ $image }}'); 
                        clip-path: polygon(20% 0, 100% 0, 100% 100%, 0 100%);">
            </div>
        </div>
    </div>
</div>

