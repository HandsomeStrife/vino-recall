<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StripeService;
use Domain\Subscription\Actions\CancelSubscriptionAction;
use Domain\Subscription\Actions\CreateSubscriptionAction;
use Domain\Subscription\Actions\UpdateSubscriptionAction;
use Domain\Subscription\Repositories\PlanRepository;
use Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreateSubscriptionAction $createSubscription,
        private UpdateSubscriptionAction $updateSubscription,
        private CancelSubscriptionAction $cancelSubscription,
        private PlanRepository $planRepository
    ) {}

    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid webhook signature', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event->data->object),
                'customer.subscription.created' => $this->handleSubscriptionCreated($event->data->object),
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event->data->object),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event->data->object),
                default => Log::info('Unhandled webhook event', ['type' => $event->type])
            };

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function handleCheckoutSessionCompleted(object $session): void
    {
        $userId = $session->metadata->user_id ?? null;
        $planId = $session->metadata->plan_id ?? null;

        if (! $userId || ! $planId) {
            Log::warning('Missing metadata in checkout session', [
                'session_id' => $session->id,
            ]);

            return;
        }

        $user = User::find($userId);
        if (! $user) {
            Log::error('User not found for checkout session', ['user_id' => $userId]);

            return;
        }

        // Get the subscription from Stripe
        $stripeSubscription = $this->stripeService->getSubscription($session->subscription);

        $this->createSubscription->execute(
            userId: (int) $userId,
            planId: (int) $planId,
            stripeSubscriptionId: $session->subscription,
            status: $stripeSubscription->status,
            currentPeriodEnd: $stripeSubscription->current_period_end
        );

        Log::info('Subscription created from checkout', [
            'user_id' => $userId,
            'plan_id' => $planId,
            'subscription_id' => $session->subscription,
        ]);
    }

    private function handleSubscriptionCreated(object $subscription): void
    {
        Log::info('Subscription created webhook received', [
            'subscription_id' => $subscription->id,
            'customer' => $subscription->customer,
        ]);

        // The subscription is already created in handleCheckoutSessionCompleted
        // This webhook is for logging and potential future use
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $existingSubscription = \Domain\Subscription\Models\Subscription::where(
            'stripe_subscription_id',
            $subscription->id
        )->first();

        if (! $existingSubscription) {
            Log::warning('Subscription not found for update', [
                'stripe_subscription_id' => $subscription->id,
            ]);

            return;
        }

        $this->updateSubscription->execute(
            subscriptionId: $existingSubscription->id,
            status: $subscription->status,
            currentPeriodEnd: $subscription->current_period_end
        );

        Log::info('Subscription updated', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $existingSubscription = \Domain\Subscription\Models\Subscription::where(
            'stripe_subscription_id',
            $subscription->id
        )->first();

        if (! $existingSubscription) {
            Log::warning('Subscription not found for deletion', [
                'stripe_subscription_id' => $subscription->id,
            ]);

            return;
        }

        $this->cancelSubscription->execute($existingSubscription->id);

        Log::info('Subscription canceled', [
            'subscription_id' => $subscription->id,
        ]);
    }

    private function handleInvoicePaymentSucceeded(object $invoice): void
    {
        Log::info('Invoice payment succeeded', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription,
            'amount' => $invoice->amount_paid,
        ]);

        // Update subscription status to active if it was past_due
        if ($invoice->subscription) {
            $subscription = \Domain\Subscription\Models\Subscription::where(
                'stripe_subscription_id',
                $invoice->subscription
            )->first();

            if ($subscription && $subscription->status === 'past_due') {
                $this->updateSubscription->execute(
                    subscriptionId: $subscription->id,
                    status: 'active'
                );
            }
        }
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        Log::warning('Invoice payment failed', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription,
            'amount' => $invoice->amount_due,
        ]);

        // Update subscription status to past_due
        if ($invoice->subscription) {
            $subscription = \Domain\Subscription\Models\Subscription::where(
                'stripe_subscription_id',
                $invoice->subscription
            )->first();

            if ($subscription) {
                $this->updateSubscription->execute(
                    subscriptionId: $subscription->id,
                    status: 'past_due'
                );
            }
        }
    }
}

