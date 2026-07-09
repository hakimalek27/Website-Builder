<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 handover_exports — rekod pakej serahan ZIP dieksport (§14).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_exports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('approval_id')->constrained()->cascadeOnDelete();
            $table->string('zip_path');
            $table->json('manifest');
            $table->timestamp('exported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_exports');
    }
};
