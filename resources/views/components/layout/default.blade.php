@props(['showNavigation' => true, 'hideFooter' => false, 'showSidebar' => false])

<x-layout :showNavigation="$showNavigation" :hideFooter="$hideFooter">
    {{ $slot }}
</x-layout>
