<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->string('expedition_type', 64);
            $table->string('status', 32)->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('returns_at');
            $table->json('result_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};
