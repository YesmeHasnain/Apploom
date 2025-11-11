<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('plan')->default('basic');
            $t->string('status')->default('active');  // active|past_due|canceled
            $t->timestamp('current_period_end')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
