<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->foreignId('building_type_id')->constrained('building_definitions');
            $table->unsignedSmallInteger('x');
            $table->unsignedSmallInteger('y');
            $table->unsignedSmallInteger('rotation')->default(0);
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('state', 32)->default('constructing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finishes_at')->nullable();
            $table->timestamp('last_processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
