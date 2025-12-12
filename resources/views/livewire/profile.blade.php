<div>
    <!-- Hero Section -->
    <div class="relative h-48 md:h-64 overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('img/defaults/7.jpg') }}" alt="Profile background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-burgundy-900/70 to-burgundy-900/90"></div>
        </div>
        <div class="relative h-full flex items-center justify-center">
            <div class="text-center text-white">
                <div class="w-20 h-20 bg-burgundy-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold shadow-lg">
                    {{ strtoupper(substr($name, 0, 1)) }}
                </div>
                <h1 class="text-3xl md:text-4xl font-bold font-heading">{{ $name }}</h1>
                <p class="text-cream-200 mt-1">{{ $email }}</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 py-8 -mt-8 relative z-10">
        <!-- Success Messages -->
        @if(session('message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('message') }}
            </div>
        @endif

        @if(session('password_message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('password_message') }}
            </div>
        @endif

        <!-- Subscription Section -->
        @if($subscription)
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-burgundy-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-neutral-900">Subscription</h2>
                    </div>
                    <x-badge :variant="$subscription->status === 'active' ? 'success' : ($subscription->status === 'past_due' ? 'warning' : 'danger')">
                        {{ ucfirst($subscription->status) }}
                    </x-badge>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-neutral-600">Current Plan</p>
                        <p class="text-lg font-semibold text-neutral-900">{{ $subscription->plan->name }}</p>
                    </div>
                    @if($subscription->current_period_end)
                        <div>
                            <p class="text-sm text-neutral-600">
                                @if($subscription->status === 'active')
                                    Renews:
                                @else
                                    Expires:
                                @endif
                            </p>
                            <p class="text-lg font-semibold text-neutral-900">
                                {{ $subscription->current_period_end->format('F j, Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-burgundy-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-neutral-900">Subscription</h2>
                </div>
                <p class="text-neutral-600 mb-6">You don't currently have an active subscription. Choose a plan below to get started:</p>
                <div class="space-y-4">
                    @foreach($availablePlans as $plan)
                        <div class="border border-neutral-200 rounded-lg p-4 hover:border-burgundy-300 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-neutral-900">{{ $plan->name }}</h3>
                                    @if($plan->features)
                                        <p class="text-sm text-neutral-600">{{ $plan->features }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-burgundy-600">${{ number_format((float)$plan->price, 2) }}</p>
                                    <p class="text-xs text-neutral-500">per month</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-burgundy-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-neutral-900">Profile Information</h2>
                </div>

                <form wire:submit.prevent="updateProfile">
                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-700 mb-2">
                                Full Name
                            </label>
                            <input id="name" 
                                   type="text" 
                                   wire:model="name"
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-burgundy-500 focus:border-transparent transition-shadow @error('name') border-red-500 @enderror"
                                   placeholder="Your name">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-neutral-700 mb-2">
                                Email Address
                            </label>
                            <input id="email" 
                                   type="email" 
                                   wire:model="email"
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-burgundy-500 focus:border-transparent transition-shadow @error('email') border-red-500 @enderror"
                                   placeholder="your.email@example.com">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-burgundy-600 hover:bg-burgundy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500 transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-burgundy-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-burgundy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-neutral-900">Change Password</h2>
                </div>

                <form wire:submit.prevent="updatePassword">
                    <div class="space-y-5">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-neutral-700 mb-2">
                                Current Password
                            </label>
                            <input id="current_password" 
                                   type="password" 
                                   wire:model="current_password"
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-burgundy-500 focus:border-transparent transition-shadow @error('current_password') border-red-500 @enderror"
                                   placeholder="Enter current password">
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-neutral-700 mb-2">
                                New Password
                            </label>
                            <input id="password" 
                                   type="password" 
                                   wire:model="password"
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-burgundy-500 focus:border-transparent transition-shadow @error('password') border-red-500 @enderror"
                                   placeholder="Enter new password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-xs text-neutral-500">Must be at least 8 characters</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 mb-2">
                                Confirm New Password
                            </label>
                            <input id="password_confirmation" 
                                   type="password" 
                                   wire:model="password_confirmation"
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-burgundy-500 focus:border-transparent transition-shadow"
                                   placeholder="Confirm new password">
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-burgundy-600 hover:bg-burgundy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500 transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
