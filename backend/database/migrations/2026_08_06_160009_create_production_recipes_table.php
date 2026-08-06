<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('input_resource_type', 32)->nullable();
            $table->unsignedInteger('input_amount')->nullable();
            $table->string('output_resource_type', 32);
            $table->unsignedInteger('output_amount');
            $table->unsignedInteger('duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_recipes');
    }
};
