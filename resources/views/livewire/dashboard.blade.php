<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Cards Mastered</h3>
                <p class="text-3xl font-bold text-burgundy-500">{{ $masteredCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Current Streak</h3>
                <p class="text-3xl font-bold text-burgundy-500">{{ $streak }} {{ $streak === 1 ? 'day' : 'days' }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Time Spent</h3>
                <p class="text-3xl font-bold text-burgundy-500">
                    @if($timeSpent['hours'] > 0)
                        {{ $timeSpent['hours'] }}h {{ $timeSpent['minutes'] }}m
                    @else
                        {{ $timeSpent['minutes'] }}m
                    @endif
                </p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Due Today</h2>
                @if($dueCardsCount > 0)
                    <p class="text-gray-600 mb-4">{{ $dueCardsCount }} {{ $dueCardsCount === 1 ? 'card' : 'cards' }} due for review.</p>
                    <a href="{{ route('study') }}" class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">
                        Start Review
                    </a>
                @else
                    <p class="text-gray-600 mb-4">No cards due for review. Great job!</p>
                    <a href="{{ route('study') }}" class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">
                        Study New Cards
                    </a>
                @endif
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Daily Goal</h2>
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Review {{ $dailyGoal }} Cards</span>
                        <span>{{ $todayReviews }} / {{ $dailyGoal }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-burgundy-500 h-4 rounded-full transition-all flex items-center justify-end pr-2" style="width: {{ $dailyGoalProgress }}%">
                            @if($dailyGoalProgress > 15)
                                <span class="text-white text-xs font-semibold">{{ $dailyGoalProgress }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($todayReviews >= $dailyGoal)
                    <p class="text-green-600 font-semibold">Daily goal achieved! 🎉</p>
                @else
                    <p class="text-gray-600">{{ $dailyGoal - $todayReviews }} more {{ ($dailyGoal - $todayReviews) === 1 ? 'card' : 'cards' }} to reach your goal.</p>
                @endif
            </div>
        </div>

        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Mastery Progress</h2>
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Cards Mastered</span>
                    <span>{{ $masteredCount }} / {{ $totalCards }} ({{ $masteryPercentage }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-6 relative overflow-hidden">
                    <div class="bg-gradient-to-r from-burgundy-500 to-burgundy-600 h-6 rounded-full transition-all flex items-center justify-center" style="width: {{ $masteryPercentage }}%">
                        @if($masteryPercentage > 10)
                            <span class="text-white text-sm font-semibold">{{ $masteryPercentage }}%</span>
                        @endif
                    </div>
                </div>
            </div>
            <p class="text-gray-600 text-sm">
                @if($masteryPercentage >= 100)
                    Congratulations! You've mastered all available cards! 🏆
                @elseif($masteryPercentage >= 75)
                    Excellent progress! You're almost there!
                @elseif($masteryPercentage >= 50)
                    Great work! You're halfway to mastery!
                @elseif($masteryPercentage >= 25)
                    Keep going! You're making steady progress.
                @else
                    Every journey begins with a single step. Keep studying!
                @endif
            </p>
        </div>
        @if($recentActivityWithCards->isNotEmpty())
            <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Recent Activity</h2>
                <div class="space-y-2">
                    @foreach($recentActivityWithCards as $item)
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                            <div>
                                <p class="text-sm text-gray-600">
                                    @if($item['activity']->created_at)
                                        {{ \Carbon\Carbon::parse($item['activity']->created_at)->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                                @if($item['card'])
                                    <p class="text-gray-900">{{ \Illuminate\Support\Str::limit($item['card']->question, 50) }}</p>
                                @endif
                            </div>
                            <x-badge.badge :variant="$item['activity']->rating === 'easy' ? 'success' : ($item['activity']->rating === 'good' ? 'primary' : ($item['activity']->rating === 'hard' ? 'warning' : 'danger'))">
                                {{ ucfirst($item['activity']->rating) }}
                            </x-badge.badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

