<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_event_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->string('choice_code', 64);
            $table->timestamp('selected_at')->nullable();
            $table->json('result_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_event_choices');
    }
};
