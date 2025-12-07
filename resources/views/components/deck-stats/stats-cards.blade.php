@props([
    'totalCards',
    'newCardsCount',
    'learningCount',
    'masteredCount',
])

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






