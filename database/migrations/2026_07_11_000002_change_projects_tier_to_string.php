<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fasa 11 NGO — tukar projects.tier (enum 3 nilai) → string(40) supaya App\Enums\Tier
// jadi satu-satunya sumber kebenaran (tambah ngo_komuniti/ngo_penuh tanpa ubah skema DB).
// SQLite: table rebuild. MySQL: MODIFY COLUMN VARCHAR(40) — nilai sedia ada dikekalkan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('tier', 40)->change();
        });
    }

    public function down(): void
    {
        // Menyempitkan semula ke enum adalah memusnahkan (nilai NGO hilang) — tiada auto-revert.
    }
};
