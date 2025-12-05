<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the new column
        Schema::table('cards', function (Blueprint $table) {
            $table->json('correct_answer_indices')->nullable()->after('answer_choices');
        });

        // Migrate existing data: convert single index to array format
        DB::table('cards')
            ->whereNotNull('correct_answer_index')
            ->orderBy('id')
            ->chunk(100, function ($cards) {
                foreach ($cards as $card) {
                    DB::table('cards')
                        ->where('id', $card->id)
                        ->update([
                            'correct_answer_indices' => json_encode([$card->correct_answer_index]),
                        ]);
                }
            });

        // Drop the old column
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('correct_answer_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old column
        Schema::table('cards', function (Blueprint $table) {
            $table->integer('correct_answer_index')->nullable()->after('answer_choices');
        });

        // Migrate data back: take first index from array
        DB::table('cards')
            ->whereNotNull('correct_answer_indices')
            ->orderBy('id')
            ->chunk(100, function ($cards) {
                foreach ($cards as $card) {
                    $indices = json_decode($card->correct_answer_indices, true);
                    $firstIndex = is_array($indices) && count($indices) > 0 ? $indices[0] : null;

                    DB::table('cards')
                        ->where('id', $card->id)
                        ->update([
                            'correct_answer_index' => $firstIndex,
                        ]);
                }
            });

        // Drop the new column
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('correct_answer_indices');
        });
    }
};
