<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-cream-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-burgundy-900">
                    Create your account
                </h2>
            </div>
            <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="name" class="sr-only">Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Name" value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Email address" value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Confirm Password">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-burgundy-500 hover:bg-burgundy-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500">
                        Sign up
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-burgundy-600 hover:text-burgundy-500">
                        Already have an account? Sign in
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>

