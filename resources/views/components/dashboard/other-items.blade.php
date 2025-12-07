@props([
    'otherItems',
])

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Other Decks</h2>
    <div class="space-y-3">
        @foreach($otherItems as $item)
            @if($item['type'] === 'collection')
                <a href="{{ route('collection.show', ['id' => $item['deck']->id]) }}" 
                   class="block relative overflow-hidden p-3 border-2 border-purple-200 rounded-lg hover:border-purple-500 transition bg-purple-50/50">
                    <!-- Background Image with Overlay -->
                    <div class="absolute inset-0 bg-cover bg-center opacity-10" 
                         style="background-image: url('{{ $item['image'] }}');"></div>
                    
                    <!-- Content -->
                    <div class="relative">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="font-semibold text-burgundy-900 text-sm">{{ $item['deck']->name }}</h3>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            @if($item['dueCards'] > 0)
                                <span class="text-burgundy-600 font-medium">{{ $item['dueCards'] }} due</span>
                            @elseif($item['newCards'] > 0)
                                <span class="text-gray-600">{{ $item['newCards'] }} new</span>
                            @else
                                <span class="text-green-600">Up to date</span>
                            @endif
                            <span class="text-purple-600">{{ $item['childCount'] }} decks</span>
                        </div>
                    </div>
                </a>
            @else
                <a href="{{ route('deck.stats', ['shortcode' => $item['deck']->shortcode]) }}" 
                   class="block relative overflow-hidden p-3 border-2 border-gray-200 rounded-lg hover:border-burgundy-500 transition">
                    <!-- Background Image with Overlay -->
                    <div class="absolute inset-0 bg-cover bg-center opacity-20" 
                         style="background-image: url('{{ $item['image'] }}');"></div>
                    
                    <!-- Content -->
                    <div class="relative">
                        <h3 class="font-semibold text-burgundy-900 text-sm mb-1">{{ $item['deck']->name }}</h3>
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            @if($item['dueCards'] > 0)
                                <span class="text-burgundy-600 font-medium">{{ $item['dueCards'] }} due</span>
                            @elseif($item['newCards'] > 0)
                                <span class="text-gray-600">{{ $item['newCards'] }} new</span>
                            @else
                                <span class="text-green-600">Up to date</span>
                            @endif
                            <span>{{ $item['retentionRate'] }}%</span>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>

