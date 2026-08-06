<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('world_areas')->cascadeOnDelete();
            $table->unsignedSmallInteger('x');
            $table->unsignedSmallInteger('y');
            $table->string('terrain_type', 32);
            $table->boolean('is_buildable')->default(true);

            $table->unique(['game_world_id', 'x', 'y']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_tiles');
    }
};
