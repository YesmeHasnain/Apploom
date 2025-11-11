<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_integrations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('api_name')->index();
            $t->text('token_enc')->nullable();
            $t->text('refresh_token_enc')->nullable();
            $t->timestamp('connected_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id','api_name']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_integrations'); }
};
