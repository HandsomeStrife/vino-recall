<div class="p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">Category Management</h1>
        <button wire:click="openCreateModal" 
                class="px-4 py-2 bg-burgundy-600 text-white rounded-lg hover:bg-burgundy-700 transition font-semibold">
            Create Category
        </button>
    </div>

    @if(session('message'))
        <div class="mb-4 bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-700">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Decks</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-750">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->image_path)
                                <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">{{ \Illuminate\Support\Str::limit($category->description, 60) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <span class="inline-flex items-center px-3 py-1 bg-gray-700 text-burgundy-400 rounded-lg text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ $category->decks_count ?? 0 }} Decks
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $category->is_active ? 'bg-green-900/50 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <button wire:click="openEditModal({{ $category->id }})" 
                                    class="px-3 py-1 bg-gray-700 text-gray-300 rounded hover:bg-gray-600 transition text-sm">
                                Edit
                            </button>
                            <button wire:click="deleteCategory({{ $category->id }})" 
                                    wire:confirm="Are you sure you want to delete this category? It will be removed from all decks."
                                    class="px-3 py-1 bg-red-900/50 text-red-300 rounded hover:bg-red-800/50 transition text-sm">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No categories found. Create your first category to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-black/70 transition-opacity" wire:click="closeModal"></div>
                
                <!-- Modal panel -->
                <div class="relative bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 border border-gray-700">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-white">
                            {{ $editingCategoryId ? 'Edit Category' : 'Create New Category' }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="{{ $editingCategoryId ? 'updateCategory' : 'createCategory' }}">
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="name" 
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-400"
                                       placeholder="Enter category name">
                                @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                <textarea wire:model="description" rows="3"
                                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 placeholder-gray-400"
                                          placeholder="Optional category description"></textarea>
                                @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <!-- Cover Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category Image</label>
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
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-burgundy-600 file:text-white hover:file:bg-burgundy-700 cursor-pointer">
                                <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 5MB</p>
                                @error('image') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <!-- Active Toggle -->
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active"
                                       class="rounded border-gray-600 bg-gray-700 text-burgundy-600 focus:ring-burgundy-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-300">Active</label>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                                <button type="button" wire:click="closeModal"
                                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition font-medium">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-burgundy-600 text-white rounded-lg hover:bg-burgundy-700 transition font-semibold">
                                    {{ $editingCategoryId ? 'Update Category' : 'Create Category' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

