<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Stripe webhook (must be outside middleware groups)
Route::post('/webhook/stripe', [WebhookController::class, 'handleStripeWebhook'])->name('webhook.stripe');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Admin authentication routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'show'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Admin\Auth\LogoutController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');
    Route::get('/study', fn () => view('pages.study'))->name('study');
    Route::get('/library', fn () => view('pages.library'))->name('library');
    Route::get('/profile', fn () => view('pages.profile'))->name('profile');
    Route::get('/subscription', fn () => view('pages.subscription'))->name('subscription');
});

Route::middleware(['auth:admin', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('pages.admin.dashboard'))->name('dashboard');
    Route::get('/users', fn () => view('pages.admin.users'))->name('users');
    Route::get('/cards', fn () => view('pages.admin.cards'))->name('cards');
    Route::get('/decks', fn () => view('pages.admin.decks'))->name('decks');
    Route::get('/import', fn () => view('pages.admin.deck-import'))->name('deck-import');
});

if (app()->environment('local')) {
    Route::get('/dev/auto-login', function () {
        $user = \Domain\User\Models\User::first();
        if ($user) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('dashboard');
        }
        return redirect()->route('home');
    })->name('dev.auto-login');
}
