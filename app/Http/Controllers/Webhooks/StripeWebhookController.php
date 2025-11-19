<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceGeneratedMail;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\Billing\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeBillingService $billing)
    {
    }

    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('billing.stripe.webhook_secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $signature ?? '', $secret)
                : json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            Log::warning('Stripe webhook validation failed', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'invalid signature'], 400);
        }

        $type = is_array($event) ? ($event['type'] ?? 'unknown') : $event->type;
        $dataObject = is_array($event) ? ($event['data']['object'] ?? []) : $event->data->object;

        match ($type) {
            'invoice.payment_succeeded' => $this->handleInvoicePaid($dataObject),
            'invoice.payment_failed' => $this->handleInvoiceFailed($dataObject),
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionUpdate($dataObject),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function handleInvoicePaid(mixed $stripeInvoice): void
    {
        $invoiceId = is_array($stripeInvoice) ? ($stripeInvoice['id'] ?? null) : ($stripeInvoice->id ?? null);

        if (! $invoiceId) {
            return;
        }

        $invoice = Invoice::where('stripe_invoice_id', $invoiceId)->first();

        if (! $invoice) {
            return;
        }

        $this->billing->markInvoiceAsPaid($invoice);
        $invoice->refresh();

        try {
            Mail::to($invoice->user)->send(new InvoiceGeneratedMail($invoice));
        } catch (Throwable $exception) {
            Log::warning('Unable to send invoice mail after Stripe payment', [
                'invoice_id' => $invoice->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function handleInvoiceFailed(mixed $stripeInvoice): void
    {
        $invoiceId = is_array($stripeInvoice) ? ($stripeInvoice['id'] ?? null) : ($stripeInvoice->id ?? null);

        if (! $invoiceId) {
            return;
        }

        $invoice = Invoice::where('stripe_invoice_id', $invoiceId)->first();

        if (! $invoice) {
            return;
        }

        $invoice->forceFill([
            'status' => 'payment_failed',
            'paid_at' => null,
        ])->save();

        if ($invoice->subscription) {
            $invoice->subscription->forceFill([
                'status' => 'past_due',
            ])->save();
        }
    }

    private function handleSubscriptionUpdate(mixed $stripeSubscription): void
    {
        $subscriptionId = is_array($stripeSubscription) ? ($stripeSubscription['id'] ?? null) : ($stripeSubscription->id ?? null);

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if (! $subscription) {
            return;
        }

        $status = is_array($stripeSubscription) ? ($stripeSubscription['status'] ?? $subscription->status) : ($stripeSubscription->status ?? $subscription->status);
        $subscription->forceFill([
            'status' => $status,
            'ended_at' => $status === 'canceled' ? now() : $subscription->ended_at,
        ])->save();
    }
}
