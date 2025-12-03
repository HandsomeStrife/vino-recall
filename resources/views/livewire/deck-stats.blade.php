<div class="min-h-screen bg-cream-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-burgundy-900 mb-2">{{ $deck->name }}</h1>
                @if($deck->description)
                    <p class="text-gray-600">{{ $deck->description }}</p>
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-800 rounded-lg transition font-medium border border-gray-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
            <!-- Left: Circular Progress Stats and Study Section (4 columns wide) -->
            <div class="lg:col-span-4 space-y-6">
                <x-deck-stats.circular-progress 
                    :progress="$progress"
                    :accuracyRate="$accuracyRate"
                    :masteryRate="$masteryRate"
                    :totalCards="$totalCards"
                    :newCardsCount="$newCardsCount"
                    :masteredCount="$masteredCount"
                />

                <x-deck-stats.study-section 
                    :dueCardsCount="$dueCardsCount"
                    :newCardsCount="$newCardsCount"
                    :reviewedCount="$reviewedCount"
                    :deckShortcode="$deck->shortcode"
                    :deckName="$deck->name"
                />
            </div>

            <!-- Right: Stats Cards Stack (1 column wide) -->
            <div class="lg:col-span-1">
                <x-deck-stats.stats-cards 
                    :totalCards="$totalCards"
                    :newCardsCount="$newCardsCount"
                    :learningCount="$learningCount"
                    :masteredCount="$masteredCount"
                />
            </div>
        </div>

        <x-deck-stats.recent-activity :recentActivity="$recentActivity" />
    </div>
</div>

