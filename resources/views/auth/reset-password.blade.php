<x-layout>
    <div class="min-h-screen flex items-center justify-center bg-cream-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-burgundy-900">
                    Reset Password
                </h2>
            </div>
            <form class="mt-8 space-y-6" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="space-y-4">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Email address" value="{{ $email }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="New Password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-burgundy-500 focus:border-burgundy-500 focus:z-10 sm:text-sm"
                            placeholder="Confirm New Password">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-burgundy-500 hover:bg-burgundy-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-burgundy-500">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>

