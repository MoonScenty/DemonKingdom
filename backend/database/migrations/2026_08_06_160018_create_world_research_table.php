<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->string('research_type', 64);
            $table->unsignedTinyInteger('level')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finishes_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_research');
    }
};
