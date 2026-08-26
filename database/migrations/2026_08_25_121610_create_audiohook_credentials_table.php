<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiohook_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('api_key');
            $table->text('client_secret');
            $table->timestamps();

            $table->unique('team_id');
            $table->unique('api_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiohook_credentials');
    }
};
