<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_builds', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('user_id')->index();
            $t->text('prompt');
            $t->string('model')->nullable();
            $t->string('visibility')->default('private');
            $t->json('targets')->nullable();
            $t->string('status')->default('queued'); // queued|running|success|failed
            $t->unsignedTinyInteger('progress')->default(0);
            $t->json('artifacts')->nullable();      // {"preview_url": "..."}
            $t->text('error')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('app_builds');
    }
};
