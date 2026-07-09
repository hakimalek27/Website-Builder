<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// §10 leads.project_id FK → projects (nullOnDelete). FK bulat leads↔projects.
// Nota (R1): SQLite (dev sahaja) tidak menyokong ALTER TABLE ADD FOREIGN KEY —
// kolum kekal ulid berindeks tanpa penguatkuasaan FK peringkat DB di dev; MySQL
// (produksi) mendapat FK penuh. Hubungan Eloquent identik pada kedua-dua.
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
    }
};
