<div>
    <!-- Hero Section with Background -->
    <div class="relative bg-cover bg-center" style="background-image: linear-gradient(rgba(78, 35, 48, 0.85), rgba(78, 35, 48, 0.85)), url('{{ asset('img/defaults/5.jpg') }}');">
        <div class="max-w-6xl mx-auto px-4 py-12">
            <!-- Welcome Message -->
            <div class="text-center text-white mb-12">
                <h1 class="text-5xl font-bold mb-2">
                    Welcome back, {{ $userName }}!
                </h1>
                @if($streak > 0)
                    <p class="text-xl text-cream-200">You're on a {{ $streak }}-day streak!</p>
                @endif
            </div>

            <!-- Hero Deck Cards -->
            @if($hasEnrolledDecks && $heroDecks->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold text-white mb-4">Today's Focus</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($heroDecks as $deckStat)
                            @php
                                // Define deck colors (can be stored in deck table later)
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $deckColor = $deckStat['deck']->category ? $colors[crc32($deckStat['deck']->category) % count($colors)] : $colors[$deckStat['deck']->id % count($colors)];
                            @endphp
                            <x-deck-card 
                                :deck="$deckStat['deck']"
                                :deckColor="$deckColor"
                                :image="$deckStat['image']"
                                :dueCards="$deckStat['dueCards']"
                                :newCards="$deckStat['newCards']"
                                :retentionRate="$deckStat['retentionRate']"
                            />
                        @endforeach
                    </div>
                </div>
            @elseif(!$hasEnrolledDecks)
                <div class="bg-white rounded-xl shadow-2xl p-12 text-center max-w-2xl mx-auto">
                    <svg class="h-20 w-20 text-burgundy-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h2 class="text-3xl font-bold text-burgundy-900 mb-4">Your wine journey awaits!</h2>
                    <p class="text-gray-600 mb-8 text-lg">You haven't enrolled in any decks yet. Let's get you started on your path to becoming a wine expert.</p>
                    <a href="{{ route('library') }}" class="inline-flex items-center px-8 py-4 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold text-lg shadow-lg hover:shadow-xl">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Browse Library
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if($hasEnrolledDecks)
            <!-- Daily Goal and Mistakes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Daily Goal -->
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

                <!-- Mistakes -->
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
                                                {{ \Carbon\Carbon::parse($item['review']->created_at)->format('M d, g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($dueCardsCount > 0)
                            <a href="{{ route('study') }}" class="mt-4 block text-center bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition font-medium">
                                Review Mistakes
                            </a>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="h-16 w-16 text-dark-green-900 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">No recent mistakes</p>
                            <p class="text-sm text-gray-500 mt-1">Keep up the great work!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentActivityWithCards->isNotEmpty())
                <div class="bg-white p-8 rounded-xl shadow-md mb-8">
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
            @endif
        @endif

        <!-- Other Decks -->
        @if($availableDecks->isNotEmpty())
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
        @endif
    </div>
</div>
