<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 tweak_requests — permohonan tweak kandungan/reka bentuk PIC.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tweak_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('base_generation_id')->constrained('generations')->cascadeOnDelete();
            $table->json('categories');
            $table->text('message');
            $table->foreignUlid('result_generation_id')->nullable()->constrained('generations')->nullOnDelete();
            $table->enum('status', ['pending', 'applied', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweak_requests');
    }
};
