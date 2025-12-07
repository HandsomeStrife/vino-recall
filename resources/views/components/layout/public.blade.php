@props(['hideFooter' => false])

<x-layout :hideFooter="$hideFooter">
    {{ $slot }}
</x-layout>
