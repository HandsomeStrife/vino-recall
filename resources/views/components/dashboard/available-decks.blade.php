@props([
    'availableDecks',
])

<div class="bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Available Decks</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($availableDecks as $deck)
            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                <div class="h-32 bg-cover bg-center" style="background-image: url('{{ Domain\Deck\Helpers\DeckImageHelper::getImagePath($deck) }}');"></div>
                <div class="p-4">
                    <h3 class="text-lg font-bold text-burgundy-900 mb-2">{{ $deck->name }}</h3>
                    @if($deck->description)
                        <p class="text-sm text-gray-600 mb-4">{{ \Illuminate\Support\Str::limit($deck->description, 80) }}</p>
                    @endif
                    <a href="{{ route('library') }}" class="block text-center bg-burgundy-500 text-white px-4 py-2 rounded-lg hover:bg-burgundy-600 transition font-medium">
                        View in Library
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>






