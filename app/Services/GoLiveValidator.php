<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Subscription;

class GoLiveValidator
{
    public function validate(Project $p): array
    {
        $errors = [];

        // 1) Required APIs must be connected (not test/pending)
        foreach ($p->apiRequirements as $req) {
            if (!in_array($req->status, ['connected','auto_created'])) {
                $errors[] = "API {$req->api_name} not ready (status={$req->status}).";
            }
        }

        // 2) Subscription active
        $sub = Subscription::where('user_id',$p->user_id)->latest()->first();
        if (!$sub || $sub->status !== 'active') {
            $errors[] = "Subscription is not active.";
        }

        // 3) Minimal smoke test stub (could be replaced by real checks)
        // … add more checks later …

        return $errors;
    }
}
