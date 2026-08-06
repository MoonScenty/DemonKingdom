<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_level_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_type_id')->constrained('building_definitions')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->json('build_cost');
            $table->unsignedInteger('production_time')->nullable();
            $table->unsignedInteger('production_amount')->nullable();
            $table->unsignedInteger('storage_capacity')->nullable();
            $table->unsignedTinyInteger('worker_capacity')->default(0);

            $table->unique(['building_type_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_level_definitions');
    }
};
