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

    public function createCheckoutSession(User $user, SubscriptionPlan $plan, string $successUrl, string $cancelUrl): array
    {
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $user->email,
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($plan->currency ?: config('stripe.currency', 'usd')),
                        'product_data' => [
                            'name' => $plan->name,
                            'description' => $plan->description ?: sprintf('%s plan', $plan->name),
                        ],
                        'unit_amount' => (int) round($plan->price_monthly * 100),
                    ],
                ],
            ],
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }

    public function retrieveCheckoutSession(string $sessionId)
    {
        return $this->client()->checkout->sessions->retrieve($sessionId);
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
