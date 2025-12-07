<div class="p-8" 
     x-data="{ 
         questionLength: 0,
         toastMessage: '',
         showToast: false,
        resetFormFields() {
            this.questionLength = 0;
            const form = this.$el;
            form.querySelectorAll('textarea, input[type=text]').forEach(el => { 
                if (el.closest('.add-card-form')) {
                    el.value = ''; 
                }
            });
            form.querySelectorAll('input[type=checkbox]').forEach(el => { 
                if (el.closest('.add-card-form')) {
                    el.checked = false; 
                }
            });
        }
     }" 
     @keydown.ctrl.enter.window="$wire.createCard()"
     @card-saved.window="toastMessage = $event.detail.message; showToast = true; setTimeout(() => showToast = false, 3000); resetFormFields()"
    
    <!-- Toast Notification -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8"
         class="fixed top-6 right-6 z-50"
         x-cloak>
        <div class="bg-green-600 text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium" x-text="toastMessage"></span>
            <button @click="showToast = false" class="ml-2 hover:bg-green-700 rounded p-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Header with breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('admin.decks') }}" class="inline-flex items-center text-gray-300 hover:text-white mb-2 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Decks
        </a>
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-white">{{ $deck?->name ?? 'Deck' }}</h1>
            <div class="flex items-center gap-2">
                <button wire:click="exportCards"
                        class="inline-flex items-center px-4 py-2 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium border border-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </button>
                <button wire:click="openImportModal"
                        class="inline-flex items-center px-4 py-2 bg-burgundy-700 text-white rounded-lg hover:bg-burgundy-600 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import CSV
                </button>
            </div>
        </div>
        @if($deck?->description)
            <p class="text-gray-400 mt-1">{{ $deck->description }}</p>
        @endif
        <div class="flex items-center gap-4 mt-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-200 border border-gray-600">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                {{ $cards->count() }} {{ $cards->count() === 1 ? 'Card' : 'Cards' }}
            </span>
        </div>
    </div>

    <!-- Add Card Section -->
    <div class="mb-6">
        <!-- Collapsed Header / Toggle Button -->
        <button wire:click="toggleAddForm" class="w-full group">
            <div class="flex items-center justify-between p-4 transition-all duration-200
                        {{ $showAddForm 
                            ? 'bg-gradient-to-r from-burgundy-900 to-burgundy-800 border border-burgundy-700 border-b-0 rounded-t-xl' 
                            : 'bg-gradient-to-r from-gray-800 to-gray-750 border border-gray-700 rounded-xl hover:from-burgundy-900/50 hover:to-burgundy-800/50 hover:border-burgundy-700/50' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-all duration-200 {{ $showAddForm ? 'bg-burgundy-700' : 'bg-gray-700 group-hover:bg-burgundy-800' }}">
                        <svg class="w-5 h-5 transition-transform duration-200 {{ $showAddForm ? 'text-white rotate-45' : 'text-gray-400 group-hover:text-burgundy-300' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h2 class="text-lg font-semibold {{ $showAddForm ? 'text-white' : 'text-gray-300 group-hover:text-white' }}">
                            {{ $showAddForm ? 'Creating New Card' : 'Add New Card' }}
                        </h2>
                        <p class="text-sm {{ $showAddForm ? 'text-gray-300' : 'text-gray-500 group-hover:text-gray-400' }}">
                            {{ $showAddForm ? 'Press Ctrl+Enter to save quickly' : 'Click to expand the card creation form' }}
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 transition-all duration-300 {{ $showAddForm ? 'text-burgundy-300 rotate-180' : 'text-gray-500 group-hover:text-burgundy-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        <!-- Expandable Form -->
        @if($showAddForm)
        <div>
            <div class="bg-gradient-to-b from-gray-800 to-gray-850 rounded-b-xl border border-gray-700 border-t-0 overflow-hidden">
                <form wire:submit.prevent="createCard" class="p-6 add-card-form">
                    <!-- Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Question & Image -->
                        <div class="space-y-5">
                            <!-- Question Field -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-sm font-semibold text-gray-200">
                                        Question <span class="text-red-400">*</span>
                                    </label>
                                    <span class="text-xs text-gray-500" x-text="questionLength + ' characters'"></span>
                                </div>
                                <textarea wire:model="form.question" 
                                          rows="4"
                                          x-on:input="questionLength = $event.target.value.length"
                                          class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-500 transition-all duration-200 resize-none"
                                          placeholder="What would you like to ask?"></textarea>
                                @error('form.question') <p class="mt-1.5 text-sm text-red-400 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>{{ $message }}</p> @enderror
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <label class="text-sm font-semibold text-gray-200 mb-2 block">Card Image (Optional)</label>
                                <div class="relative">
                                    @if($form->image)
                                        <div class="relative rounded-xl overflow-hidden border-2 border-burgundy-500 bg-gray-900">
                                            <img src="{{ $form->image->temporaryUrl() }}" alt="Preview" class="w-full h-40 object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                            <button type="button" wire:click="$set('form.image', null)" 
                                                    class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-500 text-white rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                            <span class="absolute bottom-2 left-2 text-xs text-white/80 bg-black/40 px-2 py-1 rounded">New image ready</span>
                                        </div>
                                    @else
                                        <label class="flex flex-col items-center justify-center h-40 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-900/50 hover:border-burgundy-500/50 transition-all duration-200 group">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-10 h-10 mb-3 text-gray-500 group-hover:text-burgundy-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-400 group-hover:text-gray-300">
                                                    <span class="font-semibold text-burgundy-400">Click to upload</span> or drag and drop
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 5MB</p>
                                            </div>
                                            <input type="file" wire:model="form.image" accept="image/*" class="hidden">
                                        </label>
                                    @endif
                                </div>
                                @error('form.image') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Right Column: Answer Choices -->
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-sm font-semibold text-gray-200">
                                    Answer Choices <span class="text-red-400">*</span>
                                </label>
                                <div class="flex items-center gap-3" wire:key="multi-select-add-{{ $form->is_multi_select ? '1' : '0' }}">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model.live="form.is_multi_select" 
                                               value="1"
                                               {{ $form->is_multi_select ? 'checked' : '' }}
                                               class="rounded border-gray-600 text-burgundy-600 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500 bg-gray-700 mr-2">
                                        <span class="text-xs text-gray-300">Multi-select</span>
                                    </label>
                                    @if($form->is_multi_select || count($form->correct_answer_indices) > 1)
                                        <span class="text-xs bg-burgundy-700 text-white px-2 py-1 rounded-full">
                                            Select all that apply
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Click the circle icon to mark answers as correct. Enable multi-select for "select all that apply" questions.</p>
                            
                            <!-- Answer Choice Cards -->
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($form->answer_choices as $index => $choice)
                                    <div class="relative group">
                                        <div class="relative rounded-xl border-2 transition-all duration-200 overflow-hidden
                                                    {{ in_array($index, $form->correct_answer_indices) 
                                                       ? 'border-green-500 bg-gradient-to-r from-green-900/40 to-green-800/20 shadow-lg shadow-green-900/20' 
                                                       : 'border-gray-600 bg-gray-900/30 hover:border-gray-500 hover:bg-gray-900/50' }}">
                                            <div class="flex items-center p-3">
                                                <!-- Letter Badge -->
                                                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center font-bold text-lg transition-all duration-200
                                                            {{ in_array($index, $form->correct_answer_indices) 
                                                               ? 'bg-green-500 text-white' 
                                                               : 'bg-gray-700 text-gray-400 group-hover:bg-gray-600' }}">
                                                    {{ chr(65 + $index) }}
                                                </div>
                                                
                                                <!-- Input Field -->
                                                <div class="flex-1 ml-3">
                                                    <input type="text" 
                                                           wire:model.live="form.answer_choices.{{ $index }}" 
                                                           class="w-full bg-transparent border-0 text-white placeholder-gray-500 focus:ring-0 focus:outline-none p-0 text-sm"
                                                           placeholder="Enter answer choice {{ chr(65 + $index) }}...">
                                                </div>

                                                <!-- Correct Indicator (Clickable) -->
                                                <button type="button" 
                                                        wire:click="toggleCorrectAnswer({{ $index }})"
                                                        tabindex="-1"
                                                        class="flex-shrink-0 ml-2 cursor-pointer hover:scale-110 transition-transform">
                                                    @if(in_array($index, $form->correct_answer_indices))
                                                        <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center animate-scale-in">
                                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-8 h-8 rounded-full border-2 border-gray-600 hover:border-green-500 hover:bg-green-500/10 transition-colors"></div>
                                                    @endif
                                                </button>
                                            </div>

                                            <!-- Correct Answer Glow Effect -->
                                            @if(in_array($index, $form->correct_answer_indices))
                                                <div class="absolute inset-0 bg-green-400/5 pointer-events-none"></div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('form.answer_choices') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            @error('form.correct_answer_indices') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Hidden Answer field -->
                    <input type="hidden" wire:model="form.answer">

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-700">
                        <p class="text-xs text-gray-500 hidden sm:block">
                            <kbd class="px-2 py-1 bg-gray-700 rounded text-gray-400 font-mono text-xs">Ctrl</kbd> + 
                            <kbd class="px-2 py-1 bg-gray-700 rounded text-gray-400 font-mono text-xs">Enter</kbd> 
                            to save
                        </p>
                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="toggleAddForm"
                                    class="px-4 py-2.5 text-gray-400 hover:text-white transition-colors text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-75 cursor-wait"
                                    class="px-6 py-2.5 bg-gradient-to-r from-burgundy-600 to-burgundy-500 text-white rounded-lg hover:from-burgundy-500 hover:to-burgundy-400 transition-all duration-200 font-semibold shadow-lg shadow-burgundy-900/30 flex items-center gap-2">
                                <span wire:loading.remove wire:target="createCard">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </span>
                                <span wire:loading wire:target="createCard">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="createCard">Add Card</span>
                                <span wire:loading wire:target="createCard">Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Cards List -->
    <div class="bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-700">
        <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">All Cards</h3>
        </div>
        <div class="divide-y divide-gray-700">
            @forelse($cards as $card)
                <div class="p-4 hover:bg-gray-750/50 transition-colors group">
                    <div class="flex items-start gap-4">
                        <!-- Card Image/Placeholder -->
                        <div class="flex-shrink-0">
                            @if($card->image_path)
                                <img src="{{ asset('storage/' . $card->image_path) }}" alt="Card image" class="w-16 h-16 object-cover rounded-lg border border-gray-600">
                            @else
                                <div class="w-16 h-16 bg-gray-700/50 rounded-lg flex items-center justify-center border border-gray-600">
                                    <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Card Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-white font-medium line-clamp-2">{{ $card->question }}</p>
                                <span class="flex-shrink-0 px-1.5 py-0.5 bg-gray-700/70 text-gray-400 text-xs font-mono rounded border border-gray-600" title="Card ID: {{ $card->shortcode }}">
                                    {{ $card->shortcode }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($card->answer_choices)
                                    @foreach($card->answer_choices as $idx => $answerChoice)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                     {{ in_array($idx, $card->correct_answer_indices ?? []) 
                                                        ? 'bg-green-900/50 text-green-300 border border-green-700' 
                                                        : 'bg-gray-700 text-gray-400' }}">
                                            {{ chr(65 + $idx) }}: {{ \Illuminate\Support\Str::limit($answerChoice, 20) }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="openEditModal({{ $card->id }})" 
                                    class="p-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteCard({{ $card->id }})" 
                                    wire:confirm="Are you sure you want to delete this card?"
                                    class="p-2 bg-red-900/30 text-red-400 rounded-lg hover:bg-red-900/50 hover:text-red-300 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-700/50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-300 mb-1">No cards yet</h3>
                    <p class="text-gray-500 text-sm">Add your first card to this deck using the form above.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
             x-data="{ editQuestionLength: {{ strlen($form->question) }} }">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="closeEditModal"></div>
                
                <!-- Modal panel -->
                <div class="relative bg-gradient-to-b from-gray-800 to-gray-850 rounded-2xl shadow-2xl max-w-3xl w-full border border-gray-700 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gray-900/50">
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Card</h3>
                            <p class="text-sm text-gray-400 mt-0.5">Update the card content and answer choices</p>
                        </div>
                        <button wire:click="closeEditModal" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form wire:submit.prevent="updateCard" class="p-6">
                            <!-- Two Column Layout -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Left Column: Question & Image -->
                                <div class="space-y-5">
                                    <!-- Question Field -->
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="text-sm font-semibold text-gray-200">
                                                Question <span class="text-red-400">*</span>
                                            </label>
                                            <span class="text-xs text-gray-500" x-text="editQuestionLength + ' characters'"></span>
                                        </div>
                                        <textarea wire:model="form.question" 
                                                  rows="4"
                                                  x-on:input="editQuestionLength = $event.target.value.length"
                                                  class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-500 transition-all duration-200 resize-none"
                                                  placeholder="What would you like to ask?"></textarea>
                                        @error('form.question') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Image Upload -->
                                    <div>
                                        <label class="text-sm font-semibold text-gray-200 mb-2 block">Card Image</label>
                                        <div class="relative">
                                            @if($form->image)
                                                <div class="relative rounded-xl overflow-hidden border-2 border-burgundy-500 bg-gray-900">
                                                    <img src="{{ $form->image->temporaryUrl() }}" alt="Preview" class="w-full h-40 object-cover">
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                                    <button type="button" wire:click="$set('form.image', null)" 
                                                            class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-500 text-white rounded-lg transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                    <span class="absolute bottom-2 left-2 text-xs text-white/80 bg-black/40 px-2 py-1 rounded">New image ready</span>
                                                </div>
                                            @elseif($form->existingImagePath)
                                                <div class="relative rounded-xl overflow-hidden border border-gray-600 bg-gray-900">
                                                    <img src="{{ asset('storage/' . $form->existingImagePath) }}" alt="Current" class="w-full h-40 object-cover">
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                                    <span class="absolute bottom-2 left-2 text-xs text-white/80 bg-black/40 px-2 py-1 rounded">Current image</span>
                                                    <label class="absolute bottom-2 right-2 px-3 py-1.5 bg-burgundy-600 hover:bg-burgundy-500 text-white text-xs font-medium rounded-lg cursor-pointer transition">
                                                        Change
                                                        <input type="file" wire:model="form.image" accept="image/*" class="hidden">
                                                    </label>
                                                </div>
                                            @else
                                                <label class="flex flex-col items-center justify-center h-40 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-900/50 hover:border-burgundy-500/50 transition-all duration-200 group">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <svg class="w-10 h-10 mb-3 text-gray-500 group-hover:text-burgundy-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <p class="text-sm text-gray-400">
                                                            <span class="font-semibold text-burgundy-400">Click to upload</span>
                                                        </p>
                                                    </div>
                                                    <input type="file" wire:model="form.image" accept="image/*" class="hidden">
                                                </label>
                                            @endif
                                        </div>
                                        @error('form.image') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Right Column: Answer Choices -->
                                <div>
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="text-sm font-semibold text-gray-200">
                                            Answer Choices <span class="text-red-400">*</span>
                                        </label>
                                        <div class="flex items-center gap-3" wire:key="multi-select-edit-{{ $form->is_multi_select ? '1' : '0' }}">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       wire:model.live="form.is_multi_select" 
                                                       value="1"
                                                       {{ $form->is_multi_select ? 'checked' : '' }}
                                                       class="rounded border-gray-600 text-burgundy-600 shadow-sm focus:border-burgundy-500 focus:ring-burgundy-500 bg-gray-700 mr-2">
                                                <span class="text-xs text-gray-300">Multi-select</span>
                                            </label>
                                            @if($form->is_multi_select || count($form->correct_answer_indices) > 1)
                                                <span class="text-xs bg-burgundy-700 text-white px-2 py-1 rounded-full">
                                                    Select all that apply
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Answer Choice Cards -->
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($form->answer_choices as $index => $choice)
                                            <div class="relative group">
                                                <div class="relative rounded-xl border-2 transition-all duration-200 overflow-hidden
                                                            {{ in_array($index, $form->correct_answer_indices) 
                                                               ? 'border-green-500 bg-gradient-to-r from-green-900/40 to-green-800/20 shadow-lg shadow-green-900/20' 
                                                               : 'border-gray-600 bg-gray-900/30 hover:border-gray-500 hover:bg-gray-900/50' }}">
                                                    <div class="flex items-center p-3">
                                                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center font-bold text-lg transition-all duration-200
                                                                    {{ in_array($index, $form->correct_answer_indices) 
                                                                       ? 'bg-green-500 text-white' 
                                                                       : 'bg-gray-700 text-gray-400 group-hover:bg-gray-600' }}">
                                                            {{ chr(65 + $index) }}
                                                        </div>
                                                        <div class="flex-1 ml-3">
                                                            <input type="text" 
                                                                   wire:model.live="form.answer_choices.{{ $index }}" 
                                                                   class="w-full bg-transparent border-0 text-white placeholder-gray-500 focus:ring-0 focus:outline-none p-0 text-sm"
                                                                   placeholder="Enter answer choice {{ chr(65 + $index) }}...">
                                                        </div>
                                                        <button type="button" 
                                                                wire:click="toggleCorrectAnswer({{ $index }})"
                                                                tabindex="-1"
                                                                class="flex-shrink-0 ml-2 cursor-pointer hover:scale-110 transition-transform">
                                                            @if(in_array($index, $form->correct_answer_indices))
                                                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                </div>
                                                            @else
                                                                <div class="w-8 h-8 rounded-full border-2 border-gray-600 hover:border-green-500 hover:bg-green-500/10 transition-colors"></div>
                                                            @endif
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('form.answer_choices') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Hidden Answer field -->
                            <input type="hidden" wire:model="form.answer">

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-700">
                                <button type="button" wire:click="closeEditModal"
                                        class="px-5 py-2.5 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 hover:text-white transition font-medium">
                                    Cancel
                                </button>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-wait"
                                        class="px-6 py-2.5 bg-gradient-to-r from-burgundy-600 to-burgundy-500 text-white rounded-lg hover:from-burgundy-500 hover:to-burgundy-400 transition-all duration-200 font-semibold shadow-lg shadow-burgundy-900/30 flex items-center gap-2">
                                    <span wire:loading wire:target="updateCard">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="updateCard">Save Changes</span>
                                    <span wire:loading wire:target="updateCard">Saving...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="import-modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="closeImportModal"></div>
                
                <!-- Modal panel -->
                <div class="relative bg-gradient-to-b from-gray-800 to-gray-850 rounded-2xl shadow-2xl max-w-lg w-full border border-gray-700">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gray-900/50">
                        <div>
                            <h3 class="text-xl font-bold text-white" id="import-modal-title">Import Cards</h3>
                            <p class="text-sm text-gray-400 mt-0.5">Upload a CSV file to import cards</p>
                        </div>
                        <button wire:click="closeImportModal" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="importCards" class="p-6">
                        <!-- CSV Format Info -->
                        <div class="mb-5 p-4 bg-gray-900/50 rounded-xl border border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-200 mb-2">CSV Format</h4>
                            <p class="text-xs text-gray-400 mb-2">
                                Your CSV should have the following columns:
                            </p>
                            <code class="block text-xs bg-gray-900 text-gray-300 p-2 rounded overflow-x-auto">
                                question,is_multiple_choice,number_of_correct_answers,answer1,answer2,...
                            </code>
                            <p class="text-xs text-gray-500 mt-2">
                                Correct answers should be listed first. For example, if number_of_correct_answers is 2, then answer1 and answer2 are correct.
                            </p>
                            <a href="{{ asset('templates/card-export-template.csv') }}" 
                               download
                               class="inline-flex items-center mt-3 text-xs text-burgundy-400 hover:text-burgundy-300 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download template
                            </a>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-5">
                            <label class="text-sm font-semibold text-gray-200 mb-2 block">CSV File</label>
                            <div class="relative">
                                @if($importFile)
                                    <div class="flex items-center justify-between p-4 bg-green-900/20 border border-green-700 rounded-xl">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-white">{{ $importFile->getClientOriginalName() }}</p>
                                                <p class="text-xs text-gray-400">{{ number_format($importFile->getSize() / 1024, 1) }} KB</p>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('importFile', null)" 
                                                class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-900/50 hover:border-burgundy-500/50 transition-all duration-200 group">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-10 h-10 mb-2 text-gray-500 group-hover:text-burgundy-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <p class="text-sm text-gray-400 group-hover:text-gray-300">
                                                <span class="font-semibold text-burgundy-400">Click to upload</span> or drag and drop
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">CSV files only (max 1MB)</p>
                                        </div>
                                        <input type="file" wire:model="importFile" accept=".csv,.txt" class="hidden">
                                    </label>
                                @endif
                            </div>
                            @error('importFile') 
                                <p class="mt-2 text-sm text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                            <button type="button" wire:click="closeImportModal"
                                    class="px-5 py-2.5 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 hover:text-white transition font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-75 cursor-wait"
                                    @if(!$importFile) disabled @endif
                                    class="px-6 py-2.5 bg-gradient-to-r from-burgundy-600 to-burgundy-500 text-white rounded-lg hover:from-burgundy-500 hover:to-burgundy-400 transition-all duration-200 font-semibold shadow-lg shadow-burgundy-900/30 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading wire:target="importCards">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="importCards">Import Cards</span>
                                <span wire:loading wire:target="importCards">Importing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        
        @keyframes scale-in {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>
