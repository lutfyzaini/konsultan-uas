<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            // hubungkan satu-ke-satu (- users.id)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('avatar_url', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};