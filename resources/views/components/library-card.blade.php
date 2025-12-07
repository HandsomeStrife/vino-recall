@props([
    'deck',
    'deckColor' => '#9E3B4D',
    'image',
    'totalCards' => 0,
    'description' => null,
    'isEnrolled' => false,
    'progress' => 0,
])

<div class="bg-white rounded-lg overflow-hidden relative border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
    <!-- Colored Title Banner with Enrolled Badge -->
    <div class="px-6 py-3 flex items-center justify-between" style="background-color: {{ $deckColor }};">
        <div>
            <h3 class="text-lg font-bold text-white">{{ $deck->name }}</h3>
            @if($deck->categories && $deck->categories->isNotEmpty())
                <span class="text-xs text-white/80">{{ $deck->categories->first()->name }}</span>
            @endif
        </div>
        @if($isEnrolled)
            <div class="shrink-0">
                {{ $enrolledBadge ?? '' }}
            </div>
        @endif
    </div>
    
    <div class="flex relative flex-1">
        <!-- Content Section - with proper padding to avoid image overlap -->
        <div class="flex-1 p-6 pr-64 flex flex-col">
            <div class="flex-1">
                @if($description)
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $description }}</p>
                @endif
                
                <div class="text-sm text-gray-500 mb-4">
                    <span class="font-semibold text-burgundy-900">{{ $totalCards }}</span> {{ $totalCards === 1 ? 'card' : 'cards' }}
                </div>
                
                @if($isEnrolled && $progress > 0)
                    <div class="text-sm text-gray-600 mb-4">{{ $progress }}% complete</div>
                @endif
            </div>
            
            <!-- Bottom Action Button -->
            <div class="mt-auto pt-4">
                {{ $bottomAction ?? '' }}
            </div>
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

