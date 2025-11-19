<?php

namespace App\Services\Llm;

use App\Services\Billing\AiUsageBillingService;
use Illuminate\Support\Facades\Auth;

class MeteredLlmService implements LlmServiceInterface
{
    public function __construct(
        private readonly LlmServiceInterface $inner,
        private readonly AiUsageBillingService $billing,
        private readonly string $provider,
        private readonly string $model,
    ) {
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     * @param  array<string, mixed>                            $options
     * @return array{content:string, raw:mixed}
     */
    public function generate(array $messages, array $options = []): array
    {
        $user = Auth::user();
        $provider = $options['provider'] ?? $this->provider;
        $model = $options['model'] ?? $this->model;
        $inputTokens = 0;

        if ($user) {
            $inputTokens = $this->billing->estimateTokensFromMessages($messages);
            $this->billing->assertCanUseTokens($user, $provider, $model, $inputTokens);
        }

        $response = $this->inner->generate($messages, $options);

        if ($user) {
            $outputTokens = $this->billing->estimateTokensFromString($response['content'] ?? '');
            $this->billing->recordUsage($user, [
                'provider' => $provider,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'job_id' => $options['job_id'] ?? null,
                'job_session_id' => $options['job_session_id'] ?? null,
            ]);
        }

        return $response;
    }
}
