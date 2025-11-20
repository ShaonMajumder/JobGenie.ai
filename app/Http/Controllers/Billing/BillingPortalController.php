<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Mail\SubscriptionChangedMail;
use App\Mail\SubscriptionStartedMail;
use App\Models\SubscriptionPlan;
use App\Services\Billing\AiUsageBillingService;
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

    public function startCheckout(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();
        abort_if(! $plan->is_active, 404);

        if ($plan->price_monthly <= 0) {
            return $this->finalizeSubscription($user, $plan, null);
        }

        try {
            $successUrl = route('billing.checkout.success', ['plan' => $plan->id], true).'?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('billing.checkout.cancel', [], true);

            $session = $this->stripePayments->createCheckoutSession($user, $plan, $successUrl, $cancelUrl);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('billing.index')
                ->with('error', 'Unable to start Stripe checkout.');
        }

        return redirect()->away($session['url']);
    }

    public function checkoutSuccess(Request $request, SubscriptionPlan $plan)
    {
        $sessionId = $request->query('session_id');
        abort_if(! $sessionId, 404);

        try {
            $session = $this->stripePayments->retrieveCheckoutSession($sessionId);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('billing.index')
                ->with('error', 'Unable to verify Stripe checkout.');
        }

        $sessionPlanId = isset($session->metadata->plan_id) ? (int) $session->metadata->plan_id : null;
        $sessionUserId = isset($session->metadata->user_id) ? (int) $session->metadata->user_id : null;

        if ($sessionPlanId !== (int) $plan->id || $sessionUserId !== (int) $request->user()->id) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Checkout session does not match this plan.');
        }

        if ($session->payment_status !== 'paid' || empty($session->payment_intent)) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Stripe has not completed this payment.');
        }

        return $this->finalizeSubscription($request->user(), $plan, $session->payment_intent);
    }

    public function checkoutCancel()
    {
        return redirect()
            ->route('billing.index')
            ->with('error', 'Stripe checkout was cancelled.');
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();
        abort_if(! $plan->is_active, 404);

        $paymentIntentId = $request->filled('payment_intent_id')
            ? $request->string('payment_intent_id')->toString()
            : null;

        return $this->finalizeSubscription($user, $plan, $paymentIntentId);
    }

    private function finalizeSubscription($user, SubscriptionPlan $plan, ?string $paymentIntentId)
    {
        $isNewSubscription = ! (bool) $user->activeSubscription();
        $updatedSubscription = null;

        try {
            DB::transaction(function () use ($user, $plan, $paymentIntentId, &$updatedSubscription): void {
                $subscription = $user->activeSubscription();
                $periodStart = now();
                $periodEnd = now()->addMonth();

                if ($subscription) {
                    $subscription->forceFill([
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
                        'current_period_start' => $periodStart,
                        'current_period_end' => $periodEnd,
                        'renews_at' => $periodEnd,
                    ])->save();
                } else {
                    $subscription = $user->subscriptions()->create([
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
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
                    'stripe_payment_intent_id' => $paymentIntentId,
                ]);

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
