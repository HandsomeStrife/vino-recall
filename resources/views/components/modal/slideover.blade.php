@props(['show' => false, 'title' => ''])

<div x-data="{ show: @js($show) }" x-show="show" x-cloak
    class="fixed inset-0 z-50 overflow-hidden"
    x-transition:enter="ease-in-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in-out duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="w-screen max-w-md"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">
            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                <div class="flex items-center justify-between px-4 py-6 border-b">
                    <h2 class="text-lg font-medium text-gray-900">{{ $title }}</h2>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 px-4 py-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>

