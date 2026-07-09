<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 project_pages — struktur halaman dipilih (L3 + matriks preset §6.11).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->string('page_key', 40);
            $table->boolean('enabled')->default(false);
            $table->string('custom_name')->nullable();
            $table->smallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'page_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pages');
    }
};
