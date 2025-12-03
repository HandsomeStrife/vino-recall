<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
        </svg>
        <span>{{ app()->getLocale() }}</span>
    </button>

    <div x-show="open" 
         @click.away="open = false"
         x-transition
         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 z-50">
        @php
            $localizationService = app(\App\Services\LocalizationService::class);
            $supportedLocales = $localizationService->getSupportedLocales();
            $currentLocale = app()->getLocale();
        @endphp
        
        @foreach($supportedLocales as $locale)
            <button wire:click="updateLocale('{{ $locale }}')" 
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between {{ $locale === $currentLocale ? 'bg-burgundy-50 text-burgundy-900' : '' }}">
                <span>{{ $localizationService->getLocaleLabel($locale) }}</span>
                @if($locale === $currentLocale)
                    <svg class="w-4 h-4 text-burgundy-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </button>
        @endforeach
    </div>
</div>

