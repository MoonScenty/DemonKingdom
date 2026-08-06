<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained('quests');
            $table->string('status', 32)->default('available');
            $table->unsignedInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();

            $table->unique(['game_world_id', 'quest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_quests');
    }
};
