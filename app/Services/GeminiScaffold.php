<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GeminiScaffold
{
    private Client $http;
    private string $base;
    private string $key;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 60]);
        $this->base = rtrim(config('services.gemini.base'), '/');
        $this->key  = (string) config('services.gemini.key');
    }

    public static function normalizeModel(?string $ui): string
    {
        $map = [
            'Gemini 1.5 Flash' => 'gemini-1.5-flash',
            'Gemini 1.5 Pro'   => 'gemini-1.5-pro',
            'gpt-4o'           => 'gemini-1.5-pro',
            'gpt-4o mini'      => 'gemini-1.5-flash',
        ];
        $v = trim((string) $ui);
        return $map[$v] ?? $v ?: config('services.gemini.model', 'gemini-1.5-flash');
    }

    private function post(string $model, array $payload): array
    {
        $endpoint = "{$this->base}/models/{$model}:generateContent?key={$this->key}";
        $res = $this->http->post($endpoint, ['json' => $payload]);
        $json = json_decode($res->getBody()->getContents(), true);
        if (($res->getStatusCode() ?? 500) !== 200) {
            throw new \RuntimeException("Gemini HTTP {$res->getStatusCode()}");
        }
        return $json;
    }

    /** Get plain text from Gemini response parts */
    private static function partsText(array $json): string
    {
        $txt = '';
        if (!empty($json['candidates'][0]['content']['parts'])) {
            foreach ($json['candidates'][0]['content']['parts'] as $p) $txt .= $p['text'] ?? '';
        }
        return trim($txt);
    }

    /** Planner → strict JSON project.json */
    public function plan(string $prompt, ?string $uiModel): array
    {
        $model = self::normalizeModel($uiModel);

        $sys = <<<SYS
You are an AI Planner. Output ONLY compact JSON for a mobile/web app blueprint named "project.json" with keys:
meta { name, theme }, pages[], database{tables[]}, required_apis[], data.mock.
Follow this exact example of structure and datatypes. Do not write backticks or prose. Keep to <= 400 lines.
SYS;

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $sys],
                    ['text' => "Idea: {$prompt}"],
                ],
            ]],
            'generationConfig' => ['temperature'=>0.4,'maxOutputTokens'=>2500],
        ];

        $json = $this->post($model, $payload);
        $text = self::partsText($json);
        $plan = json_decode($text, true);
        if (!is_array($plan)) {
            Log::warning('Planner returned non-JSON', ['text' => $text]);
            throw new \RuntimeException('Planner did not return valid JSON');
        }
        return $plan;
    }

    /** Preview: returns inner HTML only (we wrap outside) */
    public function previewInnerHtml(string $prompt, ?string $uiModel): string
    {
        $model = self::normalizeModel($uiModel);
        $sys = <<<SYS
Create a single preview screen for the described app using only HTML+CSS+very small JS.
Return ONLY the inner markup (no <html>,<head>,<body>). Use clean cards, spacing, and one small interaction.
SYS;
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $sys],
                    ['text' => "App preview for: {$prompt}"],
                ],
            ]],
            'generationConfig' => ['temperature'=>0.6,'maxOutputTokens'=>2200],
        ];
        $json = $this->post($model, $payload);
        return self::partsText($json);
    }
}
