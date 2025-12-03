@props([
    'deck',
    'deckColor' => '#9E3B4D',
    'image',
    'dueCards' => 0,
    'newCards' => 0,
    'retentionRate' => 0,
])

<div class="block bg-white rounded-lg overflow-hidden relative border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
    <!-- Colored Title Banner -->
    <div class="px-6 py-3" style="background-color: {{ $deckColor }};">
        <h3 class="text-lg font-bold text-white">{{ $deck->name }}</h3>
    </div>
    
    <div class="flex relative flex-1">
        <!-- Content Section - with proper padding to avoid image overlap -->
        <div class="flex-1 p-6 pr-64 flex flex-col justify-between">
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-600 uppercase mb-1">Due Today:</div>
                <div class="text-3xl font-bold text-burgundy-500">
                    {{ $dueCards }} <span class="text-lg font-normal text-gray-600">Cards</span>
                </div>
            </div>

            <div class="max-w-xs mb-4">
                <div class="text-sm text-gray-600 mb-2 font-semibold">{{ $retentionRate }}% Retention Rate</div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all" style="width: {{ $retentionRate }}%; background-color: {{ $deckColor }};"></div>
                </div>
            </div>

            <x-study-session-modal 
                :deckId="$deck->shortcode" 
                :deckName="$deck->name"
                :dueCount="$dueCards"
                :newCount="$newCards"
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

