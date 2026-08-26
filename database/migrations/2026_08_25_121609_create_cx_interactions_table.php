<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cx_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cx_agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('state')->default('active');
            $table->string('genesys_conversation_id')->nullable();
            $table->string('genesys_session_id')->nullable();
            $table->unsignedBigInteger('infinized_conversation_id')->nullable();
            $table->string('ani')->nullable();
            $table->string('dnis')->nullable();
            $table->json('input_variables')->nullable();
            $table->json('output_variables')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'state']);
            $table->index('genesys_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cx_interactions');
    }
};
