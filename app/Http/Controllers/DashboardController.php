<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Models\Job;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $recentJobs = $user->jobs()
            ->with('latestSession')
            ->latest()
            ->take(5)
            ->get();

            // dd($user->resume_text);
        return view('dashboard', [
            'jobs' => $recentJobs,
            'workTypes' => Job::WORK_TYPES,
            'jobTypes' => Job::JOB_TYPES,
            'statusOptions' => JobStatus::options(),
            'defaultCurrency' => $user->native_currency ?? 'USD',
            'defaultResume' => $user->resume_text,
            'defaultBaselineSalary' => $user->recent_salary,
        ]);
    }
}
