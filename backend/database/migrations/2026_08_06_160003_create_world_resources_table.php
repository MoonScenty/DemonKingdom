<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->string('resource_type', 32);
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('capacity');
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['game_world_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_resources');
    }
};
