<x-layout>
    <div class="min-h-screen flex flex-col">
        <x-layout.default.header :showNavigation="$showNavigation ?? true" />
        <main class="flex-1">
            {{ $slot }}
        </main>
        @if(!isset($hideFooter) || !$hideFooter)
            <x-layout.default.footer />
        @endif
    </div>
</x-layout>