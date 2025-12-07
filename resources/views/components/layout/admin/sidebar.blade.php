@props(['showSidebar' => true])

<aside class="w-64 bg-burgundy-900 text-white flex flex-col">
    <div class="p-4 border-b border-burgundy-800">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center">
            <x-logo class="w-10 h-10 fill-current" />
        </a>
    </div>
    @auth('admin')
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('admin.dashboard') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.dashboard') }}
            </a>
            <a href="{{ route('admin.users') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('admin.users') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.users') }}
            </a>
            <a href="{{ route('admin.decks') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('admin.decks') || request()->routeIs('admin.decks.cards') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.decks') }}
            </a>
            <a href="{{ route('admin.categories') }}" class="block px-4 py-2 rounded hover:bg-burgundy-800 {{ request()->routeIs('admin.categories') ? 'bg-burgundy-800' : '' }}">
                {{ __('navigation.categories') }}
            </a>
        </nav>
        <div class="p-4 border-t border-burgundy-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded hover:bg-burgundy-800">
                    {{ __('navigation.logout') }}
                </button>
            </form>
        </div>
    @endauth
</aside>
