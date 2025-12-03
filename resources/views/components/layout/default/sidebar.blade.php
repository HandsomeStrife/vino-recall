@props(['showSidebar' => true])

<aside class="w-64 bg-burgundy-900 text-white flex flex-col">
    <div class="p-6">
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center space-x-3">
            <x-logo class="w-10 h-10 fill-current" />
            <span class="text-2xl font-bold">VinoRecall</span>
        </a>
    </div>
    @auth
        <nav class="flex-1 px-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('dashboard') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.dashboard') }}
            </a>
            <a href="{{ route('study') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('study') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.study') }}
            </a>
            <a href="{{ route('library') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('library') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.library') }}
            </a>
            <a href="{{ route('profile') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('profile') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.profile') }}
            </a>
        </nav>
        <div class="p-4 border-t border-burgundy-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded hover:bg-burgundy-800">
                    {{ __('navigation.logout') }}
                </button>
            </form>
        </div>
    @else
        <nav class="flex-1 px-4 space-y-2">
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800">{{ __('navigation.home') }}</a>
            <a href="{{ route('login') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800">{{ __('auth.login') }}</a>
            <a href="{{ route('register') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800">{{ __('auth.register') }}</a>
        </nav>
    @endauth
</aside>

