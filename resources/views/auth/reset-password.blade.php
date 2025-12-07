<x-layout :hideHeader="true" :hideFooter="true">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-neutral-50 to-neutral-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10">
                <!-- Logo and Header -->
                <div class="text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center space-x-3 group mb-8">
                        <x-logo class="w-12 h-12 fill-gold-500 group-hover:fill-gold-600 transition-colors" />
                        <span class="text-3xl font-heading font-bold text-neutral-950">VinoRecall</span>
                    </a>
                    <h2 class="mt-6 text-3xl font-bold text-neutral-950">
                        Create new password
                    </h2>
                    <p class="mt-2 text-sm text-neutral-600">
                        Enter your new password below
                    </p>
                </div>

                <!-- Form -->
                <form class="mt-8 space-y-6" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-neutral-700 mb-2">
                                Email address
                            </label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   autocomplete="email" 
                                   required
                                   readonly
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 bg-neutral-50 cursor-not-allowed @error('email') border-red-500 @enderror"
                                   value="{{ $email }}">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-neutral-700 mb-2">
                                New password
                            </label>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow @error('password') border-red-500 @enderror"
                                   placeholder="Enter new password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-xs text-neutral-500">
                                Must be at least 8 characters
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 mb-2">
                                Confirm new password
                            </label>
                            <input id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow"
                                   placeholder="Confirm new password">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-red-800 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Reset password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

