<?php

namespace App\Services;

class ApiDetector
{
    /**
     * From planner output, return normalized list:
     *   [['api'=>'google_maps','auth'=>'oauth','required'=>true], ...]
     */
    public function detect(array $projectJson): array
    {
        $out = [];
        foreach (($projectJson['required_apis'] ?? []) as $a) {
            $name = strtolower(preg_replace('/\s+/', '_', $a['name'] ?? ''));
            if (!$name) continue;
            $out[] = [
                'api'      => $name,
                'auth'     => strtolower($a['auth'] ?? 'oauth'),
                'required' => (bool)($a['required'] ?? true),
            ];
        }
        return $out;
    }
}
