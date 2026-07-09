<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 ai_providers — konfigurasi AI Sistem. api_key = encrypted cast (§11.5).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->enum('driver', ['anthropic', 'openai_compatible']);
            $table->string('base_url')->nullable();
            $table->text('api_key'); // disulitkan pada peringkat model (encrypted cast)
            $table->string('model');
            $table->unsignedInteger('max_tokens')->default(3000);
            $table->decimal('temperature', 2, 1)->default(0.7);
            $table->unsignedInteger('timeout_s')->default(90);
            $table->json('meta')->nullable(); // kadar harga §8.8
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
