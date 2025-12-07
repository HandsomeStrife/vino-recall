<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('shortcode', 6)->nullable()->unique()->after('id');
        });

        // Generate shortcodes for existing cards
        Card::whereNull('shortcode')->each(function (Card $card) {
            $card->shortcode = $this->generateUniqueShortcode();
            $card->save();
        });

        // Make column not nullable after populating
        Schema::table('cards', function (Blueprint $table) {
            $table->string('shortcode', 6)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('shortcode');
        });
    }

    /**
     * Generate a unique 6-character shortcode.
     */
    private function generateUniqueShortcode(): string
    {
        do {
            $shortcode = strtoupper(Str::random(6));
        } while (Card::where('shortcode', $shortcode)->exists());

        return $shortcode;
    }
};
