<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'VinoRecall') }} - Admin</title>
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
        <div class="flex h-screen">
            <x-layout.admin.sidebar />
            <main class="flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>

