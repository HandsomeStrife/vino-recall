# Stripe Subscription Implementation

## Overview

Complete Stripe webhook handling, content access control, and daily card limits have been implemented for the VinoRecall subscription system.

---

## Features Implemented

### 1. Stripe Webhook Handling ✅

**File**: `app/Http/Controllers/WebhookController.php`

**Supported Events**:
- `checkout.session.completed` - Creates subscription when user completes checkout
- `customer.subscription.created` - Logs subscription creation
- `customer.subscription.updated` - Updates subscription status/period
- `customer.subscription.deleted` - Cancels subscription
- `invoice.payment_succeeded` - Updates status to active on successful payment
- `invoice.payment_failed` - Updates status to past_due on failed payment

**Security**:
- Webhook signature verification using Stripe's SDK
- Invalid signature returns 400 error
- Comprehensive error logging

**Route**: `POST /webhook/stripe` (no CSRF protection, verified by Stripe signature)

---

### 2. Content Access Control ✅

**File**: `app/Http/Middleware/CheckSubscription.php`

**Features**:
- Checks if user has active subscription
- Validates subscription status is 'active'
- Verifies user's plan includes required access level
- Redirects to subscription page with error message if access denied

**Usage**:
```php
// In routes/web.php
Route::middleware(['auth', 'subscription:Premium'])->group(function () {
    // Premium-only routes
});

Route::middleware(['auth', 'subscription:Basic,Premium'])->group(function () {
    // Basic or Premium routes
});
```

**Plan Hierarchy**:
- Free tier: No subscription required (default access)
- Basic: Access to WSET Level 1 content
- Premium: Access to WSET Level 1 & 2 content

---

### 3. Daily Card Limits ✅

**File**: `app/Http/Middleware/CheckDailyCardLimit.php`

**Limits by Tier**:
- **Free**: 10 cards per day
- **Basic**: 50 cards per day
- **Premium**: Unlimited cards

**Features**:
- Counts reviews created today
- Blocks access to study page when limit reached
- Redirects to dashboard with upgrade message
- Resets daily at midnight (based on `created_at` date)

**Usage**:
```php
// Apply to study routes
Route::middleware(['auth', 'card.limit'])->group(function () {
    Route::get('/study', ...);
});
```

---

## Configuration

### Environment Variables

Add to `.env`:
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### Config File

**File**: `config/stripe.php`
```php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
```

---

## Middleware Registration

**File**: `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        'subscription' => \App\Http\Middleware\CheckSubscription::class,
        'card.limit' => \App\Http\Middleware\CheckDailyCardLimit::class,
    ]);
})
```

---

## Database Schema

### Subscriptions Table

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('plan_id')->constrained()->onDelete('cascade');
    $table->string('stripe_subscription_id')->unique();
    $table->string('status'); // active, inactive, past_due, canceled, trialing
    $table->timestamp('current_period_end')->nullable();
    $table->timestamps();
});
```

### Plans Table

```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 8, 2);
    $table->text('features')->nullable();
    $table->string('stripe_price_id')->nullable();
    $table->timestamps();
});
```

---

## Webhook Flow

### 1. Checkout Session Completed

```
User completes checkout
    ↓
Stripe sends webhook
    ↓
WebhookController receives event
    ↓
Verifies signature
    ↓
Extracts user_id and plan_id from metadata
    ↓
Retrieves subscription from Stripe
    ↓
Creates subscription record in database
    ↓
User now has active subscription
```

### 2. Subscription Updated

```
Subscription changes (upgrade/downgrade/renewal)
    ↓
Stripe sends webhook
    ↓
WebhookController receives event
    ↓
Finds existing subscription by stripe_subscription_id
    ↓
Updates status and current_period_end
    ↓
User's access level updated
```

### 3. Payment Failed

```
Payment fails
    ↓
Stripe sends invoice.payment_failed webhook
    ↓
WebhookController receives event
    ↓
Updates subscription status to 'past_due'
    ↓
User loses access (middleware checks status === 'active')
    ↓
User redirected to subscription page
```

---

## Access Control Flow

### Content Access

```
User requests premium content
    ↓
CheckSubscription middleware runs
    ↓
Checks if user has active subscription
    ↓
Verifies plan includes required tier
    ↓
If yes: Allow access
If no: Redirect to /subscription with error
```

### Daily Card Limit

```
User visits /study
    ↓
CheckDailyCardLimit middleware runs
    ↓
Determines limit based on subscription
    ↓
Counts today's reviews
    ↓
If under limit: Allow access
If at/over limit: Redirect to /dashboard with upgrade message
```

---

## Testing

### Test Coverage

**Files**:
- `tests/Feature/Stripe/WebhookHandlingTest.php` (8 tests)
- `tests/Feature/Middleware/SubscriptionMiddlewareTest.php` (16 tests)
- `tests/Feature/Middleware/DailyCardLimitTest.php` (8 tests)

**Total**: 32 new tests covering:
- Webhook route registration
- Subscription data structure
- Status updates
- Cancellation
- Access control by tier
- Daily limits by tier
- Limit resets
- Edge cases (inactive subscriptions, unknown plans)

### Running Tests

```bash
# All subscription tests
vendor/bin/sail pest tests/Feature/Stripe
vendor/bin/sail pest tests/Feature/Middleware

# Full test suite
vendor/bin/sail pest
```

---

## Stripe Dashboard Setup

### 1. Create Products

1. Go to Stripe Dashboard → Products
2. Create "Basic" product
   - Price: $9.99/month
   - Copy price ID to `plans` table `stripe_price_id`
3. Create "Premium" product
   - Price: $19.99/month
   - Copy price ID to `plans` table `stripe_price_id`

### 2. Configure Webhooks

1. Go to Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/webhook/stripe`
3. Select events:
   - `checkout.session.completed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
4. Copy webhook signing secret to `.env` as `STRIPE_WEBHOOK_SECRET`

### 3. Test Webhooks

Use Stripe CLI for local testing:
```bash
stripe listen --forward-to localhost/webhook/stripe
stripe trigger checkout.session.completed
```

---

## Usage Examples

### Protecting Routes

```php
// routes/web.php

// Free tier (no middleware needed)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ...);
});

// Basic or Premium required
Route::middleware(['auth', 'subscription:Basic,Premium'])->group(function () {
    Route::get('/wset-level-1', ...);
});

// Premium only
Route::middleware(['auth', 'subscription:Premium'])->group(function () {
    Route::get('/wset-level-2', ...);
});

// With daily card limit
Route::middleware(['auth', 'card.limit'])->group(function () {
    Route::get('/study', ...);
});
```

### Checking Subscription in Code

```php
use Domain\Subscription\Repositories\SubscriptionRepository;

$subscriptionRepo = new SubscriptionRepository();
$subscription = $subscriptionRepo->findByUserId($userId);

if ($subscription && $subscription->status === 'active') {
    // User has active subscription
}
```

### Getting Daily Review Count

```php
use Domain\Card\Repositories\CardReviewRepository;

$reviewRepo = new CardReviewRepository();
$todayReviews = $reviewRepo->getRecentActivity($userId, 1000)
    ->filter(fn($activity) => 
        Carbon::parse($activity->created_at)->isToday()
    )
    ->count();
```

---

## Error Handling

### Webhook Errors

All webhook errors are logged to Laravel logs:
```php
Log::error('Webhook processing error', [
    'event_type' => $event->type,
    'error' => $e->getMessage(),
]);
```

### Middleware Redirects

**No Subscription**:
- Message: "You need an active subscription to access this content."
- Redirect: `/subscription`

**Wrong Plan**:
- Message: "Your current plan does not include access to this content."
- Redirect: `/subscription`

**Daily Limit Reached**:
- Message: "You've reached your daily limit of X card reviews. Upgrade your plan for more!"
- Redirect: `/dashboard`

---

## Security Considerations

1. **Webhook Signature Verification**: All webhooks verified with Stripe signature
2. **CSRF Exemption**: Webhook route exempt from CSRF (verified by Stripe)
3. **Status Validation**: Only 'active' subscriptions grant access
4. **User Isolation**: Each user's subscription checked independently
5. **Rate Limiting**: Daily card limits prevent abuse

---

## Future Enhancements

### Potential Improvements

1. **Proration Handling**: Handle mid-cycle upgrades/downgrades
2. **Trial Periods**: Support free trial periods
3. **Coupons**: Implement discount codes
4. **Usage Tracking**: Detailed analytics on card review patterns
5. **Soft Limits**: Warning before hitting daily limit
6. **Grace Period**: Allow limited access for past_due subscriptions
7. **Subscription Pause**: Allow users to pause subscriptions
8. **Annual Plans**: Add yearly subscription options

---

## Troubleshooting

### Webhook Not Receiving Events

1. Check webhook URL is publicly accessible
2. Verify webhook secret in `.env` matches Stripe dashboard
3. Check Stripe dashboard → Webhooks → Recent events for errors
4. Review Laravel logs for webhook processing errors

### Access Denied Despite Active Subscription

1. Verify subscription status is exactly 'active' (not 'Active' or 'ACTIVE')
2. Check plan name matches middleware requirements exactly
3. Verify user_id matches subscription user_id
4. Check subscription hasn't expired (current_period_end)

### Daily Limit Not Working

1. Verify middleware is applied to route
2. Check CardReview records have correct `created_at` timestamps
3. Verify plan name matches expected values in middleware
4. Test with different subscription tiers

---

## Summary

✅ Stripe webhook handling implemented  
✅ Content access control by subscription tier  
✅ Daily card limits by subscription tier  
✅ Comprehensive test coverage (32 tests)  
✅ Error handling and logging  
✅ Security measures in place  

The subscription system is production-ready and awaits Stripe account configuration and webhook endpoint setup.

