<?php

namespace App\Http\Controllers;

use App\Models\{Project, ApiRequirement, UserIntegration, Build, Subscription};
use App\Services\{GeminiScaffold, ApiDetector, CodeGenerator, SandboxBuilder, TokenVault, GoLiveValidator};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log, Storage, DB};

class AIFlowController extends Controller
{
    public function builderPage() { return view('services.ai_builder'); }

    /** Step 1: Planner */
    public function plan(Request $r, GeminiScaffold $gemini, ApiDetector $detector)
    {
        $data = $r->validate([
            'prompt' => 'required|string|max:8000',
            'model'  => 'nullable|string',
            'type'   => 'nullable|string',
        ]);

        $plan = $gemini->plan($data['prompt'], $data['model'] ?? null);
        $apis = $detector->detect($plan);

        DB::beginTransaction();
        try {
            $p = Project::create([
                'user_id'      => Auth::id(),
                'name'         => $plan['meta']['name'] ?? 'AI Project',
                'type'         => $data['type'] ?? 'web',
                'status'       => 'draft',
                'project_json' => json_encode($plan),
            ]);

            foreach ($apis as $a) {
                ApiRequirement::updateOrCreate(
                    ['project_id'=>$p->id,'api_name'=>$a['api']],
                    ['auth_type'=>$a['auth'],'status'=>'pending']
                );
            }

            DB::commit();
            return response()->json(['ok'=>true,'project_id'=>$p->id,'project'=>$plan,'apis'=>$apis]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Planner save failed', ['e'=>$e->getMessage()]);
            return response()->json(['ok'=>false,'error'=>$e->getMessage()], 422);
        }
    }

    /** List integrations for a project */
    public function listApis(string $projectId)
    {
        $rows = ApiRequirement::where('project_id',$projectId)->get();
        return response()->json(['ok'=>true,'items'=>$rows]);
    }

    /** Step 2: Integration choice (Connect / Auto-create / Test) */
    public function setApi(Request $r, TokenVault $vault, string $projectId, string $apiName)
    {
        $data = $r->validate([
            'mode'   => 'required|string|in:connect,auto_create,test',
            'token'  => 'nullable|string',
            'refresh_token' => 'nullable|string',
        ]);
        $api = ApiRequirement::where('project_id',$projectId)->where('api_name',$apiName)->firstOrFail();

        if ($data['mode']==='connect') {
            UserIntegration::updateOrCreate(
                ['user_id'=>Auth::id(),'api_name'=>$apiName],
                [
                    'token_enc' => $vault->encrypt($data['token'] ?? 'DUMMY_CONNECTED_TOKEN'),
                    'refresh_token_enc' => $vault->encrypt($data['refresh_token'] ?? null),
                    'connected_at' => now(),
                ]
            );
            $api->status = 'connected';
        } elseif ($data['mode']==='auto_create') {
            // Stub: pretend we called provider API and got keys
            UserIntegration::updateOrCreate(
                ['user_id'=>Auth::id(),'api_name'=>$apiName],
                [
                    'token_enc' => $vault->encrypt('AUTO_CREATED_TOKEN_'.$apiName),
                    'connected_at' => now(),
                ]
            );
            $api->status = 'auto_created';
        } else { // test
            $api->status = 'test';
        }
        $api->save();

        return response()->json(['ok'=>true,'item'=>$api]);
    }

    /** Step 3/4: Code Generate + Sandbox Build */
    public function startBuild(Request $r, GeminiScaffold $gemini, SandboxBuilder $sandbox, CodeGenerator $gen)
    {
        $data = $r->validate([
            'project_id' => 'required|uuid',
            'model'      => 'nullable|string',
        ]);
        $p = Project::where('id',$data['project_id'])->firstOrFail();
        $plan = json_decode($p->project_json ?? '{}', true);

        $b = Build::create([
            'project_id'=>$p->id,
            'status'=>'running',
            'progress'=>10,
            'logs'=>'Build started'."\n",
        ]);

        try {
            // Preview HTML from Gemini
            $inner = $gemini->previewInnerHtml("{$plan['meta']['name']}", $data['model'] ?? null);
            $b->progress = 55; $b->logs .= "Preview generated\n"; $b->save();

            // Save preview
            $previewUrl = $sandbox->savePreview($b->id, $plan['meta']['name'] ?? 'Preview', $inner);
            $b->progress = 75; $b->logs .= "Preview saved\n"; $b->save();

            // Create repo zip
            $zipRel = $gen->makeNextRepoZip($b->id, $plan);
            $b->progress = 95; $b->logs .= "Repo zipped\n"; $b->save();

            $b->status = 'success';
            $b->progress = 100;
            $b->artifacts = ['repo_zip'=>url(Storage::url($zipRel)), 'preview_url'=>$previewUrl];
            $b->logs .= "Done\n";
            $b->save();

            $p->status = 'preview'; $p->save();

            return response()->json(['ok'=>true,'build_id'=>$b->id,'artifacts'=>$b->artifacts]);
        } catch (\Throwable $e) {
            $b->status = 'failed'; $b->logs .= "Error: ".$e->getMessage(); $b->save();
            return response()->json(['ok'=>false,'error'=>$e->getMessage()], 422);
        }
    }

    /** Preview file */
    public function preview(string $id)
    {
        $rel = "previews/{$id}.html";
        if (!Storage::exists($rel)) abort(404);
        return response(Storage::get($rel))->header('Content-Type','text/html; charset=utf-8');
    }

    /** Go Live (validator) */
    public function goLive(Request $r, GoLiveValidator $validator)
    {
        $data = $r->validate(['project_id'=>'required|uuid']);
        $p = Project::where('id',$data['project_id'])->firstOrFail();
        $errors = $validator->validate($p);
        if ($errors) return response()->json(['ok'=>false,'errors'=>$errors], 422);

        // stub deploy → set live_url
        $p->status = 'live';
        $p->live_url = 'https://example.live/'.$p->id; // TODO: replace with real deployer
        $p->save();

        return response()->json(['ok'=>true,'live_url'=>$p->live_url]);
    }

    /** License check */
    public function licenseCheck(Request $r)
    {
        $data = $r->validate(['app_id'=>'required|uuid','user_id'=>'nullable|integer']);
        $p = Project::where('id',$data['app_id'])->first();
        if (!$p) return response()->json(['status'=>'inactive']);
        $sub = Subscription::where('user_id',$p->user_id)->latest()->first();
        $active = $sub && $sub->status === 'active';
        return response()->json(['status'=>$active ? 'active' : 'inactive']);
    }

    /** Build list for Dashboard */
    public function builds(string $projectId)
    {
        $items = Build::where('project_id',$projectId)->latest()->get();
        return response()->json(['ok'=>true,'items'=>$items]);
    }
}
