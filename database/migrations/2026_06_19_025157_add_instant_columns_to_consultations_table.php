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
        Schema::table('consultations', function (Blueprint $table) {
            $table->enum('consultation_type', ['scheduled', 'instant'])->nullable()->after('status');
            $table->timestamp('presence_deadline')->nullable()->after('ended_at');
            $table->timestamp('absence_resolved_at')->nullable()->after('presence_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['consultation_type', 'presence_deadline', 'absence_resolved_at']);
        });
    }
};
