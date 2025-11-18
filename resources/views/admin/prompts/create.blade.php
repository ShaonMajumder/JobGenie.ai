<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">New prompt</h1>
            <p class="text-sm text-slate-500">Register a new system or user prompt for experimentation.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-6">
                @include('admin.prompts.form', [
                    'prompt' => $prompt,
                    'action' => route('admin.prompts.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Create prompt',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
