<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Rate limiting: 5 attempts per minute per IP + email combination
        $key = 'login:' . $request->ip() . ':' . strtolower($request->input('email', ''));
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            return redirect()->intended('/dashboard');
        }

        // Increment rate limiter on failed login
        RateLimiter::hit($key, 60);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }
}
