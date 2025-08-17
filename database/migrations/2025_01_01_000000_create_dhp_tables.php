<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dhp_rules', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();     // DUE_OVER_MAX, DUP_CHARGES
            $t->string('name');
            $t->json('options')->nullable();  // {"default_due":70,"multiplier":2}
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });

        Schema::create('dhp_results', function (Blueprint $t) {
            $t->id();
            $t->string('rule_code')->index();
            $t->string('entity_type');        // 'member'
            $t->string('entity_id');
            $t->string('period_key')->nullable(); // '2025-08'
            $t->json('payload')->nullable();  // details for explainability
            $t->string('hash')->unique();     // dedupe key
            $t->enum('status', ['open','resolved'])->default('open')->index();
            $t->timestamp('detected_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('dhp_results');
        Schema::dropIfExists('dhp_rules');
    }
};
