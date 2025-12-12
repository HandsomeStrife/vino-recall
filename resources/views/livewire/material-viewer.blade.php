<div class="relative min-h-dvh bg-cream-50" 
     x-data="{ 
         imageLoaded: false 
     }"
     @keydown.window="
         if ($event.key === 'ArrowRight' && {{ $current_index }} < {{ $total_materials - 1 }}) {
             $wire.next();
         } else if ($event.key === 'ArrowLeft' && {{ $current_index }} > 0) {
             $wire.previous();
         }
     ">
    
    <!-- Top bar with Exit button -->
    <div class="absolute top-2 right-2 sm:top-4 sm:right-4 z-10">
        @if($standalone)
            <a href="{{ $exit_url }}" 
               class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-medium text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Exit
            </a>
        @endif
    </div>

    <div class="pt-14 pb-16 px-4 sm:p-6 md:p-8 flex items-start sm:items-center justify-center min-h-dvh">
        @if($materials->count() > 0 && $current_material)
            <div class="max-w-4xl w-full">
                <!-- Progress indicator -->
                <div class="mb-4 flex items-center justify-between gap-2">
                    <x-badge.badge variant="primary" class="text-sm">{{ $deck->name }}</x-badge.badge>
                    <div class="text-right flex-shrink-0">
                        <div class="text-xs sm:text-sm font-medium text-gray-700">
                            {{ $current_index + 1 }} / {{ $total_materials }}
                        </div>
                        <div class="w-20 sm:w-32 h-1.5 sm:h-2 bg-gray-200 rounded-full mt-1">
                            <div class="h-1.5 sm:h-2 bg-burgundy-500 rounded-full transition-all" 
                                 style="width: {{ (($current_index + 1) / $total_materials) * 100 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Material Card -->
                <div class="bg-white rounded-lg border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-4 sm:p-6 md:p-8 mb-6">
                    @if($current_material->title)
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-burgundy-900 mb-4 sm:mb-6">
                            {{ $current_material->title }}
                        </h2>
                    @endif

                    <div class="prose prose-burgundy max-w-none">
                        @if($current_material->image_path && $current_material->image_position->value === 'top')
                            <div class="mb-6">
                                <img src="{{ asset('storage/' . $current_material->image_path) }}" 
                                     alt="{{ $current_material->title ?? 'Material image' }}" 
                                     class="w-full rounded-lg shadow-md"
                                     loading="lazy">
                            </div>
                        @endif

                        @if($current_material->image_path && $current_material->image_position->value === 'left')
                            <div class="float-left mr-6 mb-4 max-w-sm">
                                <img src="{{ asset('storage/' . $current_material->image_path) }}" 
                                     alt="{{ $current_material->title ?? 'Material image' }}" 
                                     class="w-full rounded-lg shadow-md"
                                     loading="lazy">
                            </div>
                        @endif

                        @if($current_material->image_path && $current_material->image_position->value === 'right')
                            <div class="float-right ml-6 mb-4 max-w-sm">
                                <img src="{{ asset('storage/' . $current_material->image_path) }}" 
                                     alt="{{ $current_material->title ?? 'Material image' }}" 
                                     class="w-full rounded-lg shadow-md"
                                     loading="lazy">
                            </div>
                        @endif

                        <div class="text-gray-800">
                            {!! $current_material->content !!}
                        </div>

                        @if($current_material->image_path && $current_material->image_position->value === 'bottom')
                            <div class="mt-6">
                                <img src="{{ asset('storage/' . $current_material->image_path) }}" 
                                     alt="{{ $current_material->title ?? 'Material image' }}" 
                                     class="w-full rounded-lg shadow-md"
                                     loading="lazy">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div class="flex items-center justify-between gap-4">
                    <button wire:click="previous" 
                            :disabled="{{ $current_index === 0 }}"
                            class="px-4 py-2 sm:px-6 sm:py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous
                    </button>

                    @if(!$standalone && $current_index === 0)
                        <button wire:click="skip"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                            Skip All
                        </button>
                    @endif

                    @if($current_index < $total_materials - 1)
                        <button wire:click="next"
                                class="px-4 py-2 sm:px-6 sm:py-3 bg-burgundy-500 hover:bg-burgundy-600 text-white rounded-lg transition font-semibold text-sm sm:text-base">
                            Next
                            <svg class="w-5 h-5 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @else
                        @if($standalone)
                            <a href="{{ $exit_url }}"
                               class="px-4 py-2 sm:px-6 sm:py-3 bg-burgundy-500 hover:bg-burgundy-600 text-white rounded-lg transition font-semibold text-sm sm:text-base inline-block">
                                Done
                            </a>
                        @else
                            <button wire:click="complete"
                                    class="px-4 py-2 sm:px-6 sm:py-3 bg-burgundy-500 hover:bg-burgundy-600 text-white rounded-lg transition font-semibold text-sm sm:text-base">
                                Complete
                            </button>
                        @endif
                    @endif
                </div>

                <!-- Keyboard shortcuts hint -->
                <p class="text-center text-xs sm:text-sm text-gray-500 mt-4">
                    Use arrow keys to navigate
                </p>
            </div>
        @else
            <div class="bg-white rounded-lg border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] p-8 text-center max-w-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-2">
                    No Materials Available
                </h2>
                <p class="text-gray-600 mb-6">
                    This deck doesn't have any learning materials yet.
                </p>
                <a href="{{ $exit_url }}" class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold">
                    Return
                </a>
            </div>
        @endif
    </div>

    @once
        @push('styles')
            <style>
                .prose {
                    color: #1f2937;
                }
                .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
                    color: #561C24;
                    font-weight: 700;
                }
                .prose a {
                    color: #7c2d12;
                    decoration: underline;
                }
                .prose strong {
                    color: #1f2937;
                    font-weight: 600;
                }
                .prose ul, .prose ol {
                    padding-left: 1.5em;
                }
                .prose li {
                    margin-top: 0.5em;
                    margin-bottom: 0.5em;
                }
            </style>
        @endpush
    @endonce
</div>
