@props(['showNavigation' => true])

@if($showNavigation)
<header class="bg-burgundy-900 text-white shadow-lg sticky top-0 z-50">
    <nav class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-8">
                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center space-x-3">
                    <x-logo class="w-8 h-8 fill-current" />
                    <span class="text-xl font-bold">VinoRecall</span>
                </a>

                @auth
                <!-- Desktop Navigation -->
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('dashboard') }}" 
                       class="px-3 py-2 rounded hover:bg-burgundy-800 transition {{ request()->routeIs('dashboard') ? 'bg-burgundy-800' : '' }}">
                        {{ __('navigation.dashboard') }}
                    </a>
                    <a href="{{ route('library') }}" 
                       class="px-3 py-2 rounded hover:bg-burgundy-800 transition {{ request()->routeIs('library') ? 'bg-burgundy-800' : '' }}">
                        {{ __('navigation.library') }}
                    </a>
                    <a href="{{ route('profile') }}" 
                       class="px-3 py-2 rounded hover:bg-burgundy-800 transition {{ request()->routeIs('profile') ? 'bg-burgundy-800' : '' }}">
                        {{ __('navigation.profile') }}
                    </a>
                </div>
                @endauth
            </div>

            @auth
            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-3" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-burgundy-800 transition">
                        <div class="w-8 h-8 rounded-full bg-burgundy-700 flex items-center justify-center">
                            <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="hidden lg:block">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-4 top-14 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
                         style="display: none;">
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                            {{ __('navigation.profile') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100">
                                {{ __('navigation.logout') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button x-data="{ mobileOpen: false }" 
                        @click="mobileOpen = !mobileOpen; $dispatch('mobile-menu-toggle', { open: mobileOpen })"
                        class="md:hidden p-2 rounded hover:bg-burgundy-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            @else
            <!-- Guest Links -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('login') }}" class="px-4 py-2 rounded hover:bg-burgundy-800 transition">
                    {{ __('auth.login') }}
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 bg-cream-500 text-burgundy-900 rounded hover:bg-cream-600 transition font-semibold">
                    {{ __('auth.register') }}
                </a>
            </div>
            @endauth
        </div>

        @auth
        <!-- Mobile Navigation -->
        <div x-data="{ mobileOpen: false }" 
             @mobile-menu-toggle.window="mobileOpen = $event.detail.open"
             x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden py-4 border-t border-burgundy-800"
             style="display: none;">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('dashboard') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.dashboard') }}
            </a>
            <a href="{{ route('library') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('library') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.library') }}
            </a>
            <a href="{{ route('profile') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('profile') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2 pt-2 border-t border-burgundy-800">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded hover:bg-burgundy-800">
                    {{ __('navigation.logout') }}
                </button>
            </form>
        </div>
        @endauth
    </nav>
</header>
@endif

