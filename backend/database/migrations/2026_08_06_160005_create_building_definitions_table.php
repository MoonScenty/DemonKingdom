<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('width')->default(1);
            $table->unsignedTinyInteger('height')->default(1);
            $table->unsignedTinyInteger('max_level')->default(1);
            $table->unsignedInteger('base_build_time');
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_definitions');
    }
};
