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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Left: Circular Progress Stats and Study Section (2 columns wide) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Circular Progress Stats Container -->
                <div class="bg-white p-10 rounded-2xl border border-gray-300 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <!-- Overall Progress -->
                        <div class="flex flex-col items-center">
                            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider text-center mb-6">Overall Progress</h3>
                            <div class="relative w-36 h-36 mb-4">
                                <svg class="transform -rotate-90 w-36 h-36">
                                    <circle cx="72" cy="72" r="60" stroke="#F5E6E8" stroke-width="16" fill="none" />
                                    <circle cx="72" cy="72" r="60" stroke="#B8817D" stroke-width="16" fill="none"
                                            stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - $progress / 100) }}"
                                            stroke-linecap="round" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-800">{{ $progress }}%</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 text-center">{{ $totalCards - $newCardsCount }} / {{ $totalCards }} cards reviewed</p>
                        </div>

                        <!-- Accuracy Rate -->
                        <div class="flex flex-col items-center">
                            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider text-center mb-6">Accuracy Rate</h3>
                            <div class="relative w-36 h-36 mb-4">
                                <svg class="transform -rotate-90 w-36 h-36">
                                    <circle cx="72" cy="72" r="60" stroke="#E5F2F0" stroke-width="16" fill="none" />
                                    <circle cx="72" cy="72" r="60" stroke="#7FB5AC" stroke-width="16" fill="none"
                                            stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - $accuracyRate / 100) }}"
                                            stroke-linecap="round" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-800">{{ $accuracyRate }}%</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 text-center">Correct answers</p>
                        </div>

                        <!-- Mastery Rate -->
                        <div class="flex flex-col items-center">
                            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider text-center mb-6">Mastery Rate</h3>
                            <div class="relative w-36 h-36 mb-4">
                                <svg class="transform -rotate-90 w-36 h-36">
                                    <circle cx="72" cy="72" r="60" stroke="#F5F1E5" stroke-width="16" fill="none" />
                                    <circle cx="72" cy="72" r="60" stroke="#C9B382" stroke-width="16" fill="none"
                                            stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                                            stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - ($totalCards > 0 ? ($masteredCount / $totalCards) * 100 : 0) / 100) }}"
                                            stroke-linecap="round" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-800">{{ $totalCards > 0 ? number_format(($masteredCount / $totalCards) * 100, 1) : 0 }}%</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 text-center">{{ $masteredCount }} / {{ $totalCards }} cards mastered</p>
                        </div>
                    </div>
                </div>

                <!-- Study Section Container -->
                @if($dueCardsCount > 0 || $newCardsCount > 0)
                    <div class="bg-white p-6 rounded-2xl border border-gray-300 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-green-900 mb-2">Ready to Study?</h3>
                                <p class="text-green-700">
                                    @if($dueCardsCount > 0)
                                        {{ $dueCardsCount }} card{{ $dueCardsCount !== 1 ? 's' : '' }} due for review
                                    @endif
                                    @if($dueCardsCount > 0 && $newCardsCount > 0)
                                        and 
                                    @endif
                                    @if($newCardsCount > 0)
                                        {{ $newCardsCount }} new card{{ $newCardsCount !== 1 ? 's' : '' }} available
                                    @endif
                                </p>
                            </div>
                            <x-study-session-modal 
                                :deckId="$deck->shortcode" 
                                :deckName="$deck->name"
                                :dueCount="$dueCardsCount"
                                :newCount="$newCardsCount"
                                :totalCount="$dueCardsCount + $newCardsCount" />
                        </div>
                    </div>
                @else
                    <div class="bg-white p-8 rounded-2xl border border-gray-300 shadow-sm text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-green-900 mb-2">Palate Cleansed:</h3>
                        <p class="text-green-700">No cards due for review</p>
                    </div>
                @endif
            </div>

            <!-- Right: Stats Cards Stack -->
            <div class="space-y-4">
                <!-- New Cards -->
                <div class="bg-white p-4 rounded-xl border border-gray-300 shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">New Cards</h3>
                        <p class="text-4xl font-bold text-burgundy-900">{{ $newCardsCount }}</p>
                    </div>
                    <div class="w-16 h-16 bg-rose-100 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>

                <!-- Total Cards -->
                <div class="bg-white p-4 rounded-xl border border-gray-300 shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Total Cards</h3>
                        <p class="text-4xl font-bold text-burgundy-900">{{ $totalCards }}</p>
                    </div>
                    <div class="w-16 h-16 bg-rose-100 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Learning -->
                <div class="bg-white p-4 rounded-xl border border-gray-300 shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Learning</h3>
                        <p class="text-4xl font-bold text-burgundy-900">{{ $learningCount }}</p>
                    </div>
                    <div class="w-16 h-16 bg-rose-100 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Mastered -->
                <div class="bg-white p-4 rounded-xl border border-gray-300 shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Mastered</h3>
                        <p class="text-4xl font-bold text-burgundy-900">{{ $masteredCount }}</p>
                    </div>
                    <div class="w-16 h-16 bg-rose-100 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($recentActivity->isNotEmpty())
            <div class="bg-white p-8 rounded-2xl border border-gray-300 shadow-sm">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Recent Activity</h2>
                <div class="space-y-2">
                    @foreach($recentActivity as $item)
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 mb-1">
                                    @if($item['review']->created_at)
                                        {{ \Carbon\Carbon::parse($item['review']->created_at)->diffForHumans() }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                                @if($item['card'])
                                    <p class="text-gray-900 font-medium">{{ \Illuminate\Support\Str::limit($item['card']->question, 60) }}</p>
                                @endif
                            </div>
                            @if($item['review']->is_correct)
                                <span class="text-green-600 text-xl font-bold ml-4">✓</span>
                            @else
                                <span class="text-red-500 text-xl font-bold ml-4">✗</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

