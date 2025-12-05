<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-2">{{ __('admin.import_deck') }}</h1>
        <p class="text-gray-600">Import cards from CSV or TXT files into new or existing decks</p>
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
                <!-- Import Mode Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Import Destination</label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="importMode" value="new" 
                                   class="mr-2 text-burgundy-500 focus:ring-burgundy-500">
                            <span class="text-sm font-medium">Create New Deck</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="importMode" value="existing" 
                                   class="mr-2 text-burgundy-500 focus:ring-burgundy-500">
                            <span class="text-sm font-medium">Add to Existing Deck</span>
                        </label>
                    </div>
                </div>

                <!-- New Deck Options -->
                @if($importMode === 'new')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('admin.deck_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="deckName" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500"
                               placeholder="Enter deck name">
                        @error('deckName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('admin.description') }}
                        </label>
                        <textarea wire:model="description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500"
                                  placeholder="Optional deck description"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Existing Deck Selection -->
                @if($importMode === 'existing')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Deck <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="selectedDeckId" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                            <option value="">-- Select a deck --</option>
                            @foreach($decks as $deck)
                                <option value="{{ $deck->id }}">{{ $deck->name }} ({{ $deck->cards()->count() }} cards)</option>
                            @endforeach
                        </select>
                        @error('selectedDeckId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        <p class="text-xs text-gray-500 mt-1">
                            Cards with matching questions will be updated; new questions will be added.
                        </p>
                    </div>
                @endif

                <!-- File Format Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        File Format <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="format" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                        @foreach($formatOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('format') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- File Upload -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select File <span class="text-red-500">*</span>
                    </label>
                    <input type="file" wire:model="file" 
                           accept=".csv,.txt"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-burgundy-500 focus:border-burgundy-500">
                    @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    
                    <div wire:loading wire:target="file" class="text-sm text-burgundy-600 mt-2">
                        <svg class="inline-block w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading file...
                    </div>
                </div>

                <!-- Validation Result -->
                @if(!empty($validationResult))
                    <div class="mb-4 p-4 rounded-lg {{ $validationResult['valid'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                        <h4 class="font-semibold {{ $validationResult['valid'] ? 'text-green-800' : 'text-red-800' }} mb-2">
                            {{ $validationResult['valid'] ? 'File Validation Passed' : 'File Validation Failed' }}
                        </h4>
                        
                        @if(!empty($validationResult['errors']))
                            <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach($validationResult['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        
                        @if(!empty($validationResult['warnings']))
                            <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1 mt-2">
                                @foreach($validationResult['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if(isset($validationResult['row_count']))
                            <p class="text-sm mt-2 {{ $validationResult['valid'] ? 'text-green-700' : 'text-gray-600' }}">
                                Found {{ $validationResult['row_count'] }} data rows
                                @if(isset($validationResult['valid_rows']))
                                    ({{ $validationResult['valid_rows'] }} valid)
                                @endif
                            </p>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="button" 
                            wire:click="validateFile"
                            wire:loading.attr="disabled"
                            class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition font-semibold border border-gray-300">
                        <span wire:loading.remove wire:target="validateFile">Validate File</span>
                        <span wire:loading wire:target="validateFile">Validating...</span>
                    </button>
                    
                    <button type="submit" 
                            class="flex-1 bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="import">Start Import</span>
                        <span wire:loading wire:target="import">Starting...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-burgundy-900 mb-4">Import Instructions</h2>

            <div class="space-y-4">
                <!-- CSV Format -->
                <div>
                    <h3 class="font-semibold text-burgundy-800 mb-2">CSV Format (Comma-Separated)</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        Standard CSV files with comma-separated columns:
                    </p>
                    <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                        <li><code class="bg-gray-100 px-1 rounded">question</code> - The question text (required)</li>
                        <li><code class="bg-gray-100 px-1 rounded">answer</code> - The answer explanation (required)</li>
                        <li><code class="bg-gray-100 px-1 rounded">image_path</code> - Optional image path</li>
                        <li><code class="bg-gray-100 px-1 rounded">card_type</code> - Always "multiple_choice"</li>
                        <li><code class="bg-gray-100 px-1 rounded">answer_choices</code> - JSON array: ["A","B","C","D"]</li>
                        <li><code class="bg-gray-100 px-1 rounded">correct_answer_indices</code> - JSON array: [0] or [0,2]</li>
                    </ul>
                    <button wire:click="downloadTemplate" 
                            class="mt-3 text-burgundy-600 hover:text-burgundy-800 text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download CSV Template
                    </button>
                </div>

                <!-- TXT Format -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-burgundy-800 mb-2">TXT Format (Tab-Separated)</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        Plain text files with tab-separated columns (same column order as CSV):
                    </p>
                    <div class="bg-gray-50 p-3 rounded text-xs font-mono text-gray-700 overflow-x-auto">
                        question[TAB]answer[TAB]image_path[TAB]card_type[TAB]answer_choices[TAB]correct_answer_indices
                    </div>
                    <button wire:click="downloadTxtTemplate" 
                            class="mt-3 text-burgundy-600 hover:text-burgundy-800 text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download TXT Template
                    </button>
                </div>

                <!-- Import Behavior -->
                <div class="bg-burgundy-50 border border-burgundy-200 rounded p-3">
                    <h4 class="text-sm font-semibold text-burgundy-800 mb-1">Import Behavior</h4>
                    <ul class="text-sm text-burgundy-700 list-disc list-inside space-y-1">
                        <li>Cards with matching questions will be <strong>updated</strong></li>
                        <li>New questions will be <strong>added</strong></li>
                        <li>Invalid rows will be <strong>skipped</strong></li>
                        <li>If import fails, no cards will be added</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Tip:</strong> Use "Validate File" before importing to check for errors.
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deck</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Results</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($imports as $import)
                            <tr class="{{ $import->isProcessing() || $import->isPending() ? 'bg-yellow-50' : '' }}">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium">{{ $import->original_filename ?? $import->filename }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($import->deck)
                                        {{ $import->deck->name }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
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
                                <td class="px-4 py-3 text-sm">
                                    @if($import->isCompleted())
                                        <div class="text-green-700">+{{ $import->imported_cards_count }} new</div>
                                        @if($import->updated_cards_count > 0)
                                            <div class="text-blue-600">{{ $import->updated_cards_count }} updated</div>
                                        @endif
                                        @if($import->skipped_rows > 0)
                                            <div class="text-yellow-600">{{ $import->skipped_rows }} skipped</div>
                                        @endif
                                    @elseif($import->isFailed())
                                        <span class="text-red-600 text-xs">{{ \Illuminate\Support\Str::limit($import->error_message, 50) }}</span>
                                    @elseif($import->isProcessing() || $import->isPending())
                                        <span class="text-gray-500">Processing...</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $import->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No imports yet</p>
        @endif
    </div>

    <!-- Progress Modal -->
    @if($showProgressModal && $this->currentImport)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             wire:poll.1s="refreshImportStatus">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-burgundy-900">Import Progress</h3>
                    @if($this->currentImport->isCompleted() || $this->currentImport->isFailed())
                        <button wire:click="closeProgressModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="space-y-4">
                    <!-- Status -->
                    <div class="flex items-center gap-3">
                        @if($this->currentImport->isPending() || $this->currentImport->isProcessing())
                            <svg class="w-6 h-6 animate-spin text-burgundy-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-burgundy-700 font-medium">Processing import...</span>
                        @elseif($this->currentImport->isCompleted())
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-green-700 font-medium">Import completed successfully!</span>
                        @elseif($this->currentImport->isFailed())
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-700 font-medium">Import failed</span>
                        @endif
                    </div>

                    <!-- Progress Bar -->
                    @if($this->currentImport->isProcessing() && $this->currentImport->total_rows > 0)
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-burgundy-500 h-2.5 rounded-full transition-all duration-300" 
                                 style="width: {{ $this->currentImport->getProgressPercentage() }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600 text-center">
                            {{ $this->currentImport->getProgressPercentage() }}% complete
                        </p>
                    @endif

                    <!-- Results -->
                    @if($this->currentImport->isCompleted())
                        <div class="bg-green-50 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">New cards added:</span>
                                <span class="font-semibold text-green-700">{{ $this->currentImport->imported_cards_count }}</span>
                            </div>
                            @if($this->currentImport->updated_cards_count > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Cards updated:</span>
                                    <span class="font-semibold text-blue-700">{{ $this->currentImport->updated_cards_count }}</span>
                                </div>
                            @endif
                            @if($this->currentImport->skipped_rows > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Rows skipped:</span>
                                    <span class="font-semibold text-yellow-700">{{ $this->currentImport->skipped_rows }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm border-t pt-2 mt-2">
                                <span class="text-gray-600">Total rows processed:</span>
                                <span class="font-semibold">{{ $this->currentImport->total_rows }}</span>
                            </div>
                        </div>

                        @if($this->currentImport->deck)
                            <a href="{{ route('admin.cards') }}?deck={{ $this->currentImport->deck_id }}" 
                               class="block w-full text-center bg-burgundy-500 text-white px-4 py-2 rounded-lg hover:bg-burgundy-600 transition font-medium">
                                View Cards in Deck
                            </a>
                        @endif
                    @endif

                    <!-- Error Message -->
                    @if($this->currentImport->isFailed() && $this->currentImport->error_message)
                        <div class="bg-red-50 rounded-lg p-4">
                            <p class="text-sm text-red-700">{{ $this->currentImport->error_message }}</p>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($this->currentImport->validation_errors && count($this->currentImport->validation_errors) > 0)
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-yellow-800 mb-2">Warnings / Skipped Rows:</h4>
                            <ul class="text-xs text-yellow-700 list-disc list-inside space-y-1 max-h-32 overflow-y-auto">
                                @foreach(array_slice($this->currentImport->validation_errors, 0, 10) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @if(count($this->currentImport->validation_errors) > 10)
                                    <li class="text-yellow-600">... and {{ count($this->currentImport->validation_errors) - 10 }} more</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Close Button -->
                    @if($this->currentImport->isCompleted() || $this->currentImport->isFailed())
                        <button wire:click="closeProgressModal" 
                                class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                            Close
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
