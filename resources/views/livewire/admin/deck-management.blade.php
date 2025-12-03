<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Deck Management</h1>

        @if(session('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ $editingDeckId ? 'Edit Deck' : 'Create New Deck' }}</h2>
            <form wire:submit.prevent="{{ $editingDeckId ? 'updateDeck' : 'createDeck' }}">
                <div class="space-y-4">
                    <div>
                        <x-form.label>Name</x-form.label>
                        <x-form.input name="name" wire:model="name" />
                    </div>
                    <div>
                        <x-form.label>Description</x-form.label>
                        <x-form.textarea name="description" wire:model="description" />
                    </div>
                    <div>
                        <x-form.checkbox name="is_active" wire:model="is_active" :checked="$is_active" />
                        <x-form.label>Active</x-form.label>
                    </div>
                    <div>
                        <x-button.button type="submit">{{ $editingDeckId ? 'Update' : 'Create' }} Deck</x-button.button>
                        @if($editingDeckId)
                            <x-button.button type="button" variant="secondary" wire:click="$set('editingDeckId', null)">Cancel</x-button.button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($decks as $deck)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $deck->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $deck->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge.badge :variant="$deck->is_active ? 'success' : 'default'">
                                    {{ $deck->is_active ? 'Active' : 'Inactive' }}
                                </x-badge.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 space-x-2">
                                <x-button.button variant="secondary" wire:click="editDeck({{ $deck->id }})">Edit</x-button.button>
                                <x-button.button variant="danger" wire:click="deleteDeck({{ $deck->id }})" wire:confirm="Are you sure?">Delete</x-button.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

