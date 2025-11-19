<?php

namespace App\Http\Middleware;

use App\Mail\LowTokensWarningMail;
use App\Mail\OutOfTokensMail;
use App\Services\Billing\AiUsageBillingService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActiveSubscription
{
    public function __construct(private readonly AiUsageBillingService $billingService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response|RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $subscription = $user->activeSubscription();
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan) {
            return $this->deny($request, 'You need an active subscription to use AI features.');
        }

        if ($plan->isPrepaid() && ! $plan->allowsOverage()) {
            $remaining = $this->billingService->remainingTokens($user, $subscription);
            $included = (int) $plan->ai_included_tokens_monthly;

            if ($included > 0 && $remaining > 0) {
                $ratio = $remaining / $included;
                $warningThreshold = config('billing.token_alert_thresholds.warning', 0.2);

                if ($ratio <= $warningThreshold) {
                    Mail::to($user)->send(new LowTokensWarningMail($user, $remaining, $included));
                }
            }

            if ($remaining <= 0) {
                Mail::to($user)->send(new OutOfTokensMail($user));

                return $this->deny(
                    $request,
                    "You've used all your AI tokens — please upgrade or wait for renewal."
                );
            }
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], Response::HTTP_PAYMENT_REQUIRED);
        }

        return redirect()->route('billing.index')->with('error', $message);
    }
}
