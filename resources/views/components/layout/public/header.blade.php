<header class="bg-burgundy-900 text-cream-100">
    <nav class="flex justify-between items-center container mx-auto px-6 py-4">
        <a href="{{ route('home') }}" class="flex items-center space-x-3">
            <x-logo class="w-10 h-10 fill-current" />
            <span class="text-2xl font-bold font-heading">VinoRecall</span>
        </a>
        <div class="space-x-4">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-burgundy-500 px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="hover:text-burgundy-200">Log In</a>
                <a href="{{ route('register') }}" class="bg-burgundy-500 px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">Sign Up</a>
            @endauth
        </div>
    </nav>
</header>

