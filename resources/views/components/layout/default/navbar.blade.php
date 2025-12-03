@props(['showNavbar' => true])

<nav class="bg-neutral-950 border-b border-neutral-900 shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center space-x-2 group">
                    <x-logo class="w-8 h-8 sm:w-10 sm:h-10 fill-gold-500 group-hover:fill-gold-400 transition-colors" />
                    <span class="text-xl sm:text-2xl font-heading font-bold text-white hidden sm:block">VinoRecall</span>
                </a>
            </div>

            @auth
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-white font-semibold border-b-2 border-gold-500' : 'text-neutral-300 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        {{ __('navigation.dashboard') }}
                    </a>
                    <a href="{{ route('study') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('study') ? 'text-white font-semibold border-b-2 border-gold-500' : 'text-neutral-300 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        {{ __('navigation.study') }}
                    </a>
                    <a href="{{ route('library') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('library') ? 'text-white font-semibold border-b-2 border-gold-500' : 'text-neutral-300 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        {{ __('navigation.library') }}
                    </a>
                    <a href="{{ route('profile') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('profile') ? 'text-white font-semibold border-b-2 border-gold-500' : 'text-neutral-300 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('navigation.profile') }}
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-white hover:text-gold-400 focus:outline-none p-2">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Logout Button -->
                <div class="hidden md:block">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-neutral-300 hover:text-white transition-colors">
                            {{ __('navigation.logout') }}
                        </button>
                    </form>
                </div>
            @else
                <!-- Guest Navigation -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-neutral-300 hover:text-white transition-colors text-sm font-medium">
                        {{ __('auth.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="bg-gold-500 text-neutral-950 px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gold-400 transition-colors shadow-md">
                        {{ __('auth.register') }}
                    </a>
                </div>
            @endauth
        </div>

        <!-- Mobile Menu -->
        @auth
            <div x-show="mobileMenuOpen"
                 @click.away="mobileMenuOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-1"
                 class="md:hidden border-t border-neutral-800 bg-neutral-900"
                 style="display: none;">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-neutral-800 text-white font-semibold border-l-4 border-gold-500' : 'text-neutral-300 hover:bg-neutral-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        {{ __('navigation.dashboard') }}
                    </a>
                    <a href="{{ route('study') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('study') ? 'bg-neutral-800 text-white font-semibold border-l-4 border-gold-500' : 'text-neutral-300 hover:bg-neutral-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        {{ __('navigation.study') }}
                    </a>
                    <a href="{{ route('library') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('library') ? 'bg-neutral-800 text-white font-semibold border-l-4 border-gold-500' : 'text-neutral-300 hover:bg-neutral-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        {{ __('navigation.library') }}
                    </a>
                    <a href="{{ route('profile') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('profile') ? 'bg-neutral-800 text-white font-semibold border-l-4 border-gold-500' : 'text-neutral-300 hover:bg-neutral-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('navigation.profile') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-lg text-base font-medium text-neutral-300 hover:bg-neutral-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            {{ __('navigation.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
