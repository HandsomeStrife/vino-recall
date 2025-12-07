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

            <!-- Hero Deck/Collection Cards -->
            @if($hasEnrolledDecks && $heroItems->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold text-white mb-4">Today's Focus</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($heroItems as $item)
                            @php
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $firstCategory = ($item['deck']->categories && $item['deck']->categories->isNotEmpty()) 
                                    ? $item['deck']->categories->first()->name 
                                    : null;
                                $deckColor = $firstCategory ? $colors[crc32($firstCategory) % count($colors)] : $colors[$item['deck']->id % count($colors)];
                            @endphp
                            
                            @if($item['type'] === 'collection')
                                <x-collection-card 
                                    :collection="$item['deck']"
                                    :collectionColor="$deckColor"
                                    :image="$item['image']"
                                    :dueCards="$item['dueCards']"
                                    :newCards="$item['newCards']"
                                    :totalCards="$item['totalCards']"
                                    :childCount="$item['childCount']"
                                    :progress="$item['progress']"
                                />
                            @else
                                <x-deck-card 
                                    :deck="$item['deck']"
                                    :deckColor="$deckColor"
                                    :image="$item['image']"
                                    :dueCards="$item['dueCards']"
                                    :newCards="$item['newCards']"
                                    :reviewedCount="$item['reviewedCount']"
                                    :retentionRate="$item['retentionRate']"
                                    :nextReviewTime="$item['nextReviewTime'] ?? null"
                                />
                            @endif
                        @endforeach
                    </div>

                    @if($otherItems->isNotEmpty())
                        <div class="text-center mt-6">
                            <a href="{{ route('library') }}" class="inline-flex items-center px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-lg transition backdrop-blur-sm border border-white/20">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                View All Enrolled Decks ({{ $otherItems->count() + $heroItems->count() }})
                            </a>
                        </div>
                    @endif
                </div>
            @elseif(!$hasEnrolledDecks)
                <div class="bg-white rounded-xl shadow-2xl p-12 text-center max-w-2xl mx-auto">
                    <img src="{{ asset('img/learn.png') }}" alt="No enrolled decks" class="w-38 h-38 mx-auto mb-4">
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
            <!-- Full Width Layout (no sidebar) -->
            <div class="space-y-6 mb-8">
                <!-- Daily Goal, Stats Overview, and Mistakes (3 columns on desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-dashboard.daily-goal 
                        :todayReviews="$todayReviews"
                        :dailyGoal="$dailyGoal"
                        :dailyGoalProgress="$dailyGoalProgress"
                        :weekActivity="$weekActivity"
                    />

                    <x-dashboard.stats-overview 
                        :newCardsCount="$newCardsCount"
                        :dueCardsCount="$dueCardsCount"
                        :todayReviews="$todayReviews"
                        :streak="$streak"
                    />

                    <x-dashboard.recent-mistakes 
                        :mistakesWithCards="$mistakesWithCards"
                        :dueCardsCount="$dueCardsCount"
                    />
                </div>

                <!-- Recent Activity -->
                @if($recentActivityWithCards->isNotEmpty())
                    <x-dashboard.recent-activity :recentActivityWithCards="$recentActivityWithCards" />
                @endif
            </div>
        @endif

        <!-- Available Decks -->
        @if($availableDecks->isNotEmpty())
            <x-dashboard.available-decks :availableDecks="$availableDecks" />
        @endif
    </div>
</div>
