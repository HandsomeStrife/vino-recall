@props([
    'progress',
    'accuracyRate',
    'masteryRate',
    'totalCards',
    'newCardsCount',
    'masteredCount',
])

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
                            stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - $masteryRate / 100) }}"
                            stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-4xl font-bold text-gray-800">{{ number_format($masteryRate, 1) }}%</span>
                </div>
            </div>
            <p class="text-sm text-gray-500 text-center">{{ $masteredCount }} / {{ $totalCards }} cards mastered</p>
        </div>
    </div>
</div>

