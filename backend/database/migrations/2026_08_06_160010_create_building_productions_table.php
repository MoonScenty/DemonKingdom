<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained('production_recipes');
            $table->unsignedInteger('stored_amount')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_processed_at')->nullable();
            $table->timestamp('next_completion_at')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_productions');
    }
};
