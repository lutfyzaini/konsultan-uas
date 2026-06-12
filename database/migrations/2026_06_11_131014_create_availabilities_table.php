<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();

            $table->enum('day_of_week', [
                'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'
            ]);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);

            // ── KOLOM KRITIS UNTUK MEKANISME LOCK ──
            $table->enum('status', ['available', 'locked', 'booked'])
                  ->default('available');
            $table->timestamp('locked_at')->nullable();  // kapan mulai dikunci
            $table->foreignId('locked_by')               // siapa yang mengunci (client)
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // index untuk cron job rilis slot expired & query kalender
            $table->index(['expert_profile_id', 'status']);
            $table->index(['status', 'locked_at']); // dipakai ReleaseExpiredSlots command
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};