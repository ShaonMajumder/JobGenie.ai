<?php

namespace App\Providers;

use App\Services\AppConfigService;
use App\Services\Llm\LlmServiceInterface;
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

        $this->app->singleton(LlmServiceInterface::class, function ($app) {
            $providerKey = config('llm.provider');
            $providers = config('llm.providers', []);
            $class = Arr::get($providers, "{$providerKey}.class");

            if (! $class) {
                throw new InvalidArgumentException("Unsupported LLM provider [{$providerKey}].");
            }

            return $app->make($class);
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
