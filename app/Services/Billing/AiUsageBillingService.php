<?php

namespace App\Services\Billing;

use App\Exceptions\OutOfTokensException;
use App\Models\AiUsageRecord;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AppConfigService;
use Illuminate\Support\Arr;

class AiUsageBillingService
{
    public function __construct(private readonly AppConfigService $configService)
    {
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    public function estimateTokensFromMessages(array $messages): int
    {
        $tokens = 0;

        foreach ($messages as $message) {
            $tokens += $this->estimateTokensFromString($message['content'] ?? '');
        }

        return $tokens;
    }

    public function estimateTokensFromString(string $content): int
    {
        $length = mb_strlen($content);

        return (int) ceil($length / 4);
    }

    public function assertCanUseTokens(User $user, string $provider, string $model, int $requestedTokens): void
    {
        $subscription = $user->activeSubscription();
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan) {
            return;
        }

        if (! $plan->isPrepaid() || $plan->allowsOverage()) {
            return;
        }

        $remaining = $this->remainingTokens($user, $subscription);

        if ($remaining <= 0) {
            throw new OutOfTokensException("You've used all your AI tokens — please upgrade or wait for renewal.");
        }

        if ($requestedTokens > $remaining) {
            throw new OutOfTokensException('Not enough AI tokens remaining for this request.');
        }
    }

    public function remainingTokens(User $user, ?Subscription $subscription = null): int
    {
        $subscription ??= $user->activeSubscription();
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan || ! $plan->includesTokens()) {
            return 0;
        }

        $start = $subscription->current_period_start ?? now()->startOfMonth();
        $end = $subscription->current_period_end ?? now()->endOfMonth();

        $used = (int) $user->aiUsageRecords()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_tokens');

        return max(0, (int) $plan->ai_included_tokens_monthly - $used);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordUsage(User $user, array $payload): AiUsageRecord
    {
        $provider = $payload['provider'] ?? config('llm.provider', 'gemini');
        $model = $payload['model'] ?? config('llm.model', 'gemini-2.5-flash');
        $inputTokens = (int) ($payload['input_tokens'] ?? 0);
        $outputTokens = (int) ($payload['output_tokens'] ?? 0);
        $totalTokens = $inputTokens + $outputTokens;

        $costs = $this->calculateCosts($provider, $model, $inputTokens, $outputTokens);

        return $user->aiUsageRecords()->create([
            'job_id' => $payload['job_id'] ?? null,
            'job_session_id' => $payload['job_session_id'] ?? null,
            'provider' => $provider,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'cost_input' => $costs['cost_input'],
            'cost_output' => $costs['cost_output'],
            'cost_total' => $costs['cost_total'],
            'currency' => $costs['currency'],
        ]);
    }

    public function calculateCosts(string $provider, string $model, int $inputTokens, int $outputTokens): array
    {
        $pricing = $this->pricingFor($provider, $model);
        $currency = strtoupper($this->configService->get('ai.billing.currency')
            ?? config('ai_pricing.currency', 'USD'));

        $costInput = $this->tokensToCost($inputTokens, $pricing['input_per_1k']);
        $costOutput = $this->tokensToCost($outputTokens, $pricing['output_per_1k']);

        return [
            'currency' => $currency,
            'cost_input' => $costInput,
            'cost_output' => $costOutput,
            'cost_total' => $costInput + $costOutput,
        ];
    }

    /**
     * @return array{input_per_1k:float, output_per_1k:float}
     */
    private function pricingFor(string $provider, string $model): array
    {
        $defaults = config("ai_pricing.providers.{$provider}.models.{$model}");

        if (! $defaults && function_exists('config_path')) {
            $path = config_path('ai_pricing.php');

            if (file_exists($path)) {
                $raw = require $path;
                $defaults = Arr::get($raw, "providers.{$provider}.models.{$model}", [
                    'input_per_1k' => 0.0,
                    'output_per_1k' => 0.0,
                ]);
            }
        }

        $defaults ??= [
            'input_per_1k' => 0.0,
            'output_per_1k' => 0.0,
        ];

        return [
            'input_per_1k' => (float) ($this->configService->get("ai.pricing.{$provider}.{$model}.input_per_1k")
                ?? $defaults['input_per_1k']
                ?? 0.0),
            'output_per_1k' => (float) ($this->configService->get("ai.pricing.{$provider}.{$model}.output_per_1k")
                ?? $defaults['output_per_1k']
                ?? 0.0),
        ];
    }

    private function tokensToCost(int $tokens, float $ratePerThousand): float
    {
        if ($tokens <= 0 || $ratePerThousand <= 0) {
            return 0.0;
        }

        return round(($tokens / 1000) * $ratePerThousand, 6);
    }
}
