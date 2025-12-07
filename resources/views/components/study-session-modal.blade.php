@props(['deckId' => null, 'deckName' => null, 'dueCount' => 0, 'newCount' => 0, 'totalCount' => 0, 'reviewedCount' => 0])

<div x-data="{
    showModal: false,
    sessionType: 'normal',
    cardLimit: null,
    statusFilters: [],
    randomOrder: false,
    showAdvanced: false,
    
    startSession() {
        window.location.href = '/study/' + this.sessionType + '/{{ $deckId }}';
    },
    
    toggleFilter(filter) {
        const index = this.statusFilters.indexOf(filter);
        if (index > -1) {
            this.statusFilters.splice(index, 1);
        } else {
            this.statusFilters.push(filter);
        }
    }
}">
    <!-- Trigger Button -->
    <button @click="showModal = true" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 active:bg-burgundy-700 transition-all duration-150 font-semibold text-sm shadow-sm hover:shadow-md active:scale-98 cursor-pointer']) }}>
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        Start Study Session
    </button>
    
    <!-- Modal -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="showModal = false">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900/40 transition-opacity"
             @click="showModal = false"></div>
        
        <!-- Modal panel -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-2xl max-w-2xl w-full p-6"
                 style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);"
                 @click.away="showModal = false">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-burgundy-900 mb-2" id="modal-title">
                        Choose Study Session Type
                    </h3>
                    @if($deckName)
                        <p class="text-gray-600">{{ $deckName }}</p>
                    @endif
                </div>
                
                <!-- Session Type Options -->
                <div class="space-y-4 mb-6">
                    <!-- Normal Review -->
                    <label class="block cursor-pointer">
                        <div class="border-2 rounded-lg p-4 transition"
                             :class="sessionType === 'normal' ? 'border-burgundy-500 bg-burgundy-50' : 'border-gray-300 hover:border-burgundy-300'">
                            <div class="flex items-start">
                                <input type="radio" 
                                       name="session_type" 
                                       value="normal" 
                                       x-model="sessionType"
                                       class="mt-1 text-burgundy-500 focus:ring-burgundy-500">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-burgundy-900">Normal Review</div>
                                        @if($reviewedCount === 0 && $newCount > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Recommended
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Daily review session: complete due cards and learn a few new ones. Balanced approach with ~10 cards per session.
                                    </div>
                                    @if($dueCount > 0 || $newCount > 0)
                                        <div class="text-sm text-burgundy-600 font-medium mt-2">
                                            @if($dueCount > 0 && $newCount > 0)
                                                {{ $dueCount }} due + {{ $newCount }} new
                                            @elseif($dueCount > 0)
                                                {{ $dueCount }} card{{ $dueCount !== 1 ? 's' : '' }} due
                                            @else
                                                {{ $newCount }} new card{{ $newCount !== 1 ? 's' : '' }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Deep Study -->
                    <label class="block cursor-pointer">
                        <div class="border-2 rounded-lg p-4 transition"
                             :class="sessionType === 'deep_study' ? 'border-burgundy-500 bg-burgundy-50' : 'border-gray-300 hover:border-burgundy-300'">
                            <div class="flex items-start">
                                <input type="radio" 
                                       name="session_type" 
                                       value="deep_study" 
                                       x-model="sessionType"
                                       class="mt-1 text-burgundy-500 focus:ring-burgundy-500">
                                <div class="ml-3 flex-1">
                                    <div class="font-semibold text-burgundy-900">Deep Study</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Study all available cards in one session (due + new). Great for intensive learning or completing a deck quickly. SRS tracking enabled.
                                    </div>
                                    <div class="text-sm text-burgundy-600 font-medium mt-2">
                                        {{ $totalCount }} card{{ $totalCount !== 1 ? 's' : '' }} available
                                        @if($dueCount > 0 && $newCount > 0)
                                            ({{ $dueCount }} due + {{ $newCount }} new)
                                        @elseif($dueCount > 0)
                                            ({{ $dueCount }} due)
                                        @elseif($newCount > 0)
                                            ({{ $newCount }} new)
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Practice Session -->
                    <label class="block cursor-pointer">
                        <div class="border-2 rounded-lg p-4 transition"
                             :class="sessionType === 'practice' ? 'border-burgundy-500 bg-burgundy-50' : 'border-gray-300 hover:border-burgundy-300'">
                            <div class="flex items-start">
                                <input type="radio" 
                                       name="session_type" 
                                       value="practice" 
                                       x-model="sessionType"
                                       class="mt-1 text-burgundy-500 focus:ring-burgundy-500">
                                <div class="ml-3 flex-1">
                                    <div class="font-semibold text-burgundy-900">Practice Session</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Custom review session without affecting SRS scheduling. Perfect for exam prep.
                                    </div>
                                    
                                    <!-- Practice Options (shown when selected) -->
                                    <div x-show="sessionType === 'practice'" 
                                         x-collapse
                                         class="mt-4 space-y-4 pt-4 border-t border-gray-200">
                                        <!-- Card Limit -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Cards</label>
                                            <select x-model.number="cardLimit" class="w-full border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                                                <option :value="null">All cards</option>
                                                <option :value="10">10 cards</option>
                                                <option :value="20">20 cards</option>
                                                <option :value="50">50 cards</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Status Filters -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           value="mistakes" 
                                                           @change="toggleFilter('mistakes')"
                                                           class="text-burgundy-500 focus:ring-burgundy-500 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">Mistakes only</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           value="mastered" 
                                                           @change="toggleFilter('mastered')"
                                                           class="text-burgundy-500 focus:ring-burgundy-500 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">Mastered cards</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           value="new" 
                                                           @change="toggleFilter('new')"
                                                           class="text-burgundy-500 focus:ring-burgundy-500 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">New cards</span>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Leave empty to include all cards</p>
                                        </div>
                                        
                                        <!-- Random Order -->
                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       x-model="randomOrder"
                                                       class="text-burgundy-500 focus:ring-burgundy-500 rounded">
                                                <span class="ml-2 text-sm font-medium text-gray-700">Randomize card order</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                        Cancel
                    </button>
                    <button @click="startSession()" 
                            class="px-6 py-2 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold">
                        Start Session
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

