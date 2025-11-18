<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-wide text-indigo-600 font-semibold">Edit job</p>
            <h1 class="text-2xl font-bold text-slate-800">{{ $job->title }} @ {{ $job->company_name }}</h1>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-6">
                @include('jobs.partials.form', [
                    'job' => $job,
                    'action' => route('jobs.update', $job),
                    'method' => 'PUT',
                    'workTypes' => $workTypes,
                    'jobTypes' => $jobTypes,
                    'defaultCurrency' => $job->baseline_currency,
                    'defaultResume' => $job->user->resume_text,
                    'submitLabel' => 'Update job',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
