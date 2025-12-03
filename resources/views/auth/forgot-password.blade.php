<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-cream-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-burgundy-900">
                    Reset Password
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>
            <form class="mt-8 space-y-6" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                        placeholder="Email address" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('status') }}
                    </div>
                @endif

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-burgundy-500 hover:bg-burgundy-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500">
                        Send Password Reset Link
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-burgundy-600 hover:text-burgundy-500">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>

