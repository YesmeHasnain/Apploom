<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class GoogleAI
{
    protected string $key;
    protected string $defaultModel;

    public function __construct()
    {
        $this->key          = config('services.google_ai.key', '');
        $this->defaultModel = config('services.google_ai.model', 'gemini-1.5-flash');
    }

    protected function endpoint(string $model, string $method = 'generateContent'): string
    {
        // v1beta REST
        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:{$method}?key={$this->key}";
    }

    /**
     * Basic text generation
     */
    public function generate(string $prompt, ?string $model = null, array $opts = []): string
    {
        $model = $model ?: $this->defaultModel;

        $payload = array_filter([
            'systemInstruction' => isset($opts['system']) ? [
                'parts' => [['text' => (string)$opts['system']]],
            ] : null,
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => array_filter([
                'temperature'     => $opts['temperature'] ?? 0.7,
                'maxOutputTokens' => $opts['max_tokens']   ?? 2048,
                'topP'            => $opts['top_p']        ?? null,
                'topK'            => $opts['top_k']        ?? null,
            ]),
            'safetySettings' => $opts['safety'] ?? [],
        ]);

        $res = Http::timeout(60)->post($this->endpoint($model), $payload);

        if (!$res->ok()) {
            throw new \RuntimeException('Gemini API error: '.$res->status().' '.$res->body());
        }

        // Gemini text is typically at candidates[0].content.parts[*].text
        $candidates = $res->json('candidates', []);
        $parts = Arr::get($candidates, '0.content.parts', []);
        $texts = array_map(fn($p) => $p['text'] ?? '', $parts);
        return trim(implode("\n", $texts));
    }

    /**
     * Force JSON output (strict schema by prompt), returns decoded array
     */
    public function generateJson(string $prompt, ?string $model = null, array $schemaHint = []): array
    {
        $system = "Return STRICT minified JSON only. No prose, no markdown. "
                . "If you must include code blocks, remove backticks. "
                . "Use this shape: ".json_encode($schemaHint ?: [
                    'project' => [
                        'name' => 'string',
                        'targets' => ['web','react-native','backend'],
                        'pages' => [['name'=>'string','features'=>['string']]],
                        'tables' => [['name'=>'string','columns'=>[['name'=>'string','type'=>'string']]]],
                        'apis' => [['name'=>'string','purpose'=>'string','auth'=>'none|api_key|oauth2']],
                    ],
                ]);

        $raw = $this->generate($prompt, $model, ['system' => $system, 'temperature' => 0.4]);
        // Try strict json_decode; if fails, attempt to strip junk
        $json = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // try to extract { ... }
            if (preg_match('/\{.*\}/s', $raw, $m)) {
                $json = json_decode($m[0], true);
            }
        }
        if (!is_array($json)) {
            throw new \RuntimeException('Gemini did not return valid JSON: '.$raw);
        }
        return $json;
    }
}
