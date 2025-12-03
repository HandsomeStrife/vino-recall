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
                <div class="space-y-6">
                    <!-- Deck Selection -->
                    <div>
                        <x-form.label>Deck</x-form.label>
                        <x-form.select name="deck_id" wire:model="deck_id" :options="['' => 'Select a deck...'] + $decks->pluck('name', 'id')->toArray()" :selected="$deck_id ?? ''" />
                        @error('deck_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Card Type Selection -->
                    <div>
                        <x-form.label>Card Type</x-form.label>
                        <div class="mt-2 space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model.live="card_type" value="multiple_choice" class="form-radio text-burgundy-600">
                                <span class="ml-2 text-sm text-gray-700">Multiple Choice (Recommended)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model.live="card_type" value="traditional" class="form-radio text-burgundy-600">
                                <span class="ml-2 text-sm text-gray-700">Traditional Q&A</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question -->
                    <div>
                        <x-form.label>Question</x-form.label>
                        <x-form.textarea name="question" wire:model="question" placeholder="e.g., What grape variety is used in Bordeaux red wines?" />
                        @error('question') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Multiple Choice Options -->
                    @if($card_type === 'multiple_choice')
                        <div class="bg-burgundy-50 p-4 rounded-lg border border-burgundy-200">
                            <h3 class="text-sm font-semibold text-burgundy-900 mb-3">Answer Choices</h3>
                            <p class="text-xs text-gray-600 mb-4">Add at least 2 answer choices. Check the correct answer.</p>
                            <div class="space-y-3">
                                @foreach($answer_choices as $index => $choice)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center pt-2">
                                            <input type="radio" 
                                                   wire:model="correct_answer_index" 
                                                   value="{{ $index }}" 
                                                   class="form-radio text-green-600 h-5 w-5"
                                                   id="correct_{{ $index }}">
                                        </div>
                                        <div class="flex-1">
                                            <label for="correct_{{ $index }}" class="text-xs text-gray-500 block mb-1">
                                                Choice {{ chr(65 + $index) }}
                                                @if($correct_answer_index == $index)
                                                    <span class="text-green-600 font-semibold">(Correct Answer)</span>
                                                @endif
                                            </label>
                                            <input type="text" 
                                                   wire:model="answer_choices.{{ $index }}" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500"
                                                   placeholder="Enter answer choice {{ chr(65 + $index) }}...">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('answer_choices') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('correct_answer_index') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Hidden Answer field (auto-filled with correct choice) -->
                        <input type="hidden" wire:model="answer" value="{{ $answer_choices[$correct_answer_index] ?? '' }}">
                    @else
                        <!-- Traditional Answer -->
                        <div>
                            <x-form.label>Answer</x-form.label>
                            <x-form.textarea name="answer" wire:model="answer" placeholder="Enter the answer..." />
                            @error('answer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div>
                        <x-form.label>Image (Optional)</x-form.label>
                        <input type="file" 
                               wire:model="image" 
                               class="mt-1 block w-full text-sm text-gray-500 
                                      file:mr-4 file:py-2 file:px-4 
                                      file:rounded-lg file:border-0 
                                      file:text-sm file:font-semibold 
                                      file:bg-burgundy-50 file:text-burgundy-700 
                                      hover:file:bg-burgundy-100 cursor-pointer">
                        <p class="mt-1 text-xs text-gray-500">Upload an image to accompany the question (max 5MB)</p>
                        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3 pt-4 border-t">
                        <x-button.button type="submit" color="burgundy">
                            {{ $editingCardId ? 'Update' : 'Create' }} Card
                        </x-button.button>
                        @if($editingCardId)
                            <x-button.button type="button" variant="secondary" wire:click="$set('editingCardId', null)">
                                Cancel
                            </x-button.button>
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

