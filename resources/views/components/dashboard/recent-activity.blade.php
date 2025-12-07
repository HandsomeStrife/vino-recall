@props([
    'recentActivityWithCards',
])

<div class="bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Recent Activity</h2>
    <div class="space-y-2">
        @foreach($recentActivityWithCards as $item)
            <div class="flex justify-between items-center py-3 border-b border-gray-200 last:border-0">
                <div class="flex-1">
                    <p class="text-sm text-gray-600">
                        @if($item['activity']->created_at)
                            {{ \Carbon\Carbon::parse($item['activity']->created_at)->format('M d, Y g:i A') }}
                        @else
                            N/A
                        @endif
                    </p>
                    @if($item['card'])
                        <p class="text-gray-900 font-medium">{{ \Illuminate\Support\Str::limit($item['card']->question, 70) }}</p>
                    @endif
                </div>
                @if($item['activity']->is_correct !== null)
                    @if($item['activity']->is_correct)
                        <div class="flex items-center text-green-600 font-semibold ml-4">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Correct
                        </div>
                    @else
                        <div class="flex items-center text-red-600 font-semibold ml-4">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Incorrect
                        </div>
                    @endif
                @else
                    <x-badge.badge :variant="$item['activity']->rating === 'correct' ? 'success' : 'danger'">
                        {{ ucfirst($item['activity']->rating) }}
                    </x-badge.badge>
                @endif
            </div>
        @endforeach
    </div>
</div>






