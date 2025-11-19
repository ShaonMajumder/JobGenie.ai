<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class KillBillClient
{
    public function __construct(private readonly array $config = [])
    {
        $this->config = $config ?: config('billing.killbill', []);
    }

    public function createAccount(User $user): string
    {
        $payload = [
            'name' => $user->name,
            'externalKey' => 'user-'.$user->id,
            'email' => $user->email,
        ];

        return $this->dispatch('accounts', $payload, 'kb-account-');
    }

    public function createSubscription(User $user, SubscriptionPlan $plan): string
    {
        $payload = [
            'accountId' => 'user-'.$user->id,
            'planName' => $plan->slug,
            'productCategory' => 'BASE',
        ];

        return $this->dispatch('subscriptions', $payload, 'kb-sub-');
    }

    public function createInvoiceForSubscription(Subscription $subscription, Invoice $invoice): void
    {
        $payload = [
            'subscriptionId' => $subscription->killbill_subscription_id,
            'amount' => $invoice->amount_total,
            'currency' => $invoice->currency,
        ];

        $externalId = $this->dispatch('invoices', $payload, 'kb-invoice-');
        $invoice->update([
            'killbill_invoice_id' => $externalId,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
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

    private function dispatch(string $endpoint, array $payload, string $prefix): string
    {
        $baseUrl = $this->config['base_url'] ?? 'http://killbill:8080';
        $username = $this->config['username'] ?? 'admin';
        $password = $this->config['password'] ?? 'password';
        $apiKey = $this->config['api_key'] ?? 'bob';
        $apiSecret = $this->config['api_secret'] ?? 'lazar';

        try {
            $response = Http::withBasicAuth($username, $password)
                ->withHeaders([
                    'X-Killbill-ApiKey' => $apiKey,
                    'X-Killbill-ApiSecret' => $apiSecret,
                ])
                ->acceptJson()
                ->post(sprintf('%s/1.0/kb/%s', rtrim($baseUrl, '/'), $endpoint), $payload);

            if ($response->successful()) {
                return $response->json('invoiceId')
                    ?? $response->json('subscriptionId')
                    ?? $response->json('accountId')
                    ?? $prefix.Str::uuid()->toString();
            }
        } catch (Throwable $exception) {
            Log::warning('Kill Bill request failed, falling back to synthetic id', [
                'endpoint' => $endpoint,
                'error' => $exception->getMessage(),
            ]);
        }

        return $prefix.Str::uuid()->toString();
    }
}
