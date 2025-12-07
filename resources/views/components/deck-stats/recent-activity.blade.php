@props([
    'recentActivity',
])

@if($recentActivity->isNotEmpty())
    <div class="bg-white p-8 rounded-2xl border border-gray-300 shadow-sm">
        <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Recent Activity</h2>
        <div class="space-y-2">
            @foreach($recentActivity as $item)
                <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-1">
                            @if($item['history']->reviewed_at)
                                {{ \Carbon\Carbon::parse($item['history']->reviewed_at)->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </p>
                        @if($item['card'])
                            <p class="text-gray-900 font-medium">{{ \Illuminate\Support\Str::limit($item['card']->question, 60) }}</p>
                        @endif
                    </div>
                    @if($item['history']->is_correct)
                        <span class="text-green-600 text-xl font-bold ml-4">&#x2713;</span>
                    @else
                        <span class="text-red-500 text-xl font-bold ml-4">&#x2717;</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
