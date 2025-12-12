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

Route::get('/about', fn () => view('pages.about'))->name('about');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');
Route::get('/privacy', fn () => view('pages.privacy'))->name('privacy');

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

    Route::middleware(\App\Http\Middleware\AdminAuthenticate::class)->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Admin\Auth\LogoutController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');
    Route::get('/study/{type}/{deck}', fn (string $type, string $deck) => view('pages.study', ['type' => $type, 'deck' => $deck]))->name('study')->where(['type' => 'normal|deep_study|practice', 'deck' => '[A-Za-z0-9]{8}']);
    Route::get('/library/{identifier?}', fn (?string $identifier = null) => view('pages.library', ['identifier' => $identifier]))->name('library');
    Route::get('/profile', fn () => view('pages.profile'))->name('profile');
    Route::get('/subscription', fn () => view('pages.subscription'))->name('subscription');
    Route::get('/deck/{shortcode}/stats', fn (string $shortcode) => view('pages.deck-stats', ['shortcode' => $shortcode]))->name('deck.stats');
    Route::get('/deck/{shortcode}/materials', fn (string $shortcode) => view('pages.deck-materials', ['shortcode' => $shortcode]))->name('deck.materials');
    Route::get('/collection/{identifier}', fn (string $identifier) => view('pages.collection', ['identifier' => $identifier]))->name('collection.show');
});

Route::middleware(\App\Http\Middleware\AdminAuthenticate::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('pages.admin.dashboard'))->name('dashboard');
    Route::get('/users', fn () => view('pages.admin.users'))->name('users');
    Route::get('/decks', fn () => view('pages.admin.decks'))->name('decks');
    Route::get('/decks/{deckId}/cards', fn (int $deckId) => view('pages.admin.deck-cards', ['deckId' => $deckId]))->name('decks.cards');
    Route::get('/decks/{deckId}/materials', fn (int $deckId) => view('pages.admin.deck-materials', ['deck_id' => $deckId]))->name('decks.materials');
    Route::get('/categories', fn () => view('pages.admin.categories'))->name('categories');
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

    Route::get('/dev/admin-auto-login', function () {
        $admin = \Domain\Admin\Models\Admin::first();
        if ($admin) {
            \Illuminate\Support\Facades\Auth::guard('admin')->login($admin);

            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    })->name('dev.admin-auto-login');
}
