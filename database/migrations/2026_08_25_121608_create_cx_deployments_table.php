<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cx_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cx_agent_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('genesys_integration_id')->nullable();
            $table->string('genesys_integration_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_probe_at')->nullable();
            $table->string('last_probe_status')->nullable();
            $table->timestamps();

            $table->unique(['cx_agent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cx_deployments');
    }
};
