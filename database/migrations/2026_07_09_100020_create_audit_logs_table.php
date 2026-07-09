<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 audit_logs — jejak peristiwa keselamatan/operasi (§10 senarai peristiwa).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('actor_type', ['admin', 'pic', 'system']);
            $table->string('actor_id')->nullable();
            $table->string('action', 80)->index();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
