<?php

namespace App\Services;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class AppConfigService
{
    private const CACHE_PREFIX = 'app_config.';

    public function get(string $key, mixed $default = null): mixed
    {
        $entry = Cache::remember($this->cacheKey($key), now()->addMinutes(10), fn () => AppConfig::where('key', $key)->first());

        if (! $entry) {
            return $default;
        }

        return $entry->is_encrypted && $entry->value !== null
            ? Crypt::decryptString($entry->value)
            : $entry->value;
    }

    public function set(string $key, mixed $value, bool $encrypt = false, ?string $description = null): AppConfig
    {
        $payload = [
            'value' => $value === null ? null : (string) $value,
            'is_encrypted' => $encrypt,
            'description' => $description,
        ];

        if ($encrypt && $value !== null) {
            $payload['value'] = Crypt::encryptString((string) $value);
        }

        $config = AppConfig::updateOrCreate(['key' => $key], $payload);

        Cache::forget($this->cacheKey($key));

        return $config;
    }

    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }
}
