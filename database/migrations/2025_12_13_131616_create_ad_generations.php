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
        Schema::create('ad_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ads_id')->constrained('ads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video'])->default('image');
            $table->text('prompt');
            $table->text('caption');
            $table->enum('status', [
                'pending',
                'processing',
                'generated',
                'failed',
                'uploaded',
            ])->default('pending');

            $table->string('result_media')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_generations');
    }
};
