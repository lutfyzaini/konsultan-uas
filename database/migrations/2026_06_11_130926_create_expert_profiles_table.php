<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expert_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 150)->nullable();
            $table->text('bio')->nullable();
            $table->string('location', 100)->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('hourly_rate', 10, 2);

            // kolom untuk sistem
            $table->boolean('is_online')->default(false);
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])
                  ->default('pending');
            $table->integer('total_sessions')->default(0); // untuk leveling komisi
            $table->decimal('average_rating', 3, 2)->default(0.00);

            // komisi level — di-update otomatis saat total_sessions berubah
            $table->enum('commission_level', ['newbie', 'pro', 'master'])
                  ->default('newbie');

            $table->timestamps();

            // index untuk query discovery (filter + sort)
            $table->index(['verification_status', 'is_online']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_profiles');
    }
};