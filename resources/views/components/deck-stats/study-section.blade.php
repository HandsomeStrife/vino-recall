@props([
    'dueCardsCount',
    'newCardsCount',
    'reviewedCount',
    'deckShortcode',
    'deckName',
])

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
                :deckId="$deckShortcode" 
                :deckName="$deckName"
                :dueCount="$dueCardsCount"
                :newCount="$newCardsCount"
                :reviewedCount="$reviewedCount"
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

