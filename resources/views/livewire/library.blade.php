<div class="min-h-screen bg-cream-50">
    <!-- Hero Section -->
    <div class="relative bg-cover bg-center py-12" style="background-image: linear-gradient(rgba(78, 35, 48, 0.85), rgba(78, 35, 48, 0.85)), url('{{ asset('img/defaults/3.jpg') }}');">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
            <h1 class="text-4xl md:text-5xl font-bold font-heading mb-2">{{ __('library.title') }}</h1>
            <p class="text-lg md:text-xl text-white/90">{{ __('library.browse_decks') }}</p>
        </div>
    </div>

    @if($scrollToDeckId)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Use a longer timeout to ensure Livewire and the page are fully loaded
                setTimeout(() => {
                    const element = document.getElementById('deck-{{ $scrollToDeckId }}');
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Add highlight effect
                        element.classList.add('ring-4', 'ring-burgundy-500', 'ring-offset-4');
                        // Remove highlight after 2 seconds
                        setTimeout(() => {
                            element.classList.remove('ring-4', 'ring-burgundy-500', 'ring-offset-4');
                        }, 2000);
                    }
                }, 500);
            });
        </script>
    @endif

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Category Filter -->
        @if($categories->isNotEmpty())
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Filter by Category</h3>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="filterByCategory(null)" 
                            class="px-4 py-2 rounded-lg transition {{ $selectedCategoryId === null ? 'bg-burgundy-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                        All
                    </button>
                    @foreach($categories as $category)
                        <button wire:click="filterByCategory({{ $category->id }})" 
                                class="px-4 py-2 rounded-lg transition {{ $selectedCategoryId === $category->id ? 'bg-burgundy-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="mb-8">
            <div class="border-b-2 border-gray-200">
                <div class="flex gap-4">
                    <button 
                        wire:click="switchTab('enrolled')"
                        class="px-6 py-3 font-semibold transition-colors border-b-4 -mb-0.5 {{ $activeTab === 'enrolled' ? 'text-burgundy-600 border-burgundy-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
                        My Decks
                    </button>
                    <button 
                        wire:click="switchTab('browse')"
                        class="px-6 py-3 font-semibold transition-colors border-b-4 -mb-0.5 {{ $activeTab === 'browse' ? 'text-burgundy-600 border-burgundy-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
                        Browse
                    </button>
                </div>
            </div>
        </div>

        <!-- Enrolled Tab Content -->
        @if($activeTab === 'enrolled')
            @if($enrolledCollections->isEmpty() && $enrolledStandalone->isEmpty())
                <!-- Empty State -->
                <div class="bg-white p-12 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No Enrolled Decks Yet</h3>
                        <p class="text-gray-600 mb-6">You haven't enrolled in any decks yet. Browse the library to get started!</p>
                        <button 
                            wire:click="switchTab('browse')"
                            class="inline-block bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                            Browse Available Decks
                        </button>
                    </div>
                </div>
            @else
                <!-- Enrolled Collections -->
                @if($enrolledCollections->isNotEmpty())
                    <div class="mb-12">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-purple-100 rounded-lg p-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">My Collections</h2>
                            <span class="text-sm text-gray-500">({{ $enrolledCollections->count() }})</span>
                        </div>
                        
                        <div class="space-y-6">
                            @foreach($enrolledCollections as $deckStat)
                                @php
                                    $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                    $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                                @endphp
                                
                                <div class="bg-white rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
                                    <!-- Collection Header -->
                                    <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $deckColor }};">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-white/20 rounded-lg p-2">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                                                <span class="text-sm text-white/80">{{ $deckStat['childCount'] }} decks - {{ $deckStat['totalCards'] }} cards total</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Collection Description & Actions -->
                                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                @if($deckStat['deck']->description)
                                                    <p class="text-gray-600 text-sm mb-2">{{ $deckStat['deck']->description }}</p>
                                                @endif
                                                @if($deckStat['progress'] > 0)
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-1 max-w-xs bg-gray-200 rounded-full h-2">
                                                            <div class="bg-burgundy-500 h-2 rounded-full" style="width: {{ $deckStat['progress'] }}%"></div>
                                                        </div>
                                                        <span class="text-sm text-gray-600">{{ $deckStat['progress'] }}% complete</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4 flex gap-2">
                                                <a href="{{ route('collection.show', $deckStat['deck']->identifier) }}" 
                                                   class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                    View Collection
                                                </a>
                                                <button wire:click="unenrollFromDeck({{ $deckStat['deck']->id }})" 
                                                        class="inline-block bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Child Decks Grid -->
                                    @if($deckStat['childCount'] > 0)
                                        <div class="p-6">
                                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Included Decks</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach($deckStat['children'] as $childStat)
                                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-burgundy-300 transition">
                                                        <div class="flex items-start gap-3">
                                                            <div class="w-16 h-16 rounded-lg bg-cover bg-center shrink-0 border border-gray-200" 
                                                                 style="background-image: url('{{ $childStat['image'] }}');"></div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="flex items-center gap-2">
                                                                    <h5 class="font-semibold text-gray-900 truncate">{{ $childStat['deck']->name }}</h5>
                                                                    @if($childStat['hasMaterials'] ?? false)
                                                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-burgundy-100 text-burgundy-700 text-xs font-medium rounded" title="Includes learning materials">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.205 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.795 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.795 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.205 18 16.5 18s-3.332.477-4.5 1.253"></path>
                                                                            </svg>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <p class="text-sm text-gray-500">{{ $childStat['totalCards'] }} cards</p>
                                                                @if($childStat['progress'] > 0)
                                                                    <div class="mt-2 flex items-center gap-2">
                                                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                                                            <div class="bg-burgundy-500 h-1.5 rounded-full" style="width: {{ $childStat['progress'] }}%"></div>
                                                                        </div>
                                                                        <span class="text-xs text-gray-500">{{ $childStat['progress'] }}%</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Enrolled Standalone Decks -->
                @if($enrolledStandalone->isNotEmpty())
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-gray-100 rounded-lg p-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">My Decks</h2>
                            <span class="text-sm text-gray-500">({{ $enrolledStandalone->count() }})</span>
                        </div>
                        
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach($enrolledStandalone as $deckStat)
                                @php
                                    $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                    $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                                @endphp
                                
                                <div class="bg-white rounded-lg overflow-hidden relative border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
                                    <!-- Colored Title Banner -->
                                    <div class="px-6 py-3 flex items-center justify-between" style="background-color: {{ $deckColor }};">
                                        <div>
                                            <h3 class="text-lg font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                                            @if($deckStat['deck']->parent_name)
                                                <span class="text-xs text-white/80">From: {{ $deckStat['deck']->parent_name }}</span>
                                            @elseif($deckStat['deck']->categories && $deckStat['deck']->categories->isNotEmpty())
                                                <span class="text-xs text-white/80">{{ $deckStat['deck']->categories->first()->name }}</span>
                                            @endif
                                        </div>
                                        @if($deckStat['hasMaterials'] ?? false)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white/20 text-white text-xs font-medium rounded-full shrink-0" title="Includes learning materials">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.205 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.795 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.795 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.205 18 16.5 18s-3.332.477-4.5 1.253"></path>
                                                </svg>
                                                Materials
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex relative flex-1">
                                        <!-- Content Section -->
                                        <div class="flex-1 p-6 pr-64 flex flex-col">
                                            <div class="flex-1">
                                                @if($deckStat['deck']->description)
                                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $deckStat['deck']->description }}</p>
                                                @endif
                                                
                                                <div class="text-sm text-gray-500 mb-4">
                                                    <span class="font-semibold text-burgundy-900">{{ $deckStat['totalCards'] }}</span> {{ $deckStat['totalCards'] === 1 ? 'card' : 'cards' }}
                                                </div>
                                                
                                                @if($deckStat['reviewedCount'] > 0 && $deckStat['progress'] > 0)
                                                    <div class="text-sm text-gray-600 mb-4">{{ $deckStat['progress'] }}% complete</div>
                                                @endif
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-auto pt-4 flex flex-col gap-2">
                                                @if($deckStat['shortcode'])
                                                    @if($deckStat['reviewedCount'] === 0)
                                                        <!-- Start Now button for unstarted decks -->
                                                        <a href="{{ route('study', ['type' => 'normal', 'deck' => $deckStat['shortcode']]) }}" 
                                                           class="inline-flex items-center justify-center text-center bg-burgundy-500 text-white px-6 py-3 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                            </svg>
                                                            Start Now
                                                        </a>
                                                    @else
                                                        <!-- Standard buttons for started decks -->
                                                        <a href="{{ route('study', ['type' => 'normal', 'deck' => $deckStat['shortcode']]) }}" 
                                                           class="inline-block text-center bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                            Start Session
                                                        </a>
                                                        <a href="{{ route('deck.stats', $deckStat['shortcode']) }}" 
                                                           class="inline-block text-center bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                                                            View Stats
                                                        </a>
                                                        <button wire:click="unenrollFromDeck({{ $deckStat['deck']->id }})" 
                                                                class="inline-block text-center bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                                            Remove from Library
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Diagonal Image Section -->
                                        <div class="absolute top-0 right-0 bottom-0 w-56 overflow-hidden pointer-events-none">
                                            <div class="absolute inset-0 bg-cover bg-center" 
                                                 style="background-image: url('{{ $deckStat['image'] }}'); 
                                                        clip-path: polygon(20% 0, 100% 0, 100% 100%, 0 100%);">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif

        <!-- Browse Tab Content -->
        @if($activeTab === 'browse')
            <!-- Available Collections Section -->
            @if($availableCollections->isNotEmpty())
                <div class="mb-12">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-purple-100 rounded-lg p-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Collections</h2>
                        <span class="text-sm text-gray-500">({{ $availableCollections->count() }})</span>
                    </div>
                    
                    <div class="space-y-6">
                        @foreach($availableCollections as $deckStat)
                            @php
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                            @endphp
                            
                            <div id="deck-{{ $deckStat['deck']->id }}" class="bg-white rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden scroll-mt-8 transition-all duration-300">
                                <!-- Collection Header -->
                                <div class="px-6 py-4 flex items-center justify-between" style="background-color: {{ $deckColor }};">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-white/20 rounded-lg p-2">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">{{ $deckStat['deck']->name }}</h3>
                                            <span class="text-sm text-white/80">{{ $deckStat['childCount'] }} decks - {{ $deckStat['totalCards'] }} cards total</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Collection Description & Actions -->
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            @if($deckStat['deck']->description)
                                                <p class="text-gray-600 text-sm mb-2">{{ $deckStat['deck']->description }}</p>
                                            @endif
                                        </div>
                                        <div class="ml-4 flex gap-2">
                                            <a href="{{ route('collection.show', $deckStat['deck']->identifier) }}" 
                                               class="inline-block bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                                                View Collection
                                            </a>
                                            <button wire:click="enrollInDeck({{ $deckStat['deck']->id }})" 
                                                    class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                                Add Collection to Library
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Child Decks Grid -->
                                @if($deckStat['childCount'] > 0)
                                    <div class="p-6">
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Included Decks</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($deckStat['children'] as $childStat)
                                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-16 h-16 rounded-lg bg-cover bg-center flex-shrink-0 border border-gray-200" 
                                                             style="background-image: url('{{ $childStat['image'] }}');"></div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <h5 class="font-semibold text-gray-900 truncate">{{ $childStat['deck']->name }}</h5>
                                                                @if($childStat['hasMaterials'] ?? false)
                                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-burgundy-100 text-burgundy-700 text-xs font-medium rounded" title="Includes learning materials">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.205 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.795 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.795 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.205 18 16.5 18s-3.332.477-4.5 1.253"></path>
                                                                        </svg>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <p class="text-sm text-gray-500">{{ $childStat['totalCards'] }} cards</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="p-6 text-center text-gray-500">
                                        <p>No decks in this collection yet.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Available Standalone Decks Section -->
            @if($availableStandalone->isNotEmpty())
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gray-100 rounded-lg p-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Standalone Decks</h2>
                        <span class="text-sm text-gray-500">({{ $availableStandalone->count() }})</span>
                    </div>
                    
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        @foreach($availableStandalone as $deckStat)
                            @php
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $deckColor = $colors[$deckStat['deck']->id % count($colors)];
                                $image = \Domain\Deck\Helpers\DeckImageHelper::getImagePath($deckStat['deck']);
                            @endphp
                            
                            <x-library-card 
                                :deck="$deckStat['deck']"
                                :deckColor="$deckColor"
                                :image="$image"
                                :totalCards="$deckStat['totalCards']"
                                :description="$deckStat['deck']->description"
                                :isEnrolled="false"
                                :progress="0"
                                :hasMaterials="$deckStat['hasMaterials'] ?? false"
                            >
                                <x-slot:bottomAction>
                                    <button wire:click="enrollInDeck({{ $deckStat['deck']->id }})" 
                                            class="inline-block bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition font-semibold shadow hover:shadow-md">
                                        Add to My Library
                                    </button>
                                </x-slot:bottomAction>
                            </x-library-card>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Empty State -->
            @if($availableCollections->isEmpty() && $availableStandalone->isEmpty())
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    @if($selectedCategoryId !== null)
                        <p class="text-gray-600">No decks found in this category.</p>
                        <button wire:click="filterByCategory(null)" class="mt-4 text-burgundy-500 hover:text-burgundy-600 font-medium">
                            Clear filter
                        </button>
                    @else
                        <p class="text-gray-600">{{ __('library.no_decks') }}</p>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
