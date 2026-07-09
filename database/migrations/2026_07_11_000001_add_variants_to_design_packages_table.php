<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fasa 11 — varian struktur (header/footer/card/divider) setiap pakej reka (§7 pelbagaian).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_packages', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('icon_style');
        });
    }

    public function down(): void
    {
        Schema::table('design_packages', function (Blueprint $table) {
            $table->dropColumn('variants');
        });
    }
};
