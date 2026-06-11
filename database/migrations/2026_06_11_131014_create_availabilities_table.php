<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained('expert_profiles')->onDelete('cascade');
            $table->enum('day_of_week', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['available', 'locked', 'booked'])->default('available');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');

            // Komposit Indeks untuk performa query pencarian dan cron job lock cleaner
            $table->index(['expert_profile_id', 'status', 'locked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};