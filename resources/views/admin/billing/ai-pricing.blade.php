<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AI Billing Settings
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-900 rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('admin.billing.ai-pricing.update') }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Billing Currency</label>
                        <input type="text" name="currency" value="{{ old('currency', $currency) }}" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm uppercase" required>
                    </div>

                    <div class="mt-8 space-y-6">
                        @foreach($providers as $providerKey => $provider)
                            <div class="border rounded-lg p-5">
                                <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $providerKey }} pricing</h3>
                                <div class="mt-4 grid grid-cols-1 gap-4">
                                    @foreach($provider['models'] ?? [] as $modelKey => $pricing)
                                        <div class="border rounded-lg p-4">
                                            <p class="text-sm font-medium text-gray-700">{{ $modelKey }}</p>
                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs uppercase tracking-wide text-gray-500">Input per 1K tokens</label>
                                                    <input type="number" step="0.01" name="pricing[{{ $providerKey }}][{{ $modelKey }}][input_per_1k]" value="{{ old("pricing.$providerKey.$modelKey.input_per_1k", $pricing['input_per_1k']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs uppercase tracking-wide text-gray-500">Output per 1K tokens</label>
                                                    <input type="number" step="0.01" name="pricing[{{ $providerKey }}][{{ $modelKey }}][output_per_1k]" value="{{ old("pricing.$providerKey.$modelKey.output_per_1k", $pricing['output_per_1k']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Pricing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
