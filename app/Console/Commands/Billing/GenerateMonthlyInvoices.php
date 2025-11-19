<?php

namespace App\Console\Commands\Billing;

use App\Mail\InvoiceGeneratedMail;
use App\Models\User;
use App\Services\Billing\KillBillClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateMonthlyInvoices
{
    public function __construct(private readonly KillBillClient $killBillClient)
    {
    }

    public function __invoke(): void
    {
        $periodStart = now()->subMonthNoOverflow()->startOfMonth();
        $periodEnd = (clone $periodStart)->endOfMonth();

        User::with(['subscriptions.plan'])
            ->get()
            ->each(function ($user) use ($periodStart, $periodEnd): void {
                $subscription = $user->activeSubscription();
                $plan = $subscription?->plan;

                if (! $subscription || ! $plan) {
                    return;
                }

                $usageRecords = $user->aiUsageRecords()
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->whereNull('billed_invoice_id')
                    ->orderBy('created_at')
                    ->get();

                if ($usageRecords->isEmpty() && $plan->price_monthly <= 0) {
                    return;
                }

                DB::transaction(function () use ($user, $subscription, $plan, $usageRecords, $periodStart, $periodEnd): void {
                    $remainingAllowance = $plan->includesTokens() ? (int) $plan->ai_included_tokens_monthly : 0;
                    $amountAiUsage = 0.0;

                    foreach ($usageRecords as $record) {
                        if ($plan->isPrepaid() && ! $plan->allowsOverage()) {
                            break;
                        }

                        $tokens = $record->total_tokens;

                        if ($remainingAllowance > 0) {
                            if ($remainingAllowance >= $tokens) {
                                $remainingAllowance -= $tokens;
                                continue;
                            }

                            $tokens -= $remainingAllowance;
                            $remainingAllowance = 0;
                        }

                        if ($tokens > 0 && $record->total_tokens > 0) {
                            $tokenCost = $record->cost_total / $record->total_tokens;
                            $amountAiUsage += $tokenCost * $tokens;
                        }
                    }

                    $invoice = $user->invoices()->create([
                        'subscription_id' => $subscription->id,
                        'status' => 'paid',
                        'number' => 'INV-'.$periodStart->format('Ym').'-'.$user->id,
                        'amount_subscription' => $plan->price_monthly,
                        'amount_ai_usage' => round($amountAiUsage, 2),
                        'amount_total' => round($plan->price_monthly + $amountAiUsage, 2),
                        'currency' => $plan->currency ?? config('billing.currency', 'USD'),
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'paid_at' => now(),
                    ]);

                    $usageRecords->each(function ($record) use ($invoice): void {
                        $record->update(['billed_invoice_id' => $invoice->id]);
                    });

                    $nextPeriodStart = (clone $periodStart)->addMonthNoOverflow();
                    $nextPeriodEnd = (clone $nextPeriodStart)->endOfMonth();

                    $subscription->forceFill([
                        'current_period_start' => $nextPeriodStart,
                        'current_period_end' => $nextPeriodEnd,
                        'renews_at' => $nextPeriodEnd,
                    ])->save();

                    try {
                        $this->killBillClient->createInvoiceForSubscription($subscription, $invoice);
                        Mail::to($user)->send(new InvoiceGeneratedMail($invoice));
                    } catch (\Throwable $exception) {
                        Log::warning('Kill Bill sync failed for invoice', [
                            'invoice_id' => $invoice->id,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                });
            });
    }
}
