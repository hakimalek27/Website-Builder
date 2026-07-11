<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §Fasa 16 — katalog templat rujukan (galeri wizard mod 'template'). Bukan data PIC; metadata terkurasi admin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_catalog', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 120);
            $table->string('source', 20)->default('themeforest'); // themeforest|laman|lain (kuatkuasa di borang/seeder)
            $table->string('url', 500);
            $table->string('demo_url', 500)->nullable();
            $table->json('categories')->nullable();   // ["masjid","ngo"]
            $table->json('style_tags')->nullable();    // ["moden","gelap",...]
            $table->string('thumbnail_path')->nullable();
            $table->json('screenshots')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_catalog');
    }
};
