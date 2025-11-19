<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            All Invoices
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AI Usage</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ $invoice->user?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $invoice->user?->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $invoice->subscription?->plan?->name ?? '—' }}
                                        <div class="text-xs text-gray-500">
                                            {{ optional($invoice->period_start)->format('M d') }} – {{ optional($invoice->period_end)->format('M d') }}
                                        </div>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
