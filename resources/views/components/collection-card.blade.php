@props([
    'collection',
    'collectionColor' => '#9E3B4D',
    'image',
    'dueCards' => 0,
    'newCards' => 0,
    'totalCards' => 0,
    'childCount' => 0,
    'progress' => 0,
])

<a href="{{ route('collection.show', ['id' => $collection->id]) }}" 
   class="block bg-white rounded-lg overflow-hidden border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-all hover:-translate-y-1">
    <!-- Colored Title Banner -->
    <div class="px-6 py-3 flex items-center justify-between" style="background-color: {{ $collectionColor }};">
        <div class="flex items-center gap-2">
            <div class="bg-white/20 rounded p-1">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-white">{{ $collection->name }}</h3>
        </div>
        <span class="px-2 py-1 bg-white/20 rounded text-xs text-white font-medium">
            {{ $childCount }} decks
        </span>
    </div>
    
    <div class="flex relative">
        <!-- Content Section -->
        <div class="flex-1 p-6 pr-64 flex flex-col">
            <div class="flex-1">
                <div class="mb-4">
                    @if($dueCards > 0 && $newCards > 0)
                        <!-- Both due and new cards -->
                        <div class="text-xs font-semibold text-gray-600 uppercase mb-1">Available Today:</div>
                        <div class="text-3xl font-bold text-burgundy-500">
                            {{ $dueCards + $newCards }} <span class="text-lg font-normal text-gray-600">Cards</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">{{ $dueCards }} due / {{ $newCards }} new</div>
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
                    @endif
                </div>

                <div class="text-sm text-gray-600 mb-4">{{ $progress }}% Complete ({{ $totalCards }} total cards)</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4 max-w-xs">
                    <div class="h-2 rounded-full transition-all" style="width: {{ $progress }}%; background-color: {{ $collectionColor }};"></div>
                </div>
            </div>

            <div class="flex items-center text-burgundy-600 font-semibold mt-auto pt-4">
                <span>View Collection</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>
        
        <!-- Diagonal Image Section -->
        <div class="absolute top-0 right-0 bottom-0 w-56 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-cover bg-center" 
                 style="background-image: url('{{ $image }}'); 
                        clip-path: polygon(20% 0, 100% 0, 100% 100%, 0 100%);">
            </div>
        </div>
    </div>
</a>

