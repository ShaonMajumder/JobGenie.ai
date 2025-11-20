<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Mail\SubscriptionChangedMail;
use App\Mail\SubscriptionStartedMail;
use App\Models\SubscriptionPlan;
use App\Services\Billing\AiUsageBillingService;
use App\Services\Billing\KillBillClient;
use App\Services\Billing\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BillingPortalController extends Controller
{
    public function __construct(
        private readonly AiUsageBillingService $usageBilling,
        private readonly KillBillClient $killBillClient,
        private readonly StripePaymentService $stripePayments,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription();
        $plan = $subscription?->plan;
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price_monthly')->get();
        $invoices = $user->invoices()->latest('period_start')->take(12)->get();

        $included = $plan?->ai_included_tokens_monthly ?? 0;
        $remaining = $plan ? $this->usageBilling->remainingTokens($user, $subscription) : 0;

        return view('billing.index', [
            'subscription' => $subscription,
            'plan' => $plan,
            'plans' => $plans,
            'invoices' => $invoices,
            'usage' => [
                'included' => $included,
                'remaining' => $remaining,
                'used' => max(0, $included - $remaining),
            ],
        ]);
    }

    public function createPaymentIntent(Request $request, SubscriptionPlan $plan)
    {
        abort_if(! $plan->is_active, 404);

        if ($plan->price_monthly <= 0) {
            return response()->json([
                'requires_payment' => false,
            ]);
        }

        try {
            $intent = $this->stripePayments->createPaymentIntent($request->user(), $plan);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to initialize payment. Please try again.',
            ], 422);
        }

        return response()->json([
            'requires_payment' => true,
            'client_secret' => $intent['client_secret'],
            'payment_intent_id' => $intent['payment_intent_id'],
        ]);
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();
        abort_if(! $plan->is_active, 404);

        $requiresPayment = $plan->price_monthly > 0;
        $paymentIntentId = $request->string('payment_intent_id')->toString();
        $paymentIntent = null;

        if ($requiresPayment) {
            if (! $paymentIntentId) {
                return redirect()
                    ->route('billing.index')
                    ->with('error', 'Payment is required to change plans.');
            }

            try {
                $paymentIntent = $this->stripePayments->confirmPaymentIntent($user, $plan, $paymentIntentId);
            } catch (Throwable $exception) {
                report($exception);

                return redirect()
                    ->route('billing.index')
                    ->with('error', 'Unable to verify payment with Stripe.');
            }
        }

        $isNewSubscription = ! (bool) $user->activeSubscription();
        $updatedSubscription = null;

        try {
            DB::transaction(function () use ($user, $plan, $paymentIntent, &$updatedSubscription): void {
                $subscription = $user->activeSubscription();
                $periodStart = now();
                $periodEnd = now()->addMonth();

                $accountId = $subscription?->killbill_account_id ?? $this->killBillClient->createAccount($user);
                $subscriptionId = $this->killBillClient->createSubscription($user, $plan);

                if ($subscription) {
                    $subscription->forceFill([
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
                        'killbill_account_id' => $accountId,
                        'killbill_subscription_id' => $subscriptionId,
                        'current_period_start' => $periodStart,
                        'current_period_end' => $periodEnd,
                        'renews_at' => $periodEnd,
                    ])->save();
                } else {
                    $subscription = $user->subscriptions()->create([
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
                        'killbill_account_id' => $accountId,
                        'killbill_subscription_id' => $subscriptionId,
                        'current_period_start' => $periodStart,
                        'current_period_end' => $periodEnd,
                        'renews_at' => $periodEnd,
                    ]);
                }

                $invoice = $user->invoices()->create([
                    'subscription_id' => $subscription->id,
                    'number' => 'INV-'.now()->format('Ym').'-'.$user->id,
                    'status' => 'paid',
                    'amount_subscription' => $plan->price_monthly,
                    'amount_ai_usage' => 0,
                    'amount_total' => $plan->price_monthly,
                    'currency' => $plan->currency ?? config('billing.currency', 'USD'),
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'paid_at' => now(),
                    'stripe_payment_intent_id' => $paymentIntent->id ?? null,
                ]);

                $this->killBillClient->createInvoiceForSubscription($subscription, $invoice);
                $updatedSubscription = $subscription;
            });
        } catch (Throwable $exception) {
            Log::error('Subscription update failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('billing.index')
                ->with('error', 'Unable to update subscription right now.');
        }

        if ($updatedSubscription) {
            $mailable = $isNewSubscription
                ? new SubscriptionStartedMail($updatedSubscription)
                : new SubscriptionChangedMail($updatedSubscription);

            Mail::to($user)->send($mailable);
        }

        return redirect()
            ->route('billing.index')
            ->with('status', 'Subscription updated.');
    }
}
