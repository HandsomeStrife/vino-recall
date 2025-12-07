<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Restructures the SRS system to use WaniKani-style discrete stages.
     * This is a DESTRUCTIVE migration - all existing review data will be wiped.
     */
    public function up(): void
    {
        // Drop the old card_reviews table and recreate with new schema
        Schema::dropIfExists('card_reviews');

        Schema::create('card_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('srs_stage')->default(0);
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'card_id']);
            $table->index(['user_id', 'next_review_at']);
            $table->index(['user_id', 'srs_stage']);
        });

        // Create new review_history table for tracking all review events
        Schema::create('review_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->boolean('is_correct');
            $table->unsignedTinyInteger('previous_stage');
            $table->unsignedTinyInteger('new_stage');
            $table->boolean('is_practice')->default(false);
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->index(['user_id', 'reviewed_at']);
            $table->index(['user_id', 'is_correct']);
            $table->index(['card_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_history');
        Schema::dropIfExists('card_reviews');

        // Recreate original card_reviews table structure
        Schema::create('card_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->string('rating');
            $table->boolean('is_correct')->nullable();
            $table->boolean('is_practice')->default(false);
            $table->text('selected_answer')->nullable();
            $table->timestamp('next_review_at');
            $table->decimal('ease_factor', 5, 2)->default(2.5);
            $table->timestamps();

            $table->unique(['user_id', 'card_id']);
        });
    }
};
