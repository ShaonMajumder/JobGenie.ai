<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase text-indigo-600 font-semibold">AI configuration</p>
            <h1 class="text-2xl font-bold text-slate-800">LLM provider overrides</h1>
            <p class="text-sm text-slate-500">Manage Gemini credentials and model defaults without redeploying.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-6 space-y-6">
                <form method="POST" action="{{ route('settings.ai.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="provider" value="Provider" />
                        <select id="provider" name="provider" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach($availableProviders as $key => $label)
                                <option value="{{ $key }}" @selected($provider === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('provider')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="model" value="Default model name" />
                        <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" value="{{ old('model', $model) }}" />
                        <x-input-error :messages="$errors->get('model')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="override_api_key" value="Override API key" />
                        <x-text-input id="override_api_key" name="override_api_key" type="password" class="mt-1 block w-full" value="" autocomplete="off" placeholder="Enter to replace existing key" />
                        <x-input-error :messages="$errors->get('override_api_key')" class="mt-1" />
                        <p class="text-xs text-slate-500 mt-1">
                            Env key (masked): {{ $envKeyMasked ?? 'Not set' }}.
                            @if($hasOverrideKey)
                                A custom key is currently stored. Leave blank to keep it.
                            @endif
                        </p>
                        @if($hasOverrideKey)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600 mt-2">
                                <input type="checkbox" name="reset_override" value="1" class="rounded border-gray-300">
                                Remove stored override key
                            </label>
                        @endif
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>Save configuration</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
