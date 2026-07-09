<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 verse_library — teks Arab HANYA dari sini (§9.1/§9.2), direkod manusia.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verse_library', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('arabic_text');
            $table->text('translation_bm');
            $table->string('source_label');
            $table->string('verified_by');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verse_library');
    }
};
