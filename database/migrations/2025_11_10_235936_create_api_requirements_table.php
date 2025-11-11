<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('api_requirements', function (Blueprint $t) {
            $t->id();
            $t->uuid('project_id')->index();
            $t->string('api_name');             // google_maps, stripe, firebase
            $t->string('auth_type');            // oauth|key
            $t->string('status')->default('pending'); // pending|connected|auto_created|test
            $t->timestamps();
            $t->unique(['project_id','api_name']);
        });
    }
    public function down(): void { Schema::dropIfExists('api_requirements'); }
};
