<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 assets — fail dimuat naik (nama fail = ULID; §11.4).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', [
                'logo', 'hero', 'gallery', 'qr', 'doc',
                'committee_photo', 'facility_photo', 'perutusan_photo',
            ]);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime');
            $table->unsignedInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('caption')->nullable();
            $table->json('meta')->nullable();
            $table->smallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
