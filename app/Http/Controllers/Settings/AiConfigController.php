<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAiConfigRequest;
use App\Services\AppConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiConfigController extends Controller
{
    public function __construct(private readonly AppConfigService $configService)
    {
    }

    public function index(Request $request)
    {
        $provider = $this->configService->get('llm.provider') ?? config('llm.provider');
        $model = $this->configService->get('llm.model') ?? config('llm.model');
        $overrideKey = $this->configService->get('llm.override_api_key');

        return view('settings.ai', [
            'provider' => $provider,
            'model' => $model,
            'hasOverrideKey' => filled($overrideKey),
            'envKeyMasked' => $this->maskKey(env('GEMINI_API_KEY')),
            'availableProviders' => [
                'gemini' => 'Google Gemini',
            ],
        ]);
    }

    public function update(UpdateAiConfigRequest $request)
    {
        $data = $request->validated();

        $this->configService->set('llm.provider', $data['provider']);
        $this->configService->set('llm.model', $data['model']);
        if ($request->boolean('reset_override')) {
            $this->configService->set('llm.override_api_key', null, true);
        } elseif ($request->filled('override_api_key')) {
            $this->configService->set('llm.override_api_key', $data['override_api_key'], true);
        }

        return redirect()->route('settings.ai')->with('status', 'AI configuration updated.');
    }

    private function maskKey(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        return Str::mask($key, '*', 4, max(strlen($key) - 8, 4));
    }
}
