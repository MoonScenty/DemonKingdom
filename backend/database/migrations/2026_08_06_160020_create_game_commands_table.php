<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('command_id');
            $table->string('command_type', 64);
            $table->unsignedBigInteger('base_revision');
            $table->string('status', 32)->default('pending');
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['game_world_id', 'command_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_commands');
    }
};
