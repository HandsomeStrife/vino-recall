@props([
    'otherDecks',
])

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Other Decks</h2>
    <div class="space-y-3">
        @foreach($otherDecks as $deckStat)
            <a href="{{ route('deck.stats', ['shortcode' => $deckStat['deck']->shortcode]) }}" 
               class="block relative overflow-hidden p-3 border-2 border-gray-200 rounded-lg hover:border-burgundy-500 transition">
                <!-- Background Image with Overlay -->
                <div class="absolute inset-0 bg-cover bg-center opacity-20" 
                     style="background-image: url('{{ $deckStat['image'] }}');"></div>
                
                <!-- Content -->
                <div class="relative">
                    <h3 class="font-semibold text-burgundy-900 text-sm mb-1">{{ $deckStat['deck']->name }}</h3>
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        @if($deckStat['dueCards'] > 0)
                            <span class="text-burgundy-600 font-medium">{{ $deckStat['dueCards'] }} due</span>
                        @elseif($deckStat['newCards'] > 0)
                            <span class="text-gray-600">{{ $deckStat['newCards'] }} new</span>
                        @else
                            <span class="text-green-600">Up to date</span>
                        @endif
                        <span>{{ $deckStat['retentionRate'] }}%</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

