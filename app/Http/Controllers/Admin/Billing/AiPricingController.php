<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Services\AppConfigService;
use Illuminate\Http\Request;

class AiPricingController extends Controller
{
    public function __construct(private readonly AppConfigService $configService)
    {
    }

    public function edit()
    {
        $providers = config('ai_pricing.providers', []);
        $currency = $this->configService->get('ai.billing.currency') ?? config('ai_pricing.currency', 'USD');

        return view('admin.billing.ai-pricing', compact('providers', 'currency'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'max:3'],
            'pricing' => ['array'],
        ]);

        $this->configService->set('ai.billing.currency', strtoupper($data['currency']));

        foreach ($data['pricing'] ?? [] as $provider => $models) {
            foreach ($models as $model => $prices) {
                $input = (float) ($prices['input_per_1k'] ?? 0);
                $output = (float) ($prices['output_per_1k'] ?? 0);

                $this->configService->set("ai.pricing.{$provider}.{$model}.input_per_1k", $input);
                $this->configService->set("ai.pricing.{$provider}.{$model}.output_per_1k", $output);
            }
        }

        return redirect()->route('admin.billing.ai-pricing.edit')->with('status', 'AI pricing updated.');
    }
}
