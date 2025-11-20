<?php

namespace App\Providers;

use App\Services\AppConfigService;
use App\Services\Billing\AiUsageBillingService;
use App\Services\Llm\LlmServiceInterface;
use App\Services\Llm\MeteredLlmService;
use App\Services\PromptService;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PromptService::class);
        $this->app->singleton(AppConfigService::class);
        $this->app->singleton(AiUsageBillingService::class);
        $this->mergeConfigFrom(config_path('billing.php'), 'billing');
        $this->mergeConfigFrom(config_path('ai_pricing.php'), 'ai_pricing');
        $this->mergeConfigFrom(config_path('stripe.php'), 'stripe');

        $this->app->singleton(LlmServiceInterface::class, function ($app) {
            $providerKey = config('llm.provider');
            $providers = config('llm.providers', []);
            $class = Arr::get($providers, "{$providerKey}.class");

            if (! $class) {
                throw new InvalidArgumentException("Unsupported LLM provider [{$providerKey}].");
            }

            $delegate = $app->make($class);

            return new MeteredLlmService(
                $delegate,
                $app->make(AiUsageBillingService::class),
                $providerKey,
                config('llm.model', Arr::get($providers, "{$providerKey}.model", 'default-model'))
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
