<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-2">{{ __('admin.import_deck') }}</h1>
        <p class="text-gray-600">Import decks from CSV or APKG files</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Import Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-burgundy-900 mb-4">{{ __('admin.upload_file') }}</h2>

            <form wire:submit.prevent="import">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('admin.deck_name') }}
                    </label>
                    <input type="text" wire:model="deckName" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500"
                           required>
                    @error('deckName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('admin.description') }}
                    </label>
                    <textarea wire:model="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('admin.file_format') }}
                    </label>
                    <select wire:model="format" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                        <option value="csv">{{ __('admin.csv') }}</option>
                        <option value="apkg">{{ __('admin.apkg') }}</option>
                    </select>
                    @error('format') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select File
                    </label>
                    <input type="file" wire:model="file" 
                           accept=".csv,.txt,.apkg"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                    @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    
                    <div wire:loading wire:target="file" class="text-sm text-gray-600 mt-2">
                        Uploading file...
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="import">Import Deck</span>
                    <span wire:loading wire:target="import">Importing...</span>
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-burgundy-900 mb-4">Import Instructions</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-burgundy-800 mb-2">CSV Format</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        CSV files should contain the following columns:
                    </p>
                    <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                        <li>question (required)</li>
                        <li>answer (required)</li>
                        <li>image_path (optional)</li>
                        <li>card_type (traditional or multiple_choice)</li>
                        <li>answer_choices (JSON array for MC questions)</li>
                        <li>correct_answer_index (for MC questions)</li>
                    </ul>
                    <button wire:click="downloadTemplate" 
                            class="mt-3 text-burgundy-600 hover:text-burgundy-800 text-sm font-medium">
                        Download CSV Template →
                    </button>
                </div>

                <div>
                    <h3 class="font-semibold text-burgundy-800 mb-2">APKG Format</h3>
                    <p class="text-sm text-gray-600">
                        APKG files are standard SRS/Anki deck exports. The import will extract cards and create a new deck in VinoRecall.
                    </p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> Large imports may take a few moments to process. Please be patient.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Import History -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-burgundy-900 mb-4">Recent Imports</h2>

        @if($imports->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filename</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cards Imported</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($imports as $import)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $import->filename }}</td>
                                <td class="px-4 py-3 text-sm uppercase">{{ $import->format->value }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        {{ $import->status->value === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $import->status->value === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $import->status->value === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $import->status->value === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($import->status->value) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $import->imported_cards_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ $import->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No imports yet</p>
        @endif
    </div>
</div>
