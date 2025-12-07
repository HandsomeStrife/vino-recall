<div>
    <!-- Hero Section with Background -->
    <div class="relative bg-cover bg-center" style="background-image: linear-gradient(rgba(78, 35, 48, 0.85), rgba(78, 35, 48, 0.85)), url('{{ asset('img/defaults/5.jpg') }}');">
        <div class="max-w-6xl mx-auto px-4 py-12">
            <!-- Page Title -->
            <div class="text-center text-white mb-8">
                <h1 class="text-5xl font-bold mb-2">Your Enrolled Courses</h1>
                @if($totalDueCards > 0)
                    <p class="text-xl text-cream-200">{{ $totalDueCards }} card{{ $totalDueCards !== 1 ? 's' : '' }} due for review</p>
                @else
                    <p class="text-xl text-cream-200">All caught up! Great work.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if($hasEnrolledDecks)
            <!-- Collections Section -->
            @if($collectionsWithStats->isNotEmpty())
                <section class="mb-12">
                    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Collections</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($collectionsWithStats as $item)
                            @php
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $firstCategory = ($item['deck']->categories && $item['deck']->categories->isNotEmpty()) 
                                    ? $item['deck']->categories->first()->name 
                                    : null;
                                $collectionColor = $firstCategory ? $colors[crc32($firstCategory) % count($colors)] : $colors[$item['deck']->id % count($colors)];
                            @endphp
                            <x-collection-card 
                                :collection="$item['deck']"
                                :collectionColor="$collectionColor"
                                :image="$item['image']"
                                :dueCards="$item['dueCards']"
                                :newCards="$item['newCards']"
                                :totalCards="$item['totalCards']"
                                :childCount="$item['childCount']"
                                :progress="$item['progress']"
                            />
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Standalone Decks Section -->
            @if($standaloneWithStats->isNotEmpty())
                <section class="mb-12">
                    <h2 class="text-2xl font-bold text-burgundy-900 mb-6">Standalone Decks</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($standaloneWithStats as $item)
                            @php
                                $colors = ['#9E3B4D', '#2D3F2F', '#7A2B3A', '#8D3442', '#4A5D4E'];
                                $firstCategory = ($item['deck']->categories && $item['deck']->categories->isNotEmpty()) 
                                    ? $item['deck']->categories->first()->name 
                                    : null;
                                $deckColor = $firstCategory ? $colors[crc32($firstCategory) % count($colors)] : $colors[$item['deck']->id % count($colors)];
                            @endphp
                            <x-deck-card 
                                :deck="$item['deck']"
                                :deckColor="$deckColor"
                                :image="$item['image']"
                                :dueCards="$item['dueCards']"
                                :newCards="$item['newCards']"
                                :reviewedCount="$item['reviewedCount']"
                                :retentionRate="$item['retentionRate']"
                            />
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Browse More Link -->
            <div class="text-center mt-8">
                <a href="{{ route('library') }}" class="inline-flex items-center text-burgundy-600 hover:text-burgundy-800 font-semibold transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Browse more courses in the Library
                </a>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg p-12 text-center max-w-2xl mx-auto">
                <svg class="h-20 w-20 text-burgundy-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h2 class="text-3xl font-bold text-burgundy-900 mb-4">Your wine journey awaits!</h2>
                <p class="text-gray-600 mb-8 text-lg">You haven't enrolled in any decks yet. Let's get you started on your path to becoming a wine expert.</p>
                <a href="{{ route('library') }}" class="inline-flex items-center px-8 py-4 bg-burgundy-500 text-white rounded-lg hover:bg-burgundy-600 transition font-semibold text-lg shadow-lg hover:shadow-xl">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Browse Library
                </a>
            </div>
        @endif
    </div>
</div>

