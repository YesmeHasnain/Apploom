<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AIBuilderController extends Controller
{
    public function index()
    {
        return view('services.ai_builder');
    }

    public function plan(Request $req)
    {
        $data = $req->validate([
            'prompt' => 'required|string|min:8',
            'model'  => 'nullable|string',
        ]);

        // TODO: Yahan aap Gemini/GPT se actual planning call kar sakte ho.
        // Filhaal demo plan:
        $plan = [
            'meta' => ['name' => 'App from prompt', 'model' => $data['model'] ?? 'gemini-1.5-flash'],
            'pages' => [
                ['path' => '/', 'name'=>'Home', 'sections'=>[
                    ['type'=>'Hero', 'props'=>['title'=>$data['prompt'], 'cta'=>'Get Started']],
                    ['type'=>'List', 'props'=>['items'=>['Feature A','Feature B','Feature C']]],
                ]],
            ],
            'required_apis' => [],
        ];

        return response()->json([
            'success' => true,
            'plan'    => $plan,
        ]);
    }

    public function start(Request $req)
    {
        $data = $req->validate([
            'prompt'   => 'required|string|min:8',
            'model'    => 'nullable|string',
            'plan_json'=> 'nullable',
        ]);

        $id = (string) Str::uuid();
        Cache::put("ai_build:$id", [
            'prompt' => $data['prompt'],
            'model'  => $data['model'] ?? 'gemini-1.5-flash',
            'plan'   => $data['plan_json'] ?? null,
        ], now()->addMinutes(30));

        // Same-page preview uses this URL in an iframe
        $previewUrl = route('ai.builder.preview.live', ['id' => $id]);

        return response()->json([
            'success'     => true,
            'build_id'    => $id,
            'preview_url' => $previewUrl,
        ]);
    }

    public function preview(string $id)
    {
        $payload = Cache::get("ai_build:$id");
        if (!$payload) {
            return response('<h3 style="font-family:sans-serif">Preview expired.</h3>', 410);
        }

        $prompt = e($payload['prompt'] ?? 'Preview');
        // Minimal dynamic preview that uses the prompt
        $html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Preview – {$prompt}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{--c:#00c2d7}
  *{box-sizing:border-box} html,body{margin:0;height:100%}
  body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0b1220;color:#d7e1ff}
  .bar{padding:12px 16px;background:#0f172a;border-bottom:1px solid #1e293b;display:flex;align-items:center;gap:8px}
  .dot{width:10px;height:10px;border-radius:50%;background:var(--c)}
  .title{font-weight:700}
  .wrap{padding:20px}
  .card{background:#0f172a;border:1px solid #1e293b;border-radius:14px;padding:18px}
  input,button,textarea{font:inherit}
  input,textarea{background:#0b1220;border:1px solid #1e293b;border-radius:10px;color:#d7e1ff;padding:10px;width:100%}
  button{background:var(--c);border:0;color:#0b1220;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}
  .grid{display:grid;grid-template-columns:1fr;gap:12px}
  @media(min-width:700px){.grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
  <div class="bar"><div class="dot"></div><div class="title">Live Preview</div></div>
  <div class="wrap">
    <div class="card grid">
      <div>
        <h3 style="margin:0 0 8px">Prompt</h3>
        <input value="{$prompt}" readonly>
      </div>
      <div>
        <h3 style="margin:0 0 8px">Example Form</h3>
        <div class="grid" style="grid-template-columns:1fr auto">
          <input placeholder="Title">
          <button>Add</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        return response($html);
    }

    // Optional: webhook etc.
    public function webhook(Request $req)
    {
        return response()->json(['ok'=>true]);
    }
}
