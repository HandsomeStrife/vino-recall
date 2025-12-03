<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Rate limiting: 3 attempts per minute per IP + email combination (stricter for admin)
        $key = 'admin.login:' . $request->ip() . ':' . strtolower($request->input('email', ''));
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            return redirect()->intended(route('admin.dashboard'));
        }

        // Increment rate limiter on failed login
        RateLimiter::hit($key, 60);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }
}

