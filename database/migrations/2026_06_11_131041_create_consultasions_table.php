<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultasions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->onDelete('cascade');
            $table->enum('type', ['chat', 'video_call'])->default('chat');
            $table->text('summary')->nullable();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultasions');
    }
};