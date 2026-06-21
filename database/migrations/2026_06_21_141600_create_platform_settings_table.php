<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Seed default values
        DB::table('platform_settings')->insert([
            [
                'key' => 'platform_fee_percentage',
                'value' => '10',
                'label' => 'Platform Fee Percentage (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'top_rated_discount_percentage',
                'value' => '2',
                'label' => 'Top Rated Badge platforms fee discount (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_cancel_minutes',
                'value' => '15',
                'label' => 'Auto Cancel Pending Payment (Minutes)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'session_duration_hours',
                'value' => '1',
                'label' => 'Default Session Duration (Hours)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
