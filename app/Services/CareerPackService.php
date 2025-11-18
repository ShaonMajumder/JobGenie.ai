<?php

namespace App\Services;

use App\Models\Job;
use App\Services\Llm\LlmServiceInterface;
use Illuminate\Support\Arr;
use RuntimeException;

class CareerPackService
{
    public function __construct(
        private readonly PromptService $promptService,
        private readonly LlmServiceInterface $llmService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function generate(Job $job, array $extra = []): array
    {
        $user = $job->user;
        $systemPrompt = $this->promptService->getActive('career_pack_system')?->content
            ?? 'You are JobGenie.ai, a career co-pilot that produces structured JSON responses.';

        $resume = $extra['resume_text'] ?? $user->resume_text ?? '';

        $variables = [
            'NativeCurrency' => $user->native_currency ?? 'USD',
            'RecentSalary' => $user->recent_salary ?? 'Unknown',
            'BaselineNumber' => $extra['baseline_salary'] ?? $job->baseline_salary ?? $user->recent_salary ?? '0',
            'BaselineCurrency' => $job->baseline_currency ?? $user->native_currency ?? 'USD',
            'JobTitle' => $job->title,
            'CompanyName' => $job->company_name,
            'CompanyWebsite' => $job->company_career_page ?? 'N/A',
            'JobLocation' => $job->location ?? 'Not specified',
            'WorkType' => ucfirst(str_replace('_', ' ', $job->work_type)),
            'JobType' => ucfirst(str_replace('_', ' ', $job->job_type)),
            'JobDescriptionLink' => $job->job_description_url ?? 'N/A',
            'JobDescription' => $job->job_description,
            'Resume' => $resume ?: 'Candidate resume details not provided.',
        ];

        $userPrompt = $this->promptService->renderTemplate('career_pack_user', $variables);

        if (! $userPrompt) {
            throw new RuntimeException('Career pack prompt could not be loaded.');
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $response = $this->llmService->generate($messages);
        // dd( $response);

        $parsed = $this->decodeJson($response['content']);

        if (! is_array($parsed)) {
            throw new RuntimeException('Career pack did not return valid JSON.');
        }

        $ats = Arr::get($parsed, 'ats_summary', []);
        $salary = Arr::get($parsed, 'salary_expectations');

        return [
            'generated_cover_letter' => Arr::get($parsed, 'cover_letter', ''),
            'generated_tailored_resume' => Arr::get($parsed, 'tailored_resume', ''),
            'salary_suggestions' => $salary,
            'ats_score' => (int) Arr::get($ats, 'score', 0),
            'ats_feedback' => $this->formatAtsFeedback($ats),
            'raw_llm_request' => ['messages' => $messages],
            'raw_llm_response' => $response['raw'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $payload): ?array
    {
        $decoded = json_decode($payload, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($payload, '{');
        $end = strrpos($payload, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $trimmed = substr($payload, $start, $end - $start + 1);

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $ats
     */
    private function formatAtsFeedback(array $ats): string
    {
        $sections = [];

        if (! empty($ats['strengths'])) {
            $sections[] = 'Strengths:'."\n- ".implode("\n- ", (array) $ats['strengths']);
        }

        if (! empty($ats['gaps'])) {
            $sections[] = 'Gaps:'."\n- ".implode("\n- ", (array) $ats['gaps']);
        }

        if (! empty($ats['improvements'])) {
            $sections[] = 'Improvements:'."\n- ".implode("\n- ", (array) $ats['improvements']);
        }

        return implode("\n\n", $sections);
    }
}
