<?php

namespace App\Services\Llm;

interface LlmServiceInterface
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content: string, raw: array<mixed>}
     */
    public function generate(array $messages, array $options = []): array;
}
