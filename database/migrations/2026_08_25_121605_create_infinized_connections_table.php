<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infinized_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('infinized_organization_id')->nullable();
            $table->string('infinized_organization_slug');
            $table->string('infinized_organization_name')->nullable();
            $table->text('api_key');
            $table->string('mode');
            $table->boolean('is_partner_child')->default(false);
            $table->timestamp('last_health_check_at')->nullable();
            $table->json('last_health_payload')->nullable();
            $table->timestamps();

            $table->unique('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infinized_connections');
    }
};
