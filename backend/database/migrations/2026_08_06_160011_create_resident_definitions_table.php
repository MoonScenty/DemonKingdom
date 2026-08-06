<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('race', 32);
            $table->unsignedSmallInteger('base_production')->default(0);
            $table->unsignedSmallInteger('base_construction')->default(0);
            $table->unsignedSmallInteger('base_research')->default(0);
            $table->unsignedSmallInteger('base_combat')->default(0);
            $table->unsignedSmallInteger('base_movement')->default(0);
            $table->unsignedSmallInteger('base_charm')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_definitions');
    }
};
