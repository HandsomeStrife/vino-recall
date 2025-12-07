<div class="min-h-screen bg-cream-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-burgundy-900 mb-2">{{ $deck->name }}</h1>
                @if($deck->description)
                    <p class="text-gray-600">{{ $deck->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button 
                    wire:click="confirmReset"
                    class="inline-flex items-center px-4 py-2 bg-white hover:bg-red-50 text-red-600 rounded-lg transition font-medium border border-red-200 hover:border-red-300"
                    title="Reset all progress for this deck"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset Progress
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-800 rounded-lg transition font-medium border border-gray-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Reset Confirmation Modal -->
        @if($showResetConfirmation)
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="cancelReset">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Reset Deck Progress</h3>
                    </div>
                    
                    <p class="text-gray-600 mb-2">Are you sure you want to reset all progress for <strong>{{ $deck->name }}</strong>?</p>
                    <p class="text-sm text-gray-500 mb-6">This will delete all your review history and SRS progress for this deck. All cards will return to "Uncorked" status. This action cannot be undone.</p>
                    
                    <div class="flex gap-3 justify-end">
                        <button 
                            wire:click="cancelReset"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition font-medium"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="resetDeckProgress"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium"
                        >
                            Reset Progress
                        </button>
                    </div>
                </div>
            </div>
        @endif

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

