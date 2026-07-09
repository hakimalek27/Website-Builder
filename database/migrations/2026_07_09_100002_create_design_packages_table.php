<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 design_packages — 5 pakej reka bentuk (§7.2).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_packages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->json('tokens');
            $table->json('fonts');
            $table->string('layout');
            $table->json('icon_style');
            $table->json('preview_meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_packages');
    }
};
