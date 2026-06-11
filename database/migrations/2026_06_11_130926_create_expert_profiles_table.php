<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('title', 150)->nullable();
            $table->text('bio')->nullable();
            $table->string('location', 100)->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('hourly_rate', 10, 2);
            $table->boolean('is_online')->default(false);
            $table->enum('is_verified', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('total_sessions')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_profiles');
    }
};