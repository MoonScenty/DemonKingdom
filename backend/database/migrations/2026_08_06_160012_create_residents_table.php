<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->foreignId('resident_type_id')->constrained('resident_definitions');
            $table->string('name');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('experience')->default(0);
            $table->unsignedTinyInteger('loyalty')->default(50);
            $table->string('health_state', 32)->default('healthy');
            $table->string('current_state', 32)->default('idle');
            $table->foreignId('assigned_building_id')->nullable()->constrained('buildings')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
