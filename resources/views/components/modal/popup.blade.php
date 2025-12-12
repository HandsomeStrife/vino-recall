@props([
    'button' => null,
    'title' => null,
    'actions' => null,
    'light' => false
])

<div x-data="{ open: false }" 
    {{ $attributes->only(['x-modelable', 'x-model', 'x-init']) }}
     x-on:popup-close.window="open = false">
    
    @if ($button)
        <div x-on:click="open = true;">
            {{ $button }}
        </div>
    @endif

    <div 
        x-show="open"
        @keydown.escape.window="open = false"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-black/80" 
                aria-hidden="true"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Gradient border wrapper -->
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-cloak
                class="relative z-10 inline-block w-full text-left align-bottom transition-all transform rounded-lg max-w-3xl sm:my-8 sm:align-middle {{ $light ? 'bg-white shadow-xs' : 'p-[2px] bg-linear-to-br from-purple-300 via-pink-300 to-yellow-300 shadow-xl' }}"
            >
                <!-- Inner content container -->
                <div class="relative overflow-hidden rounded-lg {{ $light ? 'bg-white border border-gray-200' : 'bg-black text-white' }}">
                    <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block z-10">
                        <button 
                            @click="open = false" 
                            type="button" 
                            class="{{ $light ? 'text-gray-400 hover:text-gray-500' : 'text-gray-400 hover:text-white' }} focus:outline-hidden transition-colors duration-200"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if ($title)
                        <!-- Title -->
                        <div class="px-6 py-4 border-b {{ $light ? 'border-gray-200' : 'border-zinc-800' }}">
                            <h4 class="{{ $light ? 'text-gray-800' : 'text-white' }}">{{ $title }}</h4>
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="px-6 py-4">
                        {{ $slot }}
                    </div>

                    @if ($actions)
                        <!-- Buttons -->
                        <div class="mt-8 flex justify-end gap-2 px-6 py-4 border-t {{ $light ? 'border-gray-200' : 'border-zinc-800' }}">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
