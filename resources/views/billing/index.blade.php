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
                                <form method="POST" action="{{ route('billing.subscribe', $availablePlan) }}" data-plan-form>
                                    @csrf
                                    <input type="hidden" name="payment_intent_id" value="">
                                    <button
                                        type="button"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60"
                                        data-plan-button
                                        data-plan-id="{{ $availablePlan->id }}"
                                        data-plan-name="{{ $availablePlan->name }}"
                                        data-plan-price="{{ (float) $availablePlan->price_monthly }}"
                                        {{ $plan && $plan->id === $availablePlan->id ? 'disabled' : '' }}
                                    >
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
    <div id="card-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase text-gray-400 font-semibold">Secure checkout</p>
                    <h3 id="modal-plan-name" class="text-lg font-semibold text-gray-900"></h3>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-close-modal>&times;</button>
            </div>
            <div id="card-element" class="border border-gray-200 rounded-lg p-3"></div>
            <p class="text-xs text-gray-500">
                Use Stripe test cards (e.g. 4242 4242 4242 4242) with any future expiration and CVC.
            </p>
            <div class="flex items-center gap-3">
                <button type="button" class="flex-1 inline-flex justify-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" data-close-modal>
                    Cancel
                </button>
                <button type="button" id="confirm-card-button" class="flex-1 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
                    Confirm &amp; Pay
                </button>
            </div>
            <p id="card-error" class="text-sm text-red-600 hidden"></p>
        </div>
    </div>

    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const publishableKey = @json(config('stripe.publishable_key'));
                const intentRouteTemplate = @json(route('billing.plan.intent', '__PLAN__'));
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                const modal = document.getElementById('card-modal');
                const planNameEl = document.getElementById('modal-plan-name');
                const errorEl = document.getElementById('card-error');
                const confirmButton = document.getElementById('confirm-card-button');
                let stripe;
                let elements;
                let cardElement;
                let activeForm = null;
                let activePaymentIntent = null;

                if (publishableKey) {
                    stripe = Stripe(publishableKey);
                    elements = stripe.elements();
                    cardElement = elements.create('card');
                    cardElement.mount('#card-element');
                }

                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    errorEl.classList.add('hidden');
                    errorEl.textContent = '';
                    confirmButton.disabled = false;
                    activeForm = null;
                    activePaymentIntent = null;
                };

                document.querySelectorAll('[data-close-modal]').forEach((button) => {
                    button.addEventListener('click', closeModal);
                });

                document.querySelectorAll('[data-plan-button]').forEach((button) => {
                    button.addEventListener('click', async () => {
                        const form = button.closest('form');
                        const planPrice = parseFloat(button.dataset.planPrice || '0');

                        if (planPrice <= 0 || ! publishableKey) {
                            return form.submit();
                        }

                        if (! stripe || ! cardElement) {
                            alert('Stripe is not configured. Please contact support.');
                            return;
                        }

                        button.disabled = true;

                        try {
                            const intentRoute = intentRouteTemplate.replace('__PLAN__', button.dataset.planId);
                            const response = await fetch(intentRoute, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({}),
                            });

                            const data = await response.json();

                            if (! response.ok || ! data.client_secret) {
                                throw new Error(data.message ?? 'Unable to start checkout.');
                            }

                            activeForm = form;
                            activePaymentIntent = data.payment_intent_id;
                            planNameEl.textContent = button.dataset.planName;
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            confirmButton.disabled = false;
                            confirmButton.dataset.clientSecret = data.client_secret;
                        } catch (error) {
                            alert(error.message);
                        } finally {
                            button.disabled = false;
                        }
                    });
                });

                confirmButton.addEventListener('click', async () => {
                    if (! activeForm || ! activePaymentIntent) {
                        return;
                    }

                    confirmButton.disabled = true;
                    errorEl.classList.add('hidden');
                    errorEl.textContent = '';

                    try {
                        const { error } = await stripe.confirmCardPayment(confirmButton.dataset.clientSecret, {
                            payment_method: {
                                card: cardElement,
                            },
                        });

                        if (error) {
                            throw error;
                        }

                        activeForm.querySelector('input[name="payment_intent_id"]').value = activePaymentIntent;
                        activeForm.submit();
                    } catch (err) {
                        errorEl.textContent = err.message ?? 'Unable to confirm payment.';
                        errorEl.classList.remove('hidden');
                        confirmButton.disabled = false;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
