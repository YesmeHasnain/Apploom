<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SandboxBuilder
{
    /** Save full HTML and return preview URL */
    public function savePreview(string $buildId, string $title, string $innerHtml): string
    {
        $full = $this->wrap($title, $innerHtml);
        $rel  = "previews/{$buildId}.html";
        Storage::put($rel, $full);
        return route('ai.builder.preview.live', ['id' => $buildId]);
    }

    private function wrap(string $title, string $inner): string
    {
        $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!doctype html><html lang="en"><head><meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Preview — {$t}</title>
<style>
:root{--bg:#0b1724;--card:#101f31;--text:#e8f1ff;--muted:#9bb3cc;--accent:#35c3ff}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:500 15px/1.5 system-ui,Segoe UI,Roboto,Arial}
.bar{display:flex;align-items:center;gap:10px;padding:14px 18px;background:linear-gradient(90deg,#0e2032,#0d253a)}
.dot{width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 24px var(--accent)}
.wrap{padding:18px}.card{background:var(--card);border:1px solid #1b3550;border-radius:16px;padding:18px}
</style></head>
<body><div class="bar"><span class="dot"></span><strong>Live Preview</strong></div>
<div class="wrap"><div class="card">{$inner}</div></div></body></html>
HTML;
    }
}
