<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 approvals — snapshot BEKU kelulusan (spec penuh + draf + identiti + IP).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUlid('generation_id')->constrained('generations')->cascadeOnDelete();
            $table->json('snapshot');
            $table->string('pic_name');
            $table->string('pic_position');
            $table->string('pic_phone');
            $table->boolean('consent_pdpa');
            $table->boolean('declare_authority');
            $table->string('ip', 45);
            $table->text('user_agent');
            $table->timestamp('approved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
