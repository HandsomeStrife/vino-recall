<x-layout :hideHeader="true" :hideFooter="true">
    <div class="flex h-screen">
        <x-layout.admin.sidebar />
        <main class="flex-1 overflow-y-auto bg-gray-900">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</x-layout>