<?php

use Domain\Admin\Models\Admin;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    // Auto-login as user
    Route::get('/dev/auto-login', function () {
        $user = User::first();
        if ($user) {
            Auth::login($user);

            return redirect()->route('dashboard');
        }

        return redirect()->route('home');
    })->name('dev.auto-login');

    // Auto-login as admin
    Route::get('/dev/admin-auto-login', function () {
        $admin = Admin::first();
        if ($admin) {
            Auth::guard('admin')->login($admin);

            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    })->name('dev.admin-auto-login');
}
