@props([
    'wire:model' => null,
    'id' => 'trix-editor-' . uniqid(),
    'value' => '',
    'label' => null,
    'error' => null,
    'required' => false,
])

<div>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-200 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-400">*</span>
            @endif
        </label>
    @endif

    <input 
        id="{{ $id }}" 
        type="hidden" 
        name="{{ $attributes->wire('model')->value() }}"
        {{ $attributes->wire('model') }}
        value="{{ $value }}"
    />
    
    <trix-editor 
        input="{{ $id }}" 
        class="trix-content bg-gray-900/50 border border-gray-600 rounded-xl focus:ring-2 focus:ring-burgundy-500 focus:border-burgundy-500 min-h-[200px]"
    ></trix-editor>

    @if($error)
        <p class="mt-1.5 text-sm text-red-400 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>

@once
    @push('styles')
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <style>
            trix-toolbar .trix-button-group button {
                border: 1px solid #4b5563 !important;
            }
            trix-toolbar .trix-button--icon {
                background-color: #1f2937 !important;
                border-color: #4b5563 !important;
            }
            trix-toolbar .trix-button--icon:hover {
                background-color: #374151 !important;
            }
            trix-toolbar .trix-button--icon.trix-active {
                background-color: #7c2d12 !important;
            }
            .trix-content {
                color: #e5e7eb;
            }
            .trix-content a {
                color: #9ca3af;
            }
            trix-editor {
                padding: 1rem;
            }
            trix-toolbar {
                background-color: #1f2937;
                border: 1px solid #4b5563;
                border-bottom: none;
                border-radius: 0.75rem 0.75rem 0 0;
            }
            trix-editor:focus {
                outline: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    @endpush
@endonce

