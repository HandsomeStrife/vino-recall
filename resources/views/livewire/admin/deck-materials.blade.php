<div class="p-8"
     x-data="{ 
         toastMessage: '',
         showToast: false,
     }" 
     @material-saved.window="toastMessage = $event.detail.message; showToast = true; setTimeout(() => showToast = false, 3000)">
    
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
            <h1 class="text-3xl font-bold text-white">{{ $deck?->name ?? 'Deck' }} - Learning Materials</h1>
        </div>
        @if($deck?->description)
            <p class="text-gray-400 mt-1">{{ $deck->description }}</p>
        @endif
        <div class="flex items-center gap-4 mt-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-200 border border-gray-600">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                {{ $materials->count() }} {{ $materials->count() === 1 ? 'Material' : 'Materials' }}
            </span>
        </div>
    </div>

    <!-- Add Material Section -->
    <div class="mb-6">
        <button wire:click="toggleAddForm" class="w-full group">
            <div class="flex items-center justify-between p-4 transition-all duration-200
                        {{ $show_add_form 
                            ? 'bg-gradient-to-r from-burgundy-900 to-burgundy-800 border border-burgundy-700 border-b-0 rounded-t-xl' 
                            : 'bg-gradient-to-r from-gray-800 to-gray-750 border border-gray-700 rounded-xl hover:from-burgundy-900/50 hover:to-burgundy-800/50 hover:border-burgundy-700/50' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-all duration-200 {{ $show_add_form ? 'bg-burgundy-700' : 'bg-gray-700 group-hover:bg-burgundy-800' }}">
                        <svg class="w-5 h-5 transition-transform duration-200 {{ $show_add_form ? 'text-white rotate-45' : 'text-gray-400 group-hover:text-burgundy-300' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h2 class="text-lg font-semibold {{ $show_add_form ? 'text-white' : 'text-gray-300 group-hover:text-white' }}">
                            {{ $show_add_form ? 'Creating New Material' : 'Add New Material' }}
                        </h2>
                        <p class="text-sm {{ $show_add_form ? 'text-gray-300' : 'text-gray-500 group-hover:text-gray-400' }}">
                            {{ $show_add_form ? 'Fill in the content below' : 'Click to expand the material creation form' }}
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 transition-all duration-300 {{ $show_add_form ? 'text-burgundy-300 rotate-180' : 'text-gray-500 group-hover:text-burgundy-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        @if($show_add_form)
        <div>
            <div class="bg-gradient-to-b from-gray-800 to-gray-850 rounded-b-xl border border-gray-700 border-t-0 overflow-hidden">
                <form wire:submit.prevent="createMaterial" class="p-6">
                    <div class="space-y-5">
                        <!-- Title Field -->
                        <div>
                            <label class="text-sm font-semibold text-gray-200 mb-2 block">
                                Title (Optional)
                            </label>
                            <input type="text" 
                                   wire:model="title" 
                                   class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-500"
                                   placeholder="Material title">
                            @error('title') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <!-- Content Field (Trix Editor) -->
                        <div>
                            <x-form.trix-editor 
                                wire:model="content" 
                                label="Content" 
                                :required="true"
                                :error="$errors->first('content')" 
                            />
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="text-sm font-semibold text-gray-200 mb-2 block">Featured Image (Optional)</label>
                            <div class="relative">
                                @if($image)
                                    <div class="relative rounded-xl overflow-hidden border-2 border-burgundy-500 bg-gray-900 mb-3">
                                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-full h-40 object-cover">
                                        <button type="button" wire:click="$set('image', null)" 
                                                class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-500 text-white rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-900/50 hover:border-burgundy-500/50 transition-all duration-200 group mb-3">
                                        <svg class="w-10 h-10 mb-2 text-gray-500 group-hover:text-burgundy-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-400 group-hover:text-gray-300">
                                            <span class="font-semibold text-burgundy-400">Click to upload</span> or drag and drop
                                        </p>
                                        <input type="file" wire:model="image" accept="image/*" class="hidden">
                                    </label>
                                @endif
                            </div>
                            @error('image') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                            
                            <!-- Image Position -->
                            @if($image || $existing_image_path)
                            <div>
                                <label class="text-sm font-semibold text-gray-200 mb-2 block">Image Position</label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach(['top' => 'Top', 'left' => 'Left', 'right' => 'Right', 'bottom' => 'Bottom'] as $value => $label)
                                    <button type="button"
                                            wire:click="$set('image_position', '{{ $value }}')"
                                            class="px-3 py-2 rounded-lg text-sm font-medium transition-all
                                                   {{ $image_position === $value 
                                                      ? 'bg-burgundy-600 text-white' 
                                                      : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-700">
                        <button type="button" wire:click="toggleAddForm"
                                class="px-4 py-2.5 text-gray-400 hover:text-white transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                                class="px-6 py-2.5 bg-gradient-to-r from-burgundy-600 to-burgundy-500 text-white rounded-lg hover:from-burgundy-500 hover:to-burgundy-400 transition-all duration-200 font-semibold shadow-lg shadow-burgundy-900/30 flex items-center gap-2">
                            <span wire:loading.remove wire:target="createMaterial">Add Material</span>
                            <span wire:loading wire:target="createMaterial">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Materials List -->
    <div class="bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-700" 
         x-data="{ 
             materials: @js($materials->pluck('id')->toArray()),
             updateOrder() {
                 $wire.updateSortOrder(this.materials);
             }
         }"
         x-init="
             new Sortable($el.querySelector('#materials-list'), {
                 animation: 150,
                 handle: '.drag-handle',
                 onEnd: function() {
                     materials = Array.from($el.querySelectorAll('[data-material-id]')).map(el => parseInt(el.dataset.materialId));
                     updateOrder();
                 }
             });
         ">
        <div class="bg-gray-900/50 px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">All Materials (Drag to Reorder)</h3>
        </div>
        <div id="materials-list" class="divide-y divide-gray-700">
            @forelse($materials as $material)
                <div data-material-id="{{ $material->id }}" class="p-4 hover:bg-gray-750/50 transition-colors group">
                    <div class="flex items-start gap-4">
                        <!-- Drag Handle -->
                        <div class="drag-handle cursor-move flex-shrink-0 pt-2">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
                            </svg>
                        </div>

                        <!-- Content Preview -->
                        <div class="flex-1 min-w-0">
                            @if($material->title)
                                <h4 class="text-white font-semibold mb-1">{{ $material->title }}</h4>
                            @endif
                            <div class="text-gray-400 text-sm line-clamp-2 prose prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::limit(strip_tags($material->content), 200) !!}
                            </div>
                            @if($material->image_path)
                                <span class="inline-flex items-center mt-2 text-xs text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Has image ({{ ucfirst($material->image_position->value) }})
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center gap-2">
                            <button wire:click="openEditModal({{ $material->id }})" 
                                    class="p-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteMaterial({{ $material->id }})" 
                                    wire:confirm="Are you sure you want to delete this material?"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-300 mb-1">No materials yet</h3>
                    <p class="text-gray-500 text-sm">Add learning materials to help students before they start flashcards.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Edit Modal -->
    @if($show_edit_modal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="closeEditModal"></div>
                
                <div class="relative bg-gradient-to-b from-gray-800 to-gray-850 rounded-2xl shadow-2xl max-w-3xl w-full border border-gray-700 max-h-[90vh] overflow-hidden">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700 bg-gray-900/50">
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Material</h3>
                            <p class="text-sm text-gray-400 mt-0.5">Update the material content</p>
                        </div>
                        <button wire:click="closeEditModal" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form wire:submit.prevent="updateMaterial" class="p-6">
                            <div class="space-y-5">
                                <div>
                                    <label class="text-sm font-semibold text-gray-200 mb-2 block">Title (Optional)</label>
                                    <input type="text" wire:model="title" 
                                           class="w-full px-4 py-3 bg-gray-900/50 border border-gray-600 text-white rounded-xl focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500"
                                           placeholder="Material title">
                                    @error('title') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <x-form.trix-editor wire:model="content" label="Content" :required="true" :error="$errors->first('content')" />
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-gray-200 mb-2 block">Featured Image</label>
                                    <div class="relative">
                                        @if($image)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-burgundy-500 bg-gray-900 mb-3">
                                                <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-full h-40 object-cover">
                                                <button type="button" wire:click="$set('image', null)" 
                                                        class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-500 text-white rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @elseif($existing_image_path)
                                            <div class="relative rounded-xl overflow-hidden border border-gray-600 bg-gray-900 mb-3">
                                                <img src="{{ asset('storage/' . $existing_image_path) }}" alt="Current" class="w-full h-40 object-cover">
                                                <label class="absolute bottom-2 right-2 px-3 py-1.5 bg-burgundy-600 hover:bg-burgundy-500 text-white text-xs font-medium rounded-lg cursor-pointer transition">
                                                    Change
                                                    <input type="file" wire:model="image" accept="image/*" class="hidden">
                                                </label>
                                            </div>
                                        @else
                                            <label class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-900/50 hover:border-burgundy-500/50 transition-all duration-200 group mb-3">
                                                <svg class="w-10 h-10 mb-2 text-gray-500 group-hover:text-burgundy-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <input type="file" wire:model="image" accept="image/*" class="hidden">
                                            </label>
                                        @endif
                                    </div>
                                    @error('image') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                                    
                                    @if($image || $existing_image_path)
                                    <div>
                                        <label class="text-sm font-semibold text-gray-200 mb-2 block">Image Position</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach(['top' => 'Top', 'left' => 'Left', 'right' => 'Right', 'bottom' => 'Bottom'] as $value => $label)
                                            <button type="button"
                                                    wire:click="$set('image_position', '{{ $value }}')"
                                                    class="px-3 py-2 rounded-lg text-sm font-medium transition-all
                                                           {{ $image_position === $value 
                                                              ? 'bg-burgundy-600 text-white' 
                                                              : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                                                {{ $label }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-700">
                                <button type="button" wire:click="closeEditModal"
                                        class="px-5 py-2.5 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 hover:text-white transition font-medium">
                                    Cancel
                                </button>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-wait"
                                        class="px-6 py-2.5 bg-gradient-to-r from-burgundy-600 to-burgundy-500 text-white rounded-lg hover:from-burgundy-500 hover:to-burgundy-400 transition-all duration-200 font-semibold shadow-lg shadow-burgundy-900/30 flex items-center gap-2">
                                    <span wire:loading.remove wire:target="updateMaterial">Save Changes</span>
                                    <span wire:loading wire:target="updateMaterial">Saving...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @stack('styles')
    @stack('scripts')
    
    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        @endpush
    @endonce

    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>

