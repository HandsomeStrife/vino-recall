<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Card Management</h1>

        @if(session('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ $editingCardId ? 'Edit Card' : 'Create New Card' }}</h2>
            <form wire:submit.prevent="{{ $editingCardId ? 'updateCard' : 'createCard' }}">
                <div class="space-y-4">
                    <div>
                        <x-form.label>Deck</x-form.label>
                        <x-form.select name="deck_id" wire:model="deck_id" :options="['' => 'Select a deck...'] + $decks->pluck('name', 'id')->toArray()" :selected="$deck_id ?? ''" />
                        @error('deck_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-form.label>Question</x-form.label>
                        <x-form.textarea name="question" wire:model="question" />
                    </div>
                    <div>
                        <x-form.label>Answer</x-form.label>
                        <x-form.textarea name="answer" wire:model="answer" />
                    </div>
                    <div>
                        <x-form.label>Image</x-form.label>
                        <input type="file" wire:model="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-burgundy-50 file:text-burgundy-700 hover:file:bg-burgundy-100">
                    </div>
                    <div>
                        <x-button.button type="submit">{{ $editingCardId ? 'Update' : 'Create' }} Card</x-button.button>
                        @if($editingCardId)
                            <x-button.button type="button" variant="secondary" wire:click="$set('editingCardId', null)">Cancel</x-button.button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deck</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($cards as $card)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center space-x-2">
                                    @if($card->image_path)
                                        <img src="{{ asset('storage/' . $card->image_path) }}" alt="Card image" class="w-12 h-12 object-cover rounded" loading="lazy">
                                    @endif
                                    <span>{{ \Illuminate\Support\Str::limit($card->question, 50) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($card->answer, 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $decks->firstWhere('id', $card->deck_id)?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 space-x-2">
                                <x-button.button variant="secondary" wire:click="editCard({{ $card->id }})">Edit</x-button.button>
                                <x-button.button variant="danger" wire:click="deleteCard({{ $card->id }})" wire:confirm="Are you sure you want to delete this card?">Delete</x-button.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

