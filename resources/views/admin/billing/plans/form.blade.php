<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $plan->exists ? 'Edit Plan' : 'Create Plan' }}
            </h2>
            <a href="{{ route('admin.billing.plans.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to Plans
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ $plan->exists ? route('admin.billing.plans.update', $plan) : route('admin.billing.plans.store') }}">
                    @csrf
                    @if($plan->exists)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @error('slug') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $plan->description) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price (Monthly)</label>
                                <input type="number" step="0.01" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency</label>
                                <input type="text" name="currency" value="{{ old('currency', $plan->currency ?? 'USD') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm uppercase" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Billing Interval</label>
                                <input type="text" name="billing_interval" value="{{ old('billing_interval', $plan->billing_interval ?? 'monthly') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Billing Mode</label>
                                <select name="billing_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="postpaid" @selected(old('billing_mode', $plan->billing_mode) === 'postpaid')>Postpaid</option>
                                    <option value="prepaid" @selected(old('billing_mode', $plan->billing_mode) === 'prepaid')>Prepaid (hard cap)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Included Tokens (Monthly)</label>
                                <input type="number" name="ai_included_tokens_monthly" value="{{ old('ai_included_tokens_monthly', $plan->ai_included_tokens_monthly) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input id="allow_overage" type="checkbox" name="allow_overage" value="1" @checked(old('allow_overage', $plan->allow_overage)) class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <label for="allow_overage" class="ml-2 text-sm text-gray-700">Allow overage billing</label>
                            </div>
                            <div class="flex items-center">
                                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true)) class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <label for="is_active" class="ml-2 text-sm text-gray-700">Plan is active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
