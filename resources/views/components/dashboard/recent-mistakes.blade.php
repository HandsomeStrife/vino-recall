@props([
    'mistakesWithCards',
    'dueCardsCount',
])

<div class="bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Recent Mistakes</h2>
    @if($mistakesWithCards->isNotEmpty())
        <div class="space-y-3">
            @foreach($mistakesWithCards->take(5) as $item)
                <div class="flex items-start gap-3 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        @if($item['card'])
                            <p class="text-sm font-medium text-gray-900 truncate">{{ \Illuminate\Support\Str::limit($item['card']->question, 60) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($item['history']->reviewed_at)->format('M d, g:i A') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if($dueCardsCount > 0)
            <a href="{{ route('enrolled') }}" class="mt-4 block text-center bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition font-medium">
                Review Mistakes
            </a>
        @endif
    @else
        <div class="text-center py-12">
            <img src="{{ asset('img/you-go.png') }}" alt="No recent mistakes" class="w-32 h-32 mx-auto mb-4">
            <p class="text-gray-600 font-medium">No recent mistakes</p>
            <p class="text-sm text-gray-500 mt-1">Keep up the great work!</p>
        </div>
    @endif
</div>
