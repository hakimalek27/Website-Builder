<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 leads — borang Daftar Minat awam.
// project_id: FK bulat ke projects ditambah dalam migrasi 100007 (nullOnDelete).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('mosque_name');
            $table->string('state');
            $table->string('pic_name');
            $table->string('pic_phone');
            $table->string('pic_email')->nullable();
            $table->string('current_website')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'rejected'])->default('new')->index();
            $table->text('rejected_reason')->nullable();
            $table->ulid('project_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
