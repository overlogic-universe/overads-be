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
        Schema::create('ad_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_id')->constrained('ads')->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook']);
            $table->timestamp('scheduled_at');
            $table->enum('status', [
                'pending',
                'processing',
                'posted',
                'failed',
            ])->default('pending');

            $table->json('platform_response')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_schedules');
    }
};
