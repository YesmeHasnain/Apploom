<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // kis provider se login hua
            $table->string('provider')->nullable()->after('remember_token');
            $table->string('provider_id')->nullable()->after('provider');   // e.g. Google UID
            $table->string('provider_token', 1024)->nullable()->after('provider_id'); // access token (optional)

            // indexes
            $table->index(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'provider_token']);
        });
    }
};
