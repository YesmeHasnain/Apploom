<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('app_databases')) {
            Schema::create('app_databases', function (Blueprint $table) {
                $table->id();
                // users table se link
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->string('name');                 // DB display name
                $table->string('engine');               // mysql|postgres|mongodb|redis|neo4j|riak
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->string('host')->nullable();
                $table->string('port')->nullable();

                $table->json('metadata')->nullable();   // AI schema, extra config, etc.
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_databases');
    }
};
