<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobConversationRequest;
use App\Models\Job;
use App\Services\Llm\LlmServiceInterface;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class JobConversationController extends Controller
{
    private const PROMPT_MAP = [
        'interview_prep' => ['system' => 'interview_prep_system', 'user' => 'interview_prep_user'],
        'negotiation' => ['system' => 'negotiation_helper_system', 'user' => 'negotiation_helper_user'],
        'followup' => ['system' => 'followup_helper_system', 'user' => 'followup_helper_user'],
    ];

    public function __construct(
        private readonly PromptService $promptService,
        private readonly LlmServiceInterface $llmService
    ) {
    }

    public function store(StoreJobConversationRequest $request, Job $job)
    {
        $this->authorizeJob($request, $job);
        $type = $request->input('type');
        $mapping = self::PROMPT_MAP[$type] ?? null;

        if (! $mapping) {
            throw new RuntimeException('Unsupported conversation type.');
        }

        $systemPrompt = $this->promptService->getActive($mapping['system'])?->content;
        $userPrompt = $this->promptService->renderTemplate($mapping['user'], $this->buildVariables($job, $request));

        if (! $systemPrompt || ! $userPrompt) {
            return redirect()
                ->route('jobs.show', $job)
                ->with('error', 'Prompt configuration missing for this helper.');
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        try {
            $response = $this->llmService->generate($messages);
        } catch (Throwable $exception) {
            Log::error('Job conversation generation failed', [
                'job_id' => $job->id,
                'type' => $type,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('jobs.show', $job)
                ->with('error', 'Unable to generate helper content right now.');
        }

        $job->conversations()->create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'user_input' => $request->input('user_input'),
            'llm_output' => $response['content'],
            'meta' => [
                'currency' => $request->input('currency'),
                'offer_amount' => $request->input('offer_amount'),
                'target_amount' => $request->input('target_amount'),
                'context' => $request->input('context'),
            ],
        ]);

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', 'Helper content generated.');
    }

    private function buildVariables(Job $job, Request $request): array
    {
        $user = $request->user();

        return [
            'JobTitle' => $job->title,
            'CompanyName' => $job->company_name,
            'CompanyWebsite' => $job->company_website ?? 'N/A',
            'JobLocation' => $job->location ?? 'Not specified',
            'WorkType' => ucfirst(str_replace('_', ' ', $job->work_type)),
            'JobDescription' => $job->job_description,
            'Resume' => $user->resume_text ?? 'No resume on file.',
            'InterviewNotes' => $request->input('user_input'),
            'OfferDetails' => sprintf(
                'Offered %s %s - %s',
                $request->input('currency') ?? $job->baseline_currency,
                $request->input('offer_amount') ?? 'N/A',
                $request->input('context') ?? ''
            ),
            'TargetAmount' => $request->input('target_amount') ?? '',
            'Currency' => $request->input('currency') ?? $job->baseline_currency,
            'NegotiationContext' => $request->input('context'),
            'Status' => $job->status_label,
            'FollowupContext' => $request->input('context'),
        ];
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        abort_unless($job->user_id === $request->user()->id, 403);
    }
}
