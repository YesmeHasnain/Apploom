<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('name');
            $t->string('type')->default('web');                 // web|mobile|fullstack
            $t->string('status')->default('draft');             // draft|preview|live
            $t->string('live_url')->nullable();
            $t->longText('project_json')->nullable();           // planner output
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('projects'); }
};
