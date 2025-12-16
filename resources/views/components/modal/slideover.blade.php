@props([
    'button' => null,
    'title' => null,
    'actions' => null,
    'light' => false
])
<div x-data="{ 
        open: false, 
        __isOpenState: false,
        init() {
            this.$watch('open', value => {
                if (value) {
                    Livewire.dispatch('slideoverOpened');
                } else {
                    Livewire.dispatch('slideoverClosed');
                }
            });
        }
     }"
     x-on:slideover-close.window="open = false"
     {{ $attributes }}>
    <!-- Trigger -->
    @if ($button)
        <div x-on:click="open = true">
            {{ $button }}
        </div>
    @endif

    <!-- Slideover -->
    <div
        @click.stop
        x-dialog
        x-model="open"
        style="display: none"
        class="fixed inset-0 overflow-hidden z-50"
    >
        <!-- Overlay -->
        <div x-transition.opacity class="fixed inset-0 bg-black/75"></div>

        <!-- Panel -->
        <div class="fixed inset-y-0 right-0 max-w-2xl w-full">
            <div
                x-dialog:panel
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="h-full w-full"
            >
                <div class="h-full flex flex-col justify-between {{ $light ? 'bg-white text-slate-800' : 'bg-zinc-900 text-white' }} shadow-lg overflow-y-auto font-normal">
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="$dialog.close(); Livewire.dispatch('slideoverClosed')" class="{{ $light ? 'bg-gray-50 text-gray-600 hover:text-gray-800' : 'bg-zinc-800 text-gray-300 hover:text-white' }} rounded-lg p-2 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            <span class="sr-only">Close slideover</span>

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-8">
                        <!-- Title -->
                        <div>
                            <h4 x-dialog:title>{{ $title }}</h4>
                        </div>

                        <!-- Content -->
                        <div class="mt-4 {{ $light ? 'text-gray-600' : 'text-gray-300' }} flex-1">
                            {{ $slot }}
                        </div>
                    </div>

                    @if ($actions)
                        <!-- Footer -->
                        <div class="p-4 flex justify-end space-x-2 {{ $light ? 'bg-gray-50' : 'bg-zinc-800 border-t border-zinc-700' }}">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
