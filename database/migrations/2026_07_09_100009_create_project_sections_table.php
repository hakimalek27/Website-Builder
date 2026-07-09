<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 project_sections — jawapan wizard per langkah (upsert project_id+section_key).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->string('section_key', 20);
            $table->json('data');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_sections');
    }
};
