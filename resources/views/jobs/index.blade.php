<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Your job pipeline</h1>
                <p class="text-sm text-slate-500">Monitor every opportunity, ATS score, and negotiation track.</p>
            </div>
            <a href="{{ route('jobs.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-indigo-500">
                New job session
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-slate-100 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Job</th>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Last update</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jobs as $job)
                            <tr class="text-sm text-slate-600">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $job->title }}</div>
                                    <div class="text-xs text-slate-500">{{ ucfirst($job->work_type) }} • {{ ucfirst(str_replace('_',' ', $job->job_type)) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span>{{ $job->company_name }}</span>
                                    @if($job->location)
                                        <div class="text-xs text-slate-500">{{ $job->location }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-slate-100 text-slate-600">{{ $job->status_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $job->updated_at->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('jobs.show', $job) }}" class="text-indigo-600 font-semibold text-sm hover:text-indigo-500">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-slate-500">
                                    No jobs yet. Launch your first job session to see the pipeline.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
