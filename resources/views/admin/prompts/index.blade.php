<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase text-indigo-600 font-semibold">Prompt management</p>
                <h1 class="text-2xl font-bold text-slate-800">LLM prompt library</h1>
            </div>
            <a href="{{ route('admin.prompts.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-indigo-500">
                New prompt
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Scope</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-center">Version</th>
                            <th class="px-4 py-3 text-center">Active</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($prompts as $prompt)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $prompt->slug }}</td>
                                <td class="px-4 py-3">{{ $prompt->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $prompt->scope }}</td>
                                <td class="px-4 py-3 text-slate-500 capitalize">{{ $prompt->role }}</td>
                                <td class="px-4 py-3 text-center">{{ $prompt->version }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $prompt->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $prompt->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.prompts.edit', $prompt) }}" class="text-indigo-600 text-sm font-semibold">Edit</a>
                                    <form action="{{ route('admin.prompts.clone', $prompt) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="text-sm text-slate-500 hover:text-slate-700">Clone</button>
                                    </form>
                                    <form action="{{ route('admin.prompts.destroy', $prompt) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-slate-500 hover:text-slate-700">{{ $prompt->is_active ? 'Disable' : 'Activate' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $prompts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
