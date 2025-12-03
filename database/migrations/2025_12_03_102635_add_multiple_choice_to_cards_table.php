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
        Schema::table('cards', function (Blueprint $table) {
            $table->enum('card_type', ['traditional', 'multiple_choice'])->default('traditional')->after('deck_id');
            $table->json('answer_choices')->nullable()->after('answer');
            $table->integer('correct_answer_index')->nullable()->after('answer_choices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['card_type', 'answer_choices', 'correct_answer_index']);
        });
    }
};
