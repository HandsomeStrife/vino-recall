<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Subscription Management</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">{{ $plan->name }}</h3>
                    <p class="text-3xl font-bold text-burgundy-500 mb-4">${{ number_format((float)$plan->price, 2) }}</p>
                    @if($plan->features)
                        <p class="text-gray-600 mb-4">{{ $plan->features }}</p>
                    @endif
                    <x-button.button>Subscribe</x-button.button>
                </div>
            @endforeach
        </div>
        @if($subscription)
            <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Current Subscription</h2>
                <p>Status: <x-badge.badge>{{ $subscription->status }}</x-badge.badge></p>
            </div>
        @endif
    </div>

