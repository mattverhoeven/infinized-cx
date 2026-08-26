<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cx_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('infinized_agent_id');
            $table->string('connector_id')->unique();
            $table->string('name');
            $table->json('channels')->nullable();
            $table->json('voice_settings')->nullable();
            $table->json('escalation_settings')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['team_id', 'infinized_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cx_agents');
    }
};
