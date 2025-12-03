<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Domain\User\Actions\RegisterUserAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request, RegisterUserAction $registerUserAction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $userData = $registerUserAction->execute(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password']
        );

        $user = \Domain\User\Models\User::findOrFail($userData->id);
        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/dashboard');
    }
}
