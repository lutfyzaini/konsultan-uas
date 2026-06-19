<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expert_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('institution_name');
            $table->string('degree');
            $table->string('field_of_study');
            $table->integer('start_year');
            $table->integer('end_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_education');
    }
};
