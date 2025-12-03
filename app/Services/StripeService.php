<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = config('stripe.secret');
        
        if (! $secret) {
            throw new \RuntimeException('Stripe secret key not configured');
        }
        
        $this->stripe = new StripeClient($secret);
    }

    public function getClient(): StripeClient
    {
        return $this->stripe;
    }

    /**
     * Create a Stripe checkout session for subscription
     */
    public function createCheckoutSession(int $planId, int $userId, string $successUrl, string $cancelUrl): \Stripe\Checkout\Session
    {
        return $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $this->getPlanStripePriceId($planId),
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => \Domain\User\Models\User::find($userId)?->email,
            'metadata' => [
                'user_id' => $userId,
                'plan_id' => $planId,
            ],
        ]);
    }

    /**
     * Create a Stripe customer
     */
    public function createCustomer(string $email, string $name): \Stripe\Customer
    {
        return $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
        ]);
    }

    /**
     * Cancel a Stripe subscription
     */
    public function cancelSubscription(string $stripeSubscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->cancel($stripeSubscriptionId);
    }

    /**
     * Retrieve a Stripe subscription
     */
    public function getSubscription(string $stripeSubscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->retrieve($stripeSubscriptionId);
    }

    /**
     * Get the Stripe price ID for a plan
     */
    private function getPlanStripePriceId(int $planId): string
    {
        $plan = \Domain\Subscription\Models\Plan::findOrFail($planId);
        
        if ($plan->stripe_price_id === null) {
            throw new \RuntimeException("Plan {$planId} does not have a Stripe price ID configured.");
        }

        return $plan->stripe_price_id;
    }
}
