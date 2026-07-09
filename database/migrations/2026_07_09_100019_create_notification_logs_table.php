<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// §10 notification_logs — jejak setiap notifikasi (mail/whatsapp), §13.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 60);
            $table->enum('channel', ['mail', 'whatsapp']);
            $table->string('recipient');
            $table->json('payload');
            $table->enum('status', ['sent', 'failed']);
            $table->text('error')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
