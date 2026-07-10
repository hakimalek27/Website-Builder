<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §Fasa 13 — penanda penyedia "Jurutera Prompt" (Peringkat 1): jana prompt lengkap
// sebelum penyedia Default (Peringkat 2) menjana draf HTML. Satu sahaja (dikuatkuasa model).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->boolean('is_prompt_engineer')->default(false)->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn('is_prompt_engineer');
        });
    }
};
