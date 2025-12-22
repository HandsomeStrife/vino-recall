<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-white">Deck Management</h1>
        <button wire:click="openCreateModal" 
                class="px-3 py-1.5 bg-burgundy-600 text-white rounded-lg hover:bg-burgundy-700 transition font-medium text-sm">
            + New Deck
        </button>
    </div>

    @if(session('message'))
        <div class="mb-3 bg-green-900/50 border border-green-700 text-green-300 px-3 py-2 rounded text-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-700">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900/80">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-16">Deck</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Details</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400 uppercase tracking-wider w-24">Categories</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-32">Content</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700/50">
                @forelse($decks as $deck)
                    <tr class="hover:bg-gray-750/50 {{ $deck->parent_deck_id ? 'bg-gray-850/30' : '' }}">
                        {{-- Deck Image with Status Indicator --}}
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                @if($deck->parent_deck_id)
                                    <span class="text-gray-600 text-xs">|--</span>
                                @endif
                                <div class="relative">
                                    @if($deck->image_path)
                                        <img src="{{ asset('storage/' . $deck->image_path) }}" alt="{{ $deck->name }}" class="w-10 h-10 object-cover rounded">
                                    @else
                                        <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    {{-- Active/Inactive Status Circle --}}
                                    <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-gray-800 {{ $deck->is_active ? 'bg-green-500' : 'bg-red-500' }}" title="{{ $deck->is_active ? 'Active' : 'Inactive' }}"></span>
                                </div>
                            </div>
                        </td>

                        {{-- Name & Description Combined --}}
                        <td class="px-3 py-2">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-medium text-white leading-tight">{{ $deck->name }}</span>
                                    @if($deck->is_collection)
                                        <span class="inline-flex items-center px-1 py-0.5 bg-purple-900/50 text-purple-300 rounded text-[10px] font-medium">Collection</span>
                                    @elseif($deck->parent_deck_id)
                                        <span class="inline-flex items-center px-1 py-0.5 bg-blue-900/50 text-blue-300 rounded text-[10px] font-medium">Child</span>
                                    @endif
                                </div>
                                @if($deck->parent_name)
                                    <span class="text-xs text-gray-500">in {{ $deck->parent_name }}</span>
                                @endif
                                @if($deck->description)
                                    <span class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $deck->description }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Categories Count --}}
                        <td class="px-3 py-2 text-center">
                            @if($deck->categories && $deck->categories->isNotEmpty())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-700 text-gray-200 rounded text-xs font-medium" title="{{ $deck->categories->pluck('name')->join(', ') }}">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    {{ $deck->categories->count() }}
                                </span>
                            @else
                                <span class="text-xs text-gray-500">--</span>
                            @endif
                        </td>

                        {{-- Content (Cards & Materials) --}}
                        <td class="px-3 py-2">
                            @if($deck->is_collection)
                                <span class="text-xs text-gray-600">--</span>
                            @else
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.decks.cards', $deck->id) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 bg-gray-700/70 text-gray-300 rounded hover:bg-gray-600 hover:text-white transition text-xs cursor-pointer"
                                       title="Manage Cards">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        {{ $deck->cards_count ?? 0 }}
                                    </a>
                                    <a href="{{ route('admin.decks.materials', $deck->id) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 bg-gray-700/70 text-gray-300 rounded hover:bg-gray-600 hover:text-white transition text-xs cursor-pointer"
                                       title="Learning Materials">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        Mat
                                    </a>
                                </div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-3 py-2 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openEditModal({{ $deck->id }})" 
                                        class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition cursor-pointer"
                                        title="Edit Deck">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="deleteDeck({{ $deck->id }})" 
                                        wire:confirm="Are you sure you want to delete this deck?{{ $deck->is_collection ? ' Child decks will become standalone.' : '' }}"
                                        class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-red-900/30 rounded transition cursor-pointer"
                                        title="Delete Deck">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-gray-400 text-sm">
                            No decks found. Create your first deck to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Slideover -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-hidden">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/75 transition-opacity" wire:click="closeModal"></div>

            <!-- Panel -->
            <div class="fixed inset-y-0 right-0 max-w-2xl w-full">
                <div class="h-full w-full bg-zinc-900 text-white shadow-lg overflow-y-auto">
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeModal" class="bg-zinc-800 text-gray-300 hover:text-white rounded-lg p-2 cursor-pointer">
                            <span class="sr-only">Close slideover</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <h4 class="text-xl font-semibold text-white mb-6">
                            {{ $editingDeckId ? 'Edit Deck' : 'Create New Deck' }}
                        </h4>
                        <!-- Content -->
                        <form wire:submit.prevent="{{ $editingDeckId ? 'updateDeck' : 'createDeck' }}" id="deck-form" class="text-gray-300">
            <div class="space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" 
                           class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-400"
                           placeholder="Enter deck name">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea wire:model="description" rows="3"
                              class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-400"
                              placeholder="Optional deck description"></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Deck Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Deck Type <span class="text-red-400">*</span></label>
                    <select wire:model.live="deck_type"
                            class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                        <option value="standalone">Standalone - Regular deck with cards</option>
                        <option value="collection">Collection - Container for child decks (no cards)</option>
                        <option value="child">Child - Belongs to a collection</option>
                    </select>
                    @error('deck_type') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Parent Deck (only shown when type is 'child') -->
                @if($deck_type === 'child')
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Parent Collection <span class="text-red-400">*</span></label>
                        <select wire:model="parent_deck_id"
                                class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                            <option value="">Select a collection...</option>
                            @foreach($availableParents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @if($availableParents->isEmpty())
                            <p class="mt-1 text-sm text-yellow-400">No collections available. Create a collection first.</p>
                        @endif
                        @error('parent_deck_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                <!-- Categories -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Categories</label>
                    <select wire:model="selectedCategories" multiple size="5"
                            class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple categories</p>
                    @error('selectedCategories') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Cover Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cover Image</label>
                    @if($existingImagePath && !$image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $existingImagePath) }}" alt="Current image" class="w-32 h-24 object-cover rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                        </div>
                    @endif
                    @if($image)
                        <div class="mb-3">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-32 h-24 object-cover rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">New image preview</p>
                        </div>
                    @endif
                    <input type="file" wire:model="image" accept="image/*"
                           class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 text-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-burgundy-600 file:text-white hover:file:bg-burgundy-700 cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 5MB</p>
                    @error('image') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_active" id="is_active"
                           class="rounded border-zinc-600 bg-zinc-800 text-burgundy-600 focus:ring-burgundy-500 cursor-pointer">
                    <label for="is_active" class="ml-2 text-sm text-gray-300 cursor-pointer">Active</label>
                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 flex justify-end space-x-2 bg-zinc-800 border-t border-zinc-700">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 bg-zinc-700 text-gray-300 rounded-lg hover:bg-zinc-600 transition font-medium cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" form="deck-form"
                                class="px-4 py-2 bg-burgundy-600 text-white rounded-lg hover:bg-burgundy-700 transition font-semibold cursor-pointer">
                            {{ $editingDeckId ? 'Update Deck' : 'Create Deck' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
