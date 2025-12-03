<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'VinoRecall') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('css/app.css') }}">
            <script src="{{ asset('js/app.js') }}"></script>
        @endif
        @livewireStyles
    </head>
    <body class="bg-cream-50 font-sans text-gray-800">
        <div class="min-h-screen flex flex-col">
            <x-layout.default.header :showNavigation="$showNavigation ?? true" />
            <main class="flex-1">
                {{ $slot }}
            </main>
            @if(!isset($hideFooter) || !$hideFooter)
                <x-layout.default.footer />
            @endif
        </div>
        @livewireScripts
    </body>
</html>

