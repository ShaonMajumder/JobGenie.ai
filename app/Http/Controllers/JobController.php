<?php

namespace App\Http\Controllers;

use App\Enums\ConversationType;
use App\Enums\JobStatus;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use App\Services\CareerPackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobController extends Controller
{
    public function __construct(private readonly CareerPackService $careerPackService)
    {
    }

    public function index(Request $request)
    {
        $jobs = $request->user()
            ->jobs()
            ->with('latestSession')
            ->latest()
            ->paginate(10);

        return view('jobs.index', [
            'jobs' => $jobs,
            'statusOptions' => JobStatus::options(),
        ]);
    }

    public function create(Request $request)
    {
        return view('jobs.create', [
            'workTypes' => Job::WORK_TYPES,
            'jobTypes' => Job::JOB_TYPES,
            'defaultCurrency' => $request->user()->native_currency ?? 'USD',
            'defaultResume' => $request->user()->resume_text,
        ]);
    }

    public function store(StoreJobRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $data['baseline_currency'] = strtoupper($data['baseline_currency'] ?? $user->native_currency ?? 'USD');
        $job = $user->jobs()->create([
            'title' => $data['title'],
            'company_name' => $data['company_name'],
            'company_career_page' => $data['company_career_page'] ?? null,
            'location' => $data['location'] ?? null,
            'work_type' => $data['work_type'],
            'job_type' => $data['job_type'],
            'job_description' => $data['job_description'],
            'job_description_url' => $data['job_description_url'] ?? null,
            'baseline_salary' => $data['baseline_salary'] ?? null,
            'baseline_currency' => $data['baseline_currency'],
        ]);

        try {
            $sessionPayload = $this->careerPackService->generate($job, [
                'baseline_salary' => $data['baseline_salary'] ?? null,
                'resume_text' => $data['resume_text'] ?? $user->resume_text,
            ]);
        } catch (Throwable $exception) {
            Log::error('Career pack generation failed', [
                'job_id' => $job->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('jobs.show', $job)
                ->with('error', 'Job created but the AI generation failed. Please retry from the job page.');
        }

        $job->sessions()->create(array_merge($sessionPayload, [
            'user_id' => $user->id,
            'input_baseline_salary' => $data['baseline_salary'] ?? null,
            'input_currency' => $data['baseline_currency'],
            'input_resume_text' => $data['resume_text'] ?? $user->resume_text,
        ]));

        // Persist last-used values on the user so the dashboard can prefill next time
        $user->update([
            'native_currency' => $data['baseline_currency'] ?? $user->native_currency,
            'recent_salary' => $data['baseline_salary'] ?? $user->recent_salary,
            'resume_text' => $data['resume_text'] ?? $user->resume_text,
        ]);

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', 'Career pack generated successfully.');
    }

    public function show(Request $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $job->load([
            'sessions' => fn ($query) => $query->latest(),
            'conversations' => fn ($query) => $query->latest(),
            'user',
        ]);

        return view('jobs.show', [
            'job' => $job,
            'statusOptions' => JobStatus::options(),
            'conversationTypes' => ConversationType::options(),
        ]);
    }

    public function edit(Request $request, Job $job)
    {
        $this->authorizeJob($request, $job);
        $job->load('user');

        return view('jobs.edit', [
            'job' => $job,
            'workTypes' => Job::WORK_TYPES,
            'jobTypes' => Job::JOB_TYPES,
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $job->update($request->validated());

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', 'Job updated.');
    }

    public function destroy(Request $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $job->delete();

        return redirect()->route('jobs.index')->with('status', 'Job deleted.');
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        abort_unless($job->user_id === $request->user()->id, 403);
    }
}
