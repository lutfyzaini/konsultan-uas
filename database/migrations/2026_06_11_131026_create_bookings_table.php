<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('expert_profile_id')->constrained('expert_profiles')->onDelete('cascade');
            $table->foreignId('availability_id')->constrained('availabilities')->onDelete('restrict');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'pending_settlement', 'disputed'])->default('pending');
            $table->text('client_notes')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};