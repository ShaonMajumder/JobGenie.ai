<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobSessionRequest;
use App\Models\Job;
use App\Services\CareerPackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobSessionController extends Controller
{
    public function __construct(private readonly CareerPackService $careerPackService)
    {
    }

    public function store(StoreJobSessionRequest $request, Job $job)
    {
        $this->authorizeJob($request, $job);
        $user = $request->user();
        $data = $request->validated();

        try {
            $sessionPayload = $this->careerPackService->generate($job, [
                'baseline_salary' => $data['baseline_salary'] ?? $job->baseline_salary,
                'resume_text' => $data['resume_text'] ?? $user->resume_text,
            ]);
        } catch (Throwable $exception) {
            Log::error('Career pack regeneration failed', [
                'job_id' => $job->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('jobs.show', $job)
                ->with('error', 'Unable to generate the new career pack. Please try again later.');
        }

        $job->sessions()->create(array_merge($sessionPayload, [
            'user_id' => $user->id,
            'input_baseline_salary' => $data['baseline_salary'] ?? $job->baseline_salary,
            'input_currency' => $data['currency'] ?? $job->baseline_currency,
            'input_resume_text' => $data['resume_text'] ?? $user->resume_text,
        ]));

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', 'New career pack generated.');
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        abort_unless($job->user_id === $request->user()->id, 403);
    }
}
