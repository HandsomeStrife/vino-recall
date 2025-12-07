<x-layout :hideHeader="true" :hideFooter="true">
    <div class="min-h-screen flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-20 xl:px-24 bg-white">
            <div class="w-full max-w-md space-y-8">
                <!-- Logo and Header -->
                <div class="text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center space-x-3 group mb-8">
                        <x-logo class="w-12 h-12 fill-gold-500 group-hover:fill-gold-600 transition-colors" />
                        <span class="text-3xl font-heading font-bold text-neutral-950">VinoRecall</span>
                    </a>
                    <h2 class="mt-6 text-3xl font-bold text-neutral-950">
                        Start your journey
                    </h2>
                    <p class="mt-2 text-sm text-neutral-600">
                        Create your account and master wine education
                    </p>
                </div>

                <!-- Form -->
                <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="space-y-5">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-700 mb-2">
                                Full name
                            </label>
                            <input id="name" 
                                   name="name" 
                                   type="text" 
                                   autocomplete="name" 
                                   required
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow @error('name') border-red-500 @enderror"
                                   placeholder="John Doe" 
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

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
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow @error('email') border-red-500 @enderror"
                                   placeholder="your.email@example.com" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-neutral-700 mb-2">
                                Password
                            </label>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow @error('password') border-red-500 @enderror"
                                   placeholder="Create a strong password">
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
                                Confirm password
                            </label>
                            <input id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="appearance-none block w-full px-4 py-3 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-shadow"
                                   placeholder="Confirm your password">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-red-800 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Create account
                        </button>
                    </div>

                    <!-- Terms Notice -->
                    <p class="text-xs text-center text-neutral-500">
                        By creating an account, you agree to our Terms of Service and Privacy Policy
                    </p>

                    <!-- Sign In Link -->
                    <div class="text-center">
                        <p class="text-sm text-neutral-600">
                            Already have an account?
                            <a href="{{ route('login') }}" 
                               class="font-medium text-gold-600 hover:text-gold-500 transition-colors">
                                Sign in
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image/Hero -->
        <div class="hidden lg:block lg:flex-1 relative overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" 
                 style="background-image: url('{{ asset('img/defaults/3.jpg') }}');">
                <div class="absolute inset-0 bg-gradient-to-br from-neutral-950/80 via-neutral-950/70 to-neutral-900/60"></div>
            </div>
            <div class="relative h-full flex flex-col justify-center px-12 xl:px-16 text-white">
                <h2 class="text-4xl xl:text-5xl font-bold mb-6 leading-tight">
                    Your path to<br>wine expertise<br>starts here
                </h2>
                <p class="text-lg xl:text-xl text-neutral-200 mb-8 leading-relaxed max-w-md">
                    Start your WSET Level 1 and Level 2 journey with intelligent spaced repetition flashcards.
                </p>
                <div class="space-y-4 max-w-md">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-gold-500 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-neutral-200">Comprehensive coverage of WSET syllabus</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-gold-500 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-neutral-200">Study at your own pace, anywhere, anytime</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-gold-500 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-neutral-200">Proven spaced repetition methodology</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

