<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained('expert_profiles')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_skills');
    }
};