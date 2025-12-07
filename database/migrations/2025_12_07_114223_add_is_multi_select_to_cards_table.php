<?php

declare(strict_types=1);

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
            $table->boolean('is_multi_select')->default(false)->after('correct_answer_indices');
        });

        // Set existing cards with multiple correct answers to have is_multi_select = true
        \Domain\Card\Models\Card::query()
            ->whereRaw('JSON_LENGTH(correct_answer_indices) > 1')
            ->update(['is_multi_select' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('is_multi_select');
        });
    }
};
