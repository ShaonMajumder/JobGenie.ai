<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Billing Portal
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 text-green-900">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-md p-4 text-red-900">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white p-6 shadow rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Current Plan</p>
                        <h3 class="text-2xl font-semibold text-gray-900">
                            {{ $plan?->name ?? 'No plan selected' }}
                        </h3>
                        @if($plan)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ ucfirst($plan->billing_mode) }} • {{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}/{{ $plan->billing_interval }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                AI tokens: {{ number_format($usage['used']) }} / {{ number_format($usage['included']) }}
                                (Remaining: {{ number_format($usage['remaining']) }})
                            </p>
                            @if($plan->isPrepaid() && $usage['remaining'] <= 0)
                                <p class="mt-2 text-sm text-red-600 font-semibold">
                                    You've used all your AI tokens — please upgrade or wait for renewal.
                                </p>
                            @endif
                        @else
                            <p class="mt-2 text-sm text-gray-500">
                                Choose a plan below to unlock AI features.
                            </p>
                        @endif
                    </div>
                    @if($plan)
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $plan->isPrepaid() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $plan->isPrepaid() ? 'Prepaid tokens – hard cap' : 'Postpaid – overage billed monthly' }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 shadow rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">Available Plans</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($plans as $availablePlan)
                        <div class="border rounded-lg p-5 flex flex-col justify-between {{ $plan && $plan->id === $availablePlan->id ? 'border-indigo-500 ring-1 ring-indigo-200' : 'border-gray-200' }}">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xl font-semibold text-gray-900">{{ $availablePlan->name }}</h4>
                                    <span class="text-sm px-2 py-1 rounded-full {{ $availablePlan->isPrepaid() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $availablePlan->isPrepaid() ? 'Prepaid' : 'Postpaid' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ $availablePlan->description }}</p>
                                <p class="mt-4 text-3xl font-bold text-gray-900">
                                    {{ $availablePlan->currency }} {{ number_format($availablePlan->price_monthly, 2) }}
                                    <span class="text-base font-medium text-gray-500">/{{ $availablePlan->billing_interval }}</span>
                                </p>
                                <p class="mt-2 text-sm text-gray-600">
                                    Includes {{ number_format($availablePlan->ai_included_tokens_monthly) }} tokens / month
                                </p>
                                @if(!$availablePlan->isPrepaid() || $availablePlan->allowsOverage())
                                    <p class="mt-1 text-xs text-gray-500">
                                        Overage allowed, billed per 1K tokens.
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">
                                        Hard cap – upgrade when tokens are out.
                                    </p>
                                @endif
                            </div>
                            <div class="mt-4">
                                <form method="POST" action="{{ route('billing.subscribe', $availablePlan) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        {{ $plan && $plan->id === $availablePlan->id ? 'Current Plan' : 'Select Plan' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-6 shadow rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">Invoices</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AI Usage</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ optional($invoice->period_start)->format('M d, Y') }} – {{ optional($invoice->period_end)->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $invoice->currency }} {{ number_format($invoice->amount_subscription, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $invoice->currency }} {{ number_format($invoice->amount_ai_usage, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                                        {{ $invoice->currency }} {{ number_format($invoice->amount_total, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                        No invoices yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
