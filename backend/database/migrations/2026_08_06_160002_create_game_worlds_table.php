<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_worlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('city_level')->default(1);
            $table->unsignedInteger('population')->default(0);
            $table->string('current_era')->default('foundation');
            $table->unsignedBigInteger('revision')->default(0);
            $table->timestamp('last_processed_at')->useCurrent();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_worlds');
    }
};
