<?php

namespace App\Services;

use App\Models\Prompt;
use Illuminate\Support\Facades\Cache;

class PromptService
{
    private const CACHE_PREFIX = 'prompts.active.';

    public function getActive(string $slug): ?Prompt
    {
        return Cache::remember($this->cacheKey($slug), now()->addMinutes(10), function () use ($slug) {
            return Prompt::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->orderByDesc('version')
                ->first();
        });
    }

    public function renderTemplate(string $slug, array $variables = []): ?string
    {
        $prompt = $this->getActive($slug);

        if (! $prompt) {
            return null;
        }

        return preg_replace_callback('/\{([\w]+)\}/', function ($matches) use ($variables) {
            $key = $matches[1];

            return (string) ($variables[$key] ?? '');
        }, $prompt->content);
    }

    public function flush(?string $slug = null): void
    {
        if ($slug) {
            Cache::forget($this->cacheKey($slug));

            return;
        }

        Cache::flush();
    }

    private function cacheKey(string $slug): string
    {
        return self::CACHE_PREFIX.$slug;
    }
}
