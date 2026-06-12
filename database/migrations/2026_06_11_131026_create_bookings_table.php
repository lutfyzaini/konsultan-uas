<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('availability_id')->constrained('availabilities');

            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');

            // ── STATUS LENGKAP SESUAI ALUR BISNIS ──
            $table->enum('status', [
                'pending_payment',    // slot locked, menunggu bayar (15 menit)
                'confirmed',          // sudah bayar, menunggu sesi dimulai
                'ongoing',            // sesi sedang berlangsung
                'pending_settlement', // sesi selesai, menunggu konfirmasi client
                'completed',          // dana sudah cair ke expert
                'cancelled',          // dibatalkan / payment expired
                'disputed',           // sedang dalam sengketa
            ])->default('pending_payment');

            $table->text('client_notes')->nullable();
            $table->decimal('total_price', 10, 2);

            // ── KOLOM WAKTU UNTUK LOGIKA OTOMATIS ──
            $table->timestamp('payment_deadline')->nullable(); // locked_at + 15 menit
            $table->timestamp('session_started_at')->nullable();
            $table->timestamp('session_ended_at')->nullable();  // untuk trigger auto-approve

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['expert_profile_id', 'status']);
            $table->index(['status', 'session_ended_at']); // untuk cron auto-approve
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};