<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">Profile & Settings</h1>

        @if(session('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif

        @if(session('password_message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('password_message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Profile Information</h2>
                <form wire:submit.prevent="updateProfile">
                    <div class="space-y-4">
                        <div>
                            <x-form.label>Name</x-form.label>
                            <x-form.input name="name" wire:model="name" />
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-form.label>Email</x-form.label>
                            <x-form.input type="email" name="email" wire:model="email" />
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-button.button type="submit">Update Profile</x-button.button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Change Password</h2>
                <form wire:submit.prevent="updatePassword">
                    <div class="space-y-4">
                        <div>
                            <x-form.label>Current Password</x-form.label>
                            <x-form.input type="password" name="current_password" wire:model="current_password" />
                            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-form.label>New Password</x-form.label>
                            <x-form.input type="password" name="password" wire:model="password" />
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-form.label>Confirm New Password</x-form.label>
                            <x-form.input type="password" name="password_confirmation" wire:model="password_confirmation" />
                        </div>
                        <div>
                            <x-button.button type="submit">Update Password</x-button.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subscription -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-burgundy-900 mb-4">Subscription</h2>
            @if($subscription && $plan)
                <div class="space-y-2">
                    <p><strong>Current Plan:</strong> {{ $plan->name }}</p>
                    <p><strong>Status:</strong> 
                        <x-badge.badge :variant="$subscription->status === 'active' ? 'success' : 'default'">
                            {{ ucfirst($subscription->status) }}
                        </x-badge.badge>
                    </p>
                    @if($subscription->current_period_end)
                        <p><strong>Renews:</strong> {{ \Carbon\Carbon::parse($subscription->current_period_end)->format('M d, Y') }}</p>
                    @endif
                </div>
            @else
                <p class="text-gray-600 mb-4">You don't have an active subscription.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $planOption)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-xl font-bold mb-2">{{ $planOption->name }}</h3>
                            <p class="text-2xl font-bold text-burgundy-500 mb-2">${{ number_format((float)$planOption->price, 2) }}</p>
                            @if($planOption->features)
                                <p class="text-sm text-gray-600 mb-4">{{ $planOption->features }}</p>
                            @endif
                            <x-button.button>Subscribe</x-button.button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

