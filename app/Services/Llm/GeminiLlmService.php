<?php

namespace App\Services\Llm;

use App\Services\AppConfigService;
use Illuminate\Support\Arr;
use RuntimeException;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

class GeminiLlmService implements LlmServiceInterface
{
    public function __construct(
        private readonly AppConfigService $config,
    ) {
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     * @param  array<string, mixed>                            $options
     * @return array{content:string, raw:mixed}
     */
    public function generate(array $messages, array $options = []): array
    {
        // 1. Resolve API key (options > app config override > llm config)
        $provider = config('llm.providers.gemini', []);

        $apiKey = $options['api_key']
            ?? $this->config->get('llm.override_api_key')
            ?? Arr::get($provider, 'api_key');

        if (! $apiKey) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        // 2. Resolve model name
        $model = $options['model']
            ?? $this->config->get('llm.model')
            ?? config('llm.model', 'gemini-2.5-flash');

        // 3. Split messages into system + user (we ignore assistant here for simplicity)
        $systemInstruction = null;
        $userMessages = [];

        foreach ($messages as $message) {
            if (! isset($message['role'], $message['content'])) {
                continue;
            }

            if ($message['role'] === 'system' && $systemInstruction === null) {
                $systemInstruction = $message['content'];
                continue;
            }

            if ($message['role'] === 'user') {
                $userMessages[] = $message['content'];
            }
        }

        if (empty($userMessages)) {
            throw new RuntimeException('Gemini generate() called without any user messages.');
        }

        // 4. Build Gemini client + model (v1beta needed for systemInstruction)
        $client = new Client($apiKey);

        $modelBuilder = $client
            ->withV1BetaVersion()
            ->generativeModel($model);

        if ($systemInstruction) {
            $modelBuilder = $modelBuilder->withSystemInstruction($systemInstruction);
        }

        // $chat = $modelBuilder->startChat(); // for chat-based

        // 5. Replay user messages; take the last response as final
        $lastResponse = null;
        // dd($userMessages);
        foreach ($userMessages as $text) {
            // $lastResponse = $chat->generateContent(new TextPart($text)); // for chat-based
            $lastResponse = $modelBuilder->generateContent(new TextPart($text));
        }

        if (! $lastResponse) {
            throw new RuntimeException('Gemini did not return any response.');
        }

        $content = trim($lastResponse->text());

        if ($content === '') {
            throw new RuntimeException('Gemini response did not include any content.');
        }

        // 6. Try to expose "raw" safely (if library supports array export)
        $raw = null;
        if (is_object($lastResponse)) {
            if (method_exists($lastResponse, 'toArray')) {
                $raw = $lastResponse->toArray();
            } elseif ($lastResponse instanceof \JsonSerializable) {
                $raw = $lastResponse->jsonSerialize();
            }
        }

        return [
            'content' => $content,
            'raw'     => $raw,
        ];
    }
}
