@props([
    'job' => null,
    'action',
    'method' => 'POST',
    'workTypes' => [],
    'jobTypes' => [],
    'defaultCurrency' => 'USD',
    'defaultResume' => null,
    'submitLabel' => 'Save job',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if(!in_array($method, ['POST', 'GET']))
        @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="title" value="Job title" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                value="{{ old('title', $job->title ?? '') }}" required />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="company_name" value="Company" />
            <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full"
                value="{{ old('company_name', $job->company_name ?? '') }}" required />
            <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="company_website" value="Company Career Page" />
            <x-text-input id="company_website" name="company_website" type="url" class="mt-1 block w-full"
                value="{{ old('company_website', $job->company_website ?? '') }}" />
            <x-input-error :messages="$errors->get('company_website')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="location" value="Location" />
            <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                value="{{ old('location', $job->location ?? '') }}" />
            <x-input-error :messages="$errors->get('location')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="work_type" value="Work type" />
            <select id="work_type" name="work_type" class="mt-1 w-full rounded-md border-gray-300">
                @foreach($workTypes as $key => $label)
                    <option value="{{ $key }}" @selected(old('work_type', $job->work_type ?? 'remote') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('work_type')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="job_type" value="Job type" />
            <select id="job_type" name="job_type" class="mt-1 w-full rounded-md border-gray-300">
                @foreach($jobTypes as $key => $label)
                    <option value="{{ $key }}" @selected(old('job_type', $job->job_type ?? 'full_time') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('job_type')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="job_description_url" value="Job description link" />
        <x-text-input id="job_description_url" name="job_description_url" type="url" class="mt-1 block w-full"
            value="{{ old('job_description_url', $job->job_description_url ?? '') }}" />
        <x-input-error :messages="$errors->get('job_description_url')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="job_description" value="Job description" />
        <textarea id="job_description" name="job_description" rows="5" class="mt-1 w-full rounded-md border-gray-300" required>{{ old('job_description', $job->job_description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('job_description')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <x-input-label for="baseline_salary" value="Baseline salary" />
            <x-text-input id="baseline_salary" name="baseline_salary" type="number" step="0.01" class="mt-1 block w-full"
                value="{{ old('baseline_salary', $job->baseline_salary ?? '') }}" />
            <x-input-error :messages="$errors->get('baseline_salary')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="baseline_currency" value="Currency" />
            <x-text-input id="baseline_currency" name="baseline_currency" type="text" class="mt-1 block w-full uppercase"
                value="{{ old('baseline_currency', $job->baseline_currency ?? $defaultCurrency) }}" />
            <x-input-error :messages="$errors->get('baseline_currency')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="resume_text" value="Resume (paste text)" />
        <textarea id="resume_text" name="resume_text" rows="6" class="mt-1 w-full rounded-md border-gray-300">{{ old('resume_text', $defaultResume) }}</textarea>
        <x-input-error :messages="$errors->get('resume_text')" class="mt-1" />
    </div>

    <div class="flex justify-end">
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</form>
