<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->onDelete('cascade');
            $table->string('invoice', 100)->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_commission', 10, 2)->default(0.00);
            $table->decimal('expert_earnings', 10, 2)->default(0.00);
            $table->unsignedTinyInteger('commission_rate')->default(0);
            $table->enum('method', ['bank', 'ewallet', 'credit_card', 'wallet']);
            $table->enum('status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('settled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};