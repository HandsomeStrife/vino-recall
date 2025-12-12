<?php

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
        Schema::table('decks', function (Blueprint $table) {
            $table->string('identifier', 8)->nullable()->after('id');
        });

        // Generate identifiers for existing decks
        $decks = \Domain\Deck\Models\Deck::whereNull('identifier')->get();
        foreach ($decks as $deck) {
            $deck->identifier = $this->generateUniqueIdentifier();
            $deck->save();
        }

        // Now make the identifier column non-nullable and unique
        Schema::table('decks', function (Blueprint $table) {
            $table->string('identifier', 8)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->dropColumn('identifier');
        });
    }

    private function generateUniqueIdentifier(): string
    {
        do {
            $identifier = strtoupper(Str::random(8));
        } while (\Domain\Deck\Models\Deck::where('identifier', $identifier)->exists());

        return $identifier;
    }
};
