@props([
    'todayReviews',
    'dailyGoal',
    'dailyGoalProgress',
    'weekActivity',
])

<div class="bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Daily Goal</h2>
    <div class="flex flex-col items-center justify-center py-4">
        <!-- Circular Progress -->
        <div class="relative w-40 h-40 mb-6">
            <svg class="w-40 h-40 transform -rotate-90">
                <circle cx="80" cy="80" r="70" stroke="#e5e7eb" stroke-width="12" fill="none" />
                <circle 
                    cx="80" 
                    cy="80" 
                    r="70" 
                    stroke="#9E3B4D" 
                    stroke-width="12" 
                    fill="none"
                    stroke-dasharray="{{ 2 * 3.14159 * 70 }}"
                    stroke-dashoffset="{{ 2 * 3.14159 * 70 * (1 - $dailyGoalProgress / 100) }}"
                    stroke-linecap="round"
                    class="transition-all duration-500"
                />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-4xl font-bold text-burgundy-500">{{ $todayReviews }}</div>
                    <div class="text-gray-400 text-lg">/{{ $dailyGoal }}</div>
                </div>
            </div>
        </div>
        
        <p class="text-gray-600 text-center mb-4">
            @if($todayReviews >= $dailyGoal)
                <span class="text-dark-green-900 font-semibold">Daily goal achieved!</span>
            @else
                <span class="font-medium">{{ $dailyGoal - $todayReviews }} more to go</span>
            @endif
        </p>
        
        <!-- Week Streak Dots -->
        <div class="flex gap-2">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $index => $day)
                @php
                    $dayData = $weekActivity[$index] ?? null;
                    $completed = $dayData ? $dayData['completed'] : false;
                @endphp
                <div class="flex flex-col items-center">
                    <span class="text-xs text-gray-500 mb-1">{{ $day }}</span>
                    <div class="w-3 h-3 rounded-full {{ $completed ? 'bg-dark-green-900' : 'bg-gray-300' }}"></div>
                </div>
            @endforeach
        </div>
    </div>
</div>






