<?php

namespace App\Services\Billing;

use App\Models\SubscriptionPlan;
use App\Models\User;
use RuntimeException;
use Stripe\StripeClient;

class StripePaymentService
{
    private ?StripeClient $client = null;

    public function __construct(private readonly ?string $secret = null)
    {
        //
    }

    /**
     * @return array{payment_intent_id:string, client_secret:string}
     */
    public function createPaymentIntent(User $user, SubscriptionPlan $plan): array
    {
        if ($plan->price_monthly <= 0) {
            throw new RuntimeException('Payment intent is not required for free plans.');
        }

        $currency = strtolower($plan->currency ?: config('stripe.currency', 'usd'));

        $intent = $this->client()->paymentIntents->create([
            'amount' => (int) round($plan->price_monthly * 100),
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
            ],
            'description' => sprintf('%s subscription - %s', config('app.name', 'JobGenie.ai'), $plan->name),
        ]);

        return [
            'payment_intent_id' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function confirmPaymentIntent(User $user, SubscriptionPlan $plan, string $paymentIntentId)
    {
        $intent = $this->client()->paymentIntents->retrieve($paymentIntentId);

        $expectedAmount = (int) round($plan->price_monthly * 100);
        $amountReceived = (int) ($intent->amount_received ?? $intent->amount ?? 0);

        if ($intent->status !== 'succeeded' || $amountReceived < $expectedAmount) {
            throw new RuntimeException('Stripe payment has not been completed.');
        }

        return $intent;
    }

    private function client(): StripeClient
    {
        $secret = $this->secret ?? config('stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        return $this->client ??= new StripeClient($secret);
    }
}
