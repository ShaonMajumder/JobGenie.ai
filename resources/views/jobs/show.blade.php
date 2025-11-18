@php
    $latestSession = $job->sessions->first();
    $salary = $latestSession?->salary_suggestions['ranges'] ?? null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm text-indigo-600 font-semibold uppercase tracking-wide">{{ $job->company_name }}</p>
                <h1 class="text-2xl font-bold text-slate-800">{{ $job->title }}</h1>
                <p class="text-sm text-slate-500">{{ $job->location ?? 'Location TBD' }} • {{ ucfirst($job->work_type) }} • {{ ucfirst(str_replace('_',' ', $job->job_type)) }}</p>
            </div>
            <form method="POST" action="{{ route('jobs.update', $job) }}" class="flex items-center gap-3">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium text-slate-600">Status</label>
                <select name="status" class="rounded-md border-gray-300">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($job->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-primary-button>Update</x-primary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-100 p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Baseline salary</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">
                        {{ $job->baseline_currency }} {{ number_format($job->baseline_salary ?? 0, 0) }}
                    </p>
                    <p class="text-sm text-slate-500 mt-2">Last updated {{ $job->updated_at->diffForHumans() }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-100 p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Latest ATS score</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $latestSession?->ats_score ?? '—' }}</p>
                    <div class="mt-3 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full bg-indigo-500" style="width: {{ $latestSession?->ats_score ?? 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-100 p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Career packs generated</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $job->sessions->count() }}</p>
                    <p class="text-sm text-slate-500 mt-2">Last run {{ optional($latestSession?->created_at)->diffForHumans() ?? 'N/A' }}</p>
                </div>
            </section>

            @if($latestSession)
                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-slate-800">Cover letter</h2>
                            <button x-data @click.prevent="navigator.clipboard.writeText(@js($latestSession->generated_cover_letter))" class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">Copy</button>
                        </div>
                        <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-wrap">
                            {{ $latestSession->generated_cover_letter }}
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-slate-800">Tailored resume</h2>
                            <button x-data @click.prevent="navigator.clipboard.writeText(@js($latestSession->generated_tailored_resume))" class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">Copy</button>
                        </div>
                        <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-wrap">
                            {{ $latestSession->generated_tailored_resume }}
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-3">Salary expectations</h2>
                        @if($salary)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs uppercase text-slate-500">
                                            <th class="py-2">Range</th>
                                            <th class="py-2">Monthly Native / USD</th>
                                            <th class="py-2">Yearly Native / USD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($salary as $range => $values)
                                            <tr>
                                                <td class="py-2 font-semibold text-slate-700">{{ ucfirst($range) }}</td>
                                                <td class="py-2 text-slate-600">
                                                    {{ number_format($values['monthly']['native'] ?? 0, 0) }} / {{ number_format($values['monthly']['usd'] ?? 0, 0) }}
                                                </td>
                                                <td class="py-2 text-slate-600">
                                                    {{ number_format($values['yearly']['native'] ?? 0, 0) }} / {{ number_format($values['yearly']['usd'] ?? 0, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">{{ $latestSession->salary_suggestions['notes'] ?? '' }}</p>
                        @else
                            <p class="text-sm text-slate-500">No salary data captured for this run.</p>
                        @endif
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-3">ATS feedback</h2>
                        <div class="text-sm text-slate-600 whitespace-pre-wrap">
                            {{ $latestSession->ats_feedback }}
                        </div>
                    </div>
                </section>
            @endif

            <section class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Generate new career pack</h2>
                        <p class="text-sm text-slate-500">Adjust salary targets or paste a refreshed resume.</p>
                    </div>
                    <p class="text-xs text-slate-400">Latest run {{ optional($latestSession?->created_at)->toDayDateTimeString() ?? 'N/A' }}</p>
                </div>
                <form method="POST" action="{{ route('jobs.sessions.store', $job) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <x-input-label for="baseline_salary" value="Baseline salary" />
                        <x-text-input name="baseline_salary" type="number" step="0.01" class="mt-1 block w-full" value="{{ $job->baseline_salary }}" />
                        <x-input-error :messages="$errors->get('baseline_salary')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="currency" value="Currency" />
                        <x-text-input name="currency" type="text" class="mt-1 block w-full uppercase" value="{{ $job->baseline_currency }}" required />
                        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="resume_text" value="Resume snippet" />
                        <textarea name="resume_text" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('resume_text', $job->user->resume_text) }}</textarea>
                        <x-input-error :messages="$errors->get('resume_text')" class="mt-1" />
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <x-primary-button>Generate again</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-800 mb-3">Interview preparation</h2>
                    <form method="POST" action="{{ route('jobs.conversations.store', $job) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="interview_prep">
                        <x-input-label for="interview_notes" value="Interview notes or concerns" />
                        <textarea id="interview_notes" name="user_input" rows="4" class="w-full rounded-md border-gray-300" placeholder="Panel interview next Tuesday focusing on data strategy.">{{ old('user_input') }}</textarea>
                        <x-primary-button>Generate prep guide</x-primary-button>
                    </form>
                    <div class="mt-4 space-y-4">
                        @forelse($job->conversations->where('type','interview_prep') as $conversation)
                            <article class="border border-slate-100 rounded-lg p-4">
                                <p class="text-xs text-slate-400 uppercase">Generated {{ $conversation->created_at->diffForHumans() }}</p>
                                <div class="prose prose-sm text-slate-700 max-w-none whitespace-pre-wrap mt-2">
                                    {{ $conversation->llm_output }}
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">No interview prep sessions yet.</p>
                        @endforelse
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-3">Offer negotiation</h2>
                        <form method="POST" action="{{ route('jobs.conversations.store', $job) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="type" value="negotiation">
                            <div>
                                <x-input-label value="Currency" />
                                <x-text-input name="currency" type="text" class="mt-1 block w-full uppercase" value="{{ $job->baseline_currency }}" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label value="Offered amount" />
                                    <x-text-input name="offer_amount" type="number" step="0.01" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label value="Target amount" />
                                    <x-text-input name="target_amount" type="number" step="0.01" class="mt-1 block w-full" />
                                </div>
                            </div>
                            <div>
                                <x-input-label value="Context or recruiter notes" />
                                <textarea name="context" rows="3" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                            </div>
                            <x-primary-button>Draft negotiation email</x-primary-button>
                        </form>
                        <div class="mt-4 space-y-4">
                            @forelse($job->conversations->where('type','negotiation') as $conversation)
                                <article class="border border-slate-100 rounded-lg p-4 text-sm text-slate-600 whitespace-pre-wrap">
                                    {{ $conversation->llm_output }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-500">No negotiation scripts yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800 mb-3">Follow-up / reapply</h2>
                        <form method="POST" action="{{ route('jobs.conversations.store', $job) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="type" value="followup">
                            <div>
                                <x-input-label value="Recruiter notes or email" />
                                <textarea name="context" rows="3" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                            </div>
                            <div>
                                <x-input-label value="Key reminder to include" />
                                <textarea name="user_input" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                            </div>
                            <x-primary-button>Generate follow-up</x-primary-button>
                        </form>
                        <div class="mt-4 space-y-4">
                            @forelse($job->conversations->where('type','followup') as $conversation)
                                <article class="border border-slate-100 rounded-lg p-4 text-sm text-slate-600 whitespace-pre-wrap">
                                    {{ $conversation->llm_output }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-500">No follow-up templates yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">History</h2>
                <div class="space-y-4">
                    @forelse($job->sessions as $session)
                        <div class="border border-slate-100 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Session on {{ $session->created_at->toDayDateTimeString() }}</p>
                                    <p class="text-xs text-slate-500">ATS: {{ $session->ats_score }} | Baseline: {{ $session->input_currency }} {{ $session->input_baseline_salary }}</p>
                                </div>
                                <span class="text-xs text-slate-400">#{!! $session->id !!}</span>
                            </div>
                            <details class="mt-3">
                                <summary class="text-sm text-indigo-600 cursor-pointer">View outputs</summary>
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
                                    <div class="whitespace-pre-wrap">
                                        <p class="font-semibold text-slate-800 mb-1">Cover Letter</p>
                                        {{ $session->generated_cover_letter }}
                                    </div>
                                    <div class="whitespace-pre-wrap">
                                        <p class="font-semibold text-slate-800 mb-1">Resume</p>
                                        {{ $session->generated_tailored_resume }}
                                    </div>
                                </div>
                            </details>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No sessions recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
