<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                <p class="text-3xl font-bold text-burgundy-500">{{ $userCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Total Decks</h3>
                <p class="text-3xl font-bold text-burgundy-500">{{ $deckCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">Total Cards</h3>
                <p class="text-3xl font-bold text-burgundy-500">{{ $cardCount }}</p>
            </div>
        </div>
    </div>

