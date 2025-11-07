<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add only if column does not exist
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('language');
            }
            if (! Schema::hasColumn('users', 'notify_security')) {
                $table->boolean('notify_security')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_budget')) {
                $table->boolean('notify_budget')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_quota')) {
                $table->boolean('notify_quota')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_general')) {
                $table->boolean('notify_general')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_newsletter')) {
                $table->boolean('notify_newsletter')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop only if present (safe rollbacks)
            if (Schema::hasColumn('users', 'username'))         $table->dropUnique(['username']);
            if (Schema::hasColumn('users', 'username'))         $table->dropColumn('username');
            if (Schema::hasColumn('users', 'phone'))            $table->dropColumn('phone');
            if (Schema::hasColumn('users', 'gender'))           $table->dropColumn('gender');
            if (Schema::hasColumn('users', 'language'))         $table->dropColumn('language');
            if (Schema::hasColumn('users', 'avatar'))           $table->dropColumn('avatar');
            if (Schema::hasColumn('users', 'notify_security'))  $table->dropColumn('notify_security');
            if (Schema::hasColumn('users', 'notify_budget'))    $table->dropColumn('notify_budget');
            if (Schema::hasColumn('users', 'notify_quota'))     $table->dropColumn('notify_quota');
            if (Schema::hasColumn('users', 'notify_general'))   $table->dropColumn('notify_general');
            if (Schema::hasColumn('users', 'notify_newsletter'))$table->dropColumn('notify_newsletter');
        });
    }
};
