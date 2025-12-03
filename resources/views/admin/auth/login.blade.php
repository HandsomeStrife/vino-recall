<x-layout.default>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-burgundy-900 via-burgundy-800 to-forest-green-900 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-2xl">
            <div>
                <div class="flex justify-center">
                    <x-logo class="w-16 h-16 fill-burgundy-500" />
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-burgundy-900">
                    Admin Login
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Administrator access only
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('email') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-burgundy-600 focus:ring-burgundy-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-burgundy-600 hover:bg-burgundy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500 transition"
                    >
                        Sign in to Admin Panel
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('home') }}" class="text-sm text-burgundy-600 hover:text-burgundy-500">
                        ← Back to Home
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout.default>

