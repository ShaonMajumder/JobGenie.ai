<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Subscription Plans
            </h2>
            <a href="{{ route('admin.billing.plans.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md shadow hover:bg-indigo-500">
                New Plan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-900 rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($plans as $plan)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ $plan->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}/{{ $plan->billing_interval }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->isPrepaid() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ ucfirst($plan->billing_mode) }}
                                        </span>
                                        @if($plan->allowsOverage())
                                            <span class="ml-2 text-xs text-gray-500">Overage allowed</span>
                                        @else
                                            <span class="ml-2 text-xs text-gray-500">Hard cap</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ number_format($plan->ai_included_tokens_monthly) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                            {{ $plan->is_active ? 'Active' : 'Archived' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="{{ route('admin.billing.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
