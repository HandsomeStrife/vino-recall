@props([
    'newCardsCount',
    'dueCardsCount',
    'todayReviews',
    'streak',
])

<div class="bg-white p-6 rounded-xl shadow-md h-full">
    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Your Progress</h2>
    
    <div class="grid grid-cols-2">
        <!-- New Cards -->
        <div class="text-center py-4 border-r border-b border-gray-200">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-burgundy-100 mb-2">
                <svg class="w-6 h-6 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-burgundy-900">{{ $newCardsCount }}</p>
            <p class="text-xs text-gray-500 font-medium">New</p>
        </div>

        <!-- Cards Due -->
        <div class="text-center py-4 border-b border-gray-200">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-burgundy-100 mb-2">
                <svg class="w-6 h-6 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-burgundy-900">{{ $dueCardsCount }}</p>
            <p class="text-xs text-gray-500 font-medium">Due</p>
        </div>

        <!-- Reviews Today -->
        <div class="text-center py-4 border-r border-gray-200">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-burgundy-100 mb-2">
                <svg class="w-6 h-6 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-burgundy-900">{{ $todayReviews }}</p>
            <p class="text-xs text-gray-500 font-medium">Today</p>
        </div>

        <!-- Current Streak -->
        <div class="text-center py-4">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-burgundy-100 mb-2">
                <svg class="w-6 h-6 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-burgundy-900">{{ $streak }}</p>
            <p class="text-xs text-gray-500 font-medium">Day Streak</p>
        </div>
    </div>
</div>

