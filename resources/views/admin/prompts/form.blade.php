@props([
    'prompt',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save prompt',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if(!in_array($method, ['POST', 'GET']))
        @method($method)
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="slug" value="Slug" />
            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" value="{{ old('slug', $prompt->slug) }}" required />
            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $prompt->name) }}" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="scope" value="Scope" />
            <x-text-input id="scope" name="scope" type="text" class="mt-1 block w-full" value="{{ old('scope', $prompt->scope) }}" required />
            <x-input-error :messages="$errors->get('scope')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="role" value="Role" />
            <select id="role" name="role" class="mt-1 w-full rounded-md border-gray-300">
                <option value="system" @selected(old('role', $prompt->role) === 'system')>System</option>
                <option value="user" @selected(old('role', $prompt->role) === 'user')>User</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="version" value="Version" />
            <x-text-input id="version" name="version" type="number" class="mt-1 block w-full" value="{{ old('version', $prompt->version ?? 1) }}" min="1" required />
            <x-input-error :messages="$errors->get('version')" class="mt-1" />
        </div>
        <div class="flex items-center space-x-2 mt-6">
            <input id="is_active" name="is_active" type="checkbox" class="rounded border-gray-300" value="1" {{ old('is_active', $prompt->is_active ?? true) ? 'checked' : '' }}>
            <x-input-label for="is_active" value="Active" />
        </div>
    </div>
    <div>
        <x-input-label for="description" value="Description" />
        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" value="{{ old('description', $prompt->description) }}" />
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="content" value="Prompt content" />
        <textarea id="content" name="content" rows="10" class="mt-1 w-full rounded-md border-gray-300">{{ old('content', $prompt->content) }}</textarea>
        <x-input-error :messages="$errors->get('content')" class="mt-1" />
    </div>
    <div class="flex justify-end">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
