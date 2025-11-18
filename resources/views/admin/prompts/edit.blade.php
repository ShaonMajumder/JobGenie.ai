<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase text-indigo-600 font-semibold">Edit prompt</p>
            <h1 class="text-2xl font-bold text-slate-800">{{ $prompt->name }}</h1>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-6">
                @include('admin.prompts.form', [
                    'prompt' => $prompt,
                    'action' => route('admin.prompts.update', $prompt),
                    'method' => 'PUT',
                    'submitLabel' => 'Update prompt',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
