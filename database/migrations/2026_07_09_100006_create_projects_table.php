<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 projects — satu masjid = satu project. Status enum §4.2.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('mosque_name');
            $table->string('short_name')->nullable();
            $table->enum('tier', ['surau_ringkas', 'masjid_kariah', 'masjid_besar']);
            $table->boolean('is_gov')->default(false);
            $table->string('state');
            $table->char('jakim_zone', 5)->index();
            $table->enum('status', [
                'invited', 'in_progress', 'submitted', 'draft_ready', 'approved',
                'handover_exported', 'in_build', 'in_review', 'live', 'archived',
                'cancelled', 'expired',
            ])->default('invited')->index();
            $table->unsignedTinyInteger('quota_ai_total')->default(3);
            $table->unsignedTinyInteger('quota_ai_used')->default(0);
            $table->unsignedTinyInteger('quota_design_used')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
