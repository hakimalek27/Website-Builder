<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 generations — setiap panggilan jana/tweak/render. Ledger kos §8.8.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('ai_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['initial', 'content_tweak', 'design_render']);
            $table->enum('status', ['queued', 'processing', 'succeeded', 'failed'])->index();
            $table->unsignedTinyInteger('progress_step')->default(0);
            $table->json('input_snapshot')->nullable();
            $table->json('output_json')->nullable();
            $table->string('rendered_path')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->decimal('cost_estimate', 8, 4)->default(0);
            $table->unsignedTinyInteger('attempt')->default(0);
            $table->enum('created_by', ['pic', 'admin']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
