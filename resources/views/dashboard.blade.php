<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm uppercase tracking-wide text-indigo-600 font-semibold">JobGenie.ai</p>
            <h1 class="text-2xl font-bold text-slate-800">Career co-pilot dashboard</h1>
            <p class="text-sm text-slate-500">Paste a job description, drop your resume, and JobGenie.ai will craft a full career pack in seconds.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <section class="bg-white border border-slate-100 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-1">New job session</h2>
                    <p class="text-sm text-slate-500 mb-4">Provide the job details and your baseline salary to tailor the cover letter, resume, salary ranges, and ATS insights.</p>
                    <form id="job-session-form" method="POST" action="{{ route('jobs.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="title" value="Job title" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{ old('title') }}" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="company_name" value="Company" />
                                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" value="{{ old('company_name') }}" required />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="company_website" value="Company Career Page" />
                                <x-text-input id="company_website" name="company_website" type="url" class="mt-1 block w-full" placeholder="https://example.com" value="{{ old('company_website') }}" />
                                <x-input-error :messages="$errors->get('company_website')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="location" value="Location" />
                                <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" placeholder="Remote, New York, etc." value="{{ old('location') }}" />
                                <x-input-error :messages="$errors->get('location')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="work_type" value="Work type" />
                                <select id="work_type" name="work_type" class="mt-1 w-full rounded-md border-gray-300">
                                    @foreach($workTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('work_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('work_type')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="job_type" value="Job type" />
                                <select id="job_type" name="job_type" class="mt-1 w-full rounded-md border-gray-300">
                                    @foreach($jobTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('job_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('job_type')" class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="job_description_url" value="Job description link" />
                            <x-text-input id="job_description_url" name="job_description_url" type="url" class="mt-1 block w-full" placeholder="https://..." value="{{ old('job_description_url') }}" />
                            <x-input-error :messages="$errors->get('job_description_url')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="job_description" value="Job description" />
                            <textarea id="job_description" name="job_description" rows="5" class="mt-1 w-full rounded-md border-gray-300" required>{{ old('job_description') }}</textarea>
                            <x-input-error :messages="$errors->get('job_description')" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <x-input-label for="baseline_salary" value="Baseline salary" />
                                <x-text-input id="baseline_salary" name="baseline_salary" type="number" step="0.01" class="mt-1 block w-full" placeholder="65000" value="{{ old('baseline_salary', $defaultBaselineSalary) }}" />
                                <x-input-error :messages="$errors->get('baseline_salary')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="baseline_currency" value="Currency" />
                                <x-text-input id="baseline_currency" name="baseline_currency" type="text" class="mt-1 block w-full uppercase" value="{{ old('baseline_currency', $defaultCurrency) }}" />
                                <x-input-error :messages="$errors->get('baseline_currency')" class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="resume_text" value="Resume (paste text)" />
                            <textarea id="resume_text" name="resume_text" rows="6" class="mt-1 w-full rounded-md border-gray-300" placeholder="Paste your resume or summary here...">{{ old('resume_text', $defaultResume) }}</textarea>
                            <x-input-error :messages="$errors->get('resume_text')" class="mt-1" />
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button>
                                Generate career pack
                            </x-primary-button>
                        </div>
                    </form>
                </section>
                <section class="bg-white border border-slate-100 shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">Recent jobs</h2>
                            <p class="text-sm text-slate-500">Track every opportunity and regenerate packs anytime.</p>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-semibold">View all</a>
                    </div>
                    @if($jobs->isEmpty())
                        <p class="text-sm text-slate-500">No jobs yet. Create your first job session to unlock insights.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($jobs as $job)
                                <a href="{{ route('jobs.show', $job) }}" class="block border border-slate-100 rounded-lg p-4 hover:border-indigo-200 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm uppercase text-slate-400 font-semibold">{{ $job->company_name }}</p>
                                            <p class="text-lg font-semibold text-slate-800">{{ $job->title }}</p>
                                        </div>
                                        <span class="text-xs px-3 py-1 rounded-full bg-slate-100 text-slate-600">{{ $job->status_label }}</span>
                                    </div>
                                    <div class="mt-2 text-sm text-slate-500 flex flex-wrap gap-4">
                                        <span>{{ $job->work_type }}</span>
                                        <span>{{ $job->job_type }}</span>
                                        <span>Updated {{ $job->updated_at->diffForHumans() }}</span>
                                    </div>
                                    @if($job->latestSession)
                                        <div class="mt-3 text-xs text-slate-500">
                                            Last ATS score: <span class="font-semibold text-slate-700">{{ $job->latestSession->ats_score ?? '—' }}</span>
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
