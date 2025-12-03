# Admin Authentication Separation

This document outlines the architectural change to separate admin and user authentication into distinct systems.

## Overview

The system now uses two separate authentication tables and guards:
- **Users**: Regular application users who study wine content
- **Admins**: Administrators who manage content, users, and decks

## Key Changes

### 1. Database Structure

**New Table: `admins`**
- Separate table for administrator accounts
- Fields: `id`, `name`, `email`, `password`, `email_verified_at`, `remember_token`, `timestamps`

**Updated Table: `users`**
- Removed `role` column
- Users are now purely for content consumption
- No admin privileges

### 2. Authentication Guards

**config/auth.php** now includes:
- `web` guard: For regular users (uses `users` table)
- `admin` guard: For administrators (uses `admins` table)

### 3. Admin Login

**Separate Login Portal:**
- Admin login: `/admin/login`
- User login: `/login`

**Controllers:**
- `App\Http\Controllers\Admin\Auth\LoginController`
- `App\Http\Controllers\Admin\Auth\LogoutController`

**View:**
- `resources/views/admin/auth/login.blade.php`

### 4. Admin Routes

All admin routes now use `auth:admin` middleware:

```php
Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('pages.admin.dashboard'))->name('dashboard');
    Route::get('/users', fn () => view('pages.admin.users'))->name('users');
    Route::get('/cards', fn () => view('pages.admin.cards'))->name('cards');
    Route::get('/decks', fn () => view('pages.admin.decks'))->name('decks');
    Route::get('/decks/import', fn () => view('pages.admin.deck-import'))->name('decks.import');
});
```

### 5. Domain Layer Updates

**New:**
- `domain/Admin/Models/Admin.php` - Admin model
- `domain/Admin/Data/AdminData.php` - Admin DTO
- `domain/Admin/Repositories/AdminRepository.php` - Admin repository
- `database/factories/AdminFactory.php` - Admin factory

**Updated:**
- `domain/User/Models/User.php` - Removed role field and isAdmin() method
- `domain/User/Data/UserData.php` - Removed role property
- `domain/User/Actions/UpdateUserAction.php` - Removed role parameter

### 6. Livewire Components

All admin Livewire components now use `AdminRepository` instead of `UserRepository`:
- `App\Livewire\Admin\Dashboard`
- `App\Livewire\Admin\DeckManagement`
- `App\Livewire\Admin\CardManagement`
- `App\Livewire\Admin\UserManagement`
- `App\Livewire\Admin\DeckImport`

### 7. Middleware

**Updated `EnsureUserIsAdmin`:**
```php
public function handle(Request $request, Closure $next): Response
{
    // Check if admin is authenticated via admin guard
    if (! auth()->guard('admin')->check()) {
        return redirect()->route('admin.login');
    }

    return $next($request);
}
```

### 8. Development Routes

**`routes/dev.php`** now includes two auto-login routes:
- `/dev/auto-login` - Login as first user
- `/dev/admin-auto-login` - Login as first admin

### 9. Seeders

**Updated `DatabaseSeeder`:**
- Creates admin account in `admins` table
- Creates regular user in `users` table
- Decks are created by admin (uses admin ID)

## Access Credentials

### Admin Access:
- URL: `/admin/login`
- Email: `admin@vinorecall.com`
- Password: `password`

### User Access:
- URL: `/login`
- Email: `test@example.com`
- Password: `password`

## Testing

The testing helpers have been updated:
- `actingAsUser()` - Returns a `User` model, uses `web` guard
- `actingAsAdmin()` - Returns an `Admin` model, uses `admin` guard

All tests need to be updated to:
1. Use the correct guard when acting as admin
2. Create `Admin` models instead of `User::factory()->admin()`
3. Remove references to `UserRole` enum

## Migration Path

1. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

2. Seed database:
```bash
./vendor/bin/sail artisan db:seed
```

3. Test admin login at `/admin/login`

## Security Implications

- **Improved Separation of Concerns**: Admins and users are completely isolated
- **Different Password Policies**: Can implement different security policies for each
- **Separate Session Management**: Admin and user sessions don't interfere
- **Audit Trail**: Easier to track admin actions separately from user actions

