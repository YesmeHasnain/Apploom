<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('builds', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('project_id')->index();
            $t->string('status')->default('queued'); // queued|running|success|failed
            $t->unsignedTinyInteger('progress')->default(0);
            $t->json('artifacts')->nullable();       // { "repo_zip": "...", "preview_url": "..." }
            $t->text('logs')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('builds'); }
};
