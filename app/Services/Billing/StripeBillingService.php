<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Throwable;

class StripeBillingService
{
    public function __construct(
        private readonly StripeClient $client,
        private readonly array $config = []
    ) {
    }

    public function createCustomer(User $user): string
    {
        try {
            $customer = $this->client->customers->create([
                'name' => $user->name,
                'email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            return $customer->id;
        } catch (Throwable $exception) {
            Log::warning('Stripe customer creation failed, using fallback id', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return 'cus_'.Str::uuid()->toString();
    }

    public function ensureCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customerId = $this->createCustomer($user);
        $user->forceFill(['stripe_customer_id' => $customerId])->save();

        return $customerId;
    }

    public function createSubscription(User $user, SubscriptionPlan $plan): array
    {
        $customerId = $this->ensureCustomer($user);
        $priceId = $this->ensurePriceForPlan($plan);

        try {
            $stripeSubscription = $this->client->subscriptions->create([
                'customer' => $customerId,
                'items' => [
                    [
                        'price' => $priceId,
                        'metadata' => [
                            'plan_id' => $plan->id,
                            'plan_slug' => $plan->slug,
                        ],
                    ],
                ],
                'payment_behavior' => 'default_incomplete',
                'collection_method' => 'charge_automatically',
                'metadata' => [
                    'user_id' => $user->id,
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $paymentIntent = $stripeSubscription->latest_invoice->payment_intent ?? null;

            return [
                'stripe_subscription' => $stripeSubscription,
                'client_secret' => $paymentIntent?->client_secret,
            ];
        } catch (Throwable $exception) {
            Log::error('Stripe subscription creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function createInvoiceForSubscription(Subscription $subscription, Invoice $invoice): void
    {
        $customerId = $this->ensureCustomer($subscription->user);
        $currency = strtolower($invoice->currency ?? $this->config['currency'] ?? 'usd');
        $amount = (int) round((float) $invoice->amount_total * 100);

        try {
            if ($amount > 0) {
                $this->client->invoiceItems->create([
                    'customer' => $customerId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => sprintf(
                        'JobGenie.ai subscription %s',
                        $subscription->plan?->name ?? 'charge'
                    ),
                ]);
            }

            $stripeInvoice = $this->client->invoices->create([
                'customer' => $customerId,
                'collection_method' => 'charge_automatically',
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                ],
                'auto_advance' => true,
            ]);

            $invoice->forceFill([
                'stripe_invoice_id' => $stripeInvoice->id,
                'status' => $stripeInvoice->status ?? 'open',
            ])->save();
        } catch (Throwable $exception) {
            Log::warning('Stripe invoice sync failed', [
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscription->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function markInvoiceAsPaid(Invoice $invoice): void
    {
        if (! $invoice->paid_at) {
            $invoice->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
            ])->save();
        }
    }

    public function retrieveSubscription(string $stripeSubscriptionId)
    {
        return $this->client->subscriptions->retrieve($stripeSubscriptionId, ['expand' => ['latest_invoice']]);
    }

    public function retrieveInvoice(string $stripeInvoiceId)
    {
        return $this->client->invoices->retrieve($stripeInvoiceId);
    }

    private function ensurePriceForPlan(SubscriptionPlan $plan): string
    {
        $metadata = $plan->metadata ?? [];
        $priceId = Arr::get($metadata, 'stripe_price_id');

        if ($priceId) {
            return $priceId;
        }

        try {
            $productId = Arr::get($metadata, 'stripe_product_id');

            if (! $productId) {
                $product = $this->client->products->create([
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'plan_slug' => $plan->slug,
                    ],
                ]);

                $productId = $product->id;
                $metadata['stripe_product_id'] = $productId;
            }

            $price = $this->client->prices->create([
                'unit_amount' => (int) round((float) $plan->price_monthly * 100),
                'currency' => strtolower($plan->currency ?? $this->config['currency'] ?? 'usd'),
                'product' => $productId,
                'recurring' => [
                    'interval' => $this->mapInterval($plan->billing_interval),
                ],
            ]);

            $metadata['stripe_price_id'] = $price->id;
            $plan->forceFill(['metadata' => $metadata])->save();

            return $price->id;
        } catch (Throwable $exception) {
            Log::warning('Stripe price provisioning failed', [
                'plan_id' => $plan->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return 'price_'.Str::uuid()->toString();
    }

    private function mapInterval(?string $interval): string
    {
        $normalized = strtolower($interval ?? '');

        return match ($normalized) {
            'month', 'monthly' => 'month',
            'year', 'yearly', 'annually', 'annual' => 'year',
            'week', 'weekly' => 'week',
            'day', 'daily' => 'day',
            default => 'month',
        };
    }
}
