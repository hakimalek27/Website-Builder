<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 jakim_zones — 59 zon JAKIM (§16.A). Kod = kritikal; label = paparan UI.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jakim_zones', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('code', 5)->unique();
            $table->string('state')->index();
            $table->string('districts_label');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jakim_zones');
    }
};
