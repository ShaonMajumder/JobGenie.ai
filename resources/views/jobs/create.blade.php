<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Create job session</h1>
            <p class="text-sm text-slate-500">Store the job in your pipeline and instantly generate the first career pack.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-6">
                @include('jobs.partials.form', [
                    'job' => null,
                    'action' => route('jobs.store'),
                    'method' => 'POST',
                    'workTypes' => $workTypes,
                    'jobTypes' => $jobTypes,
                    'defaultCurrency' => $defaultCurrency,
                    'defaultResume' => $defaultResume,
                    'submitLabel' => 'Generate career pack',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
