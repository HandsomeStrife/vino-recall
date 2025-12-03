<x-layout>
    <div class="flex h-screen">
        <x-layout.admin.sidebar />
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</x-layout>