<div class="p-8">
        <h1 class="text-3xl font-bold text-white mb-6">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700">
                <h3 class="text-lg font-semibold mb-2 text-gray-300">Total Users</h3>
                <p class="text-3xl font-bold text-burgundy-400">{{ $userCount }}</p>
            </div>
            <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700">
                <h3 class="text-lg font-semibold mb-2 text-gray-300">Total Decks</h3>
                <p class="text-3xl font-bold text-burgundy-400">{{ $deckCount }}</p>
            </div>
            <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700">
                <h3 class="text-lg font-semibold mb-2 text-gray-300">Total Cards</h3>
                <p class="text-3xl font-bold text-burgundy-400">{{ $cardCount }}</p>
            </div>
        </div>
    </div>

