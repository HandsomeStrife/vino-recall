<?php

namespace Database\Factories;

use Domain\Admin\Models\Admin;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Models\DeckImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Deck\Models\DeckImport>
 */
class DeckImportFactory extends Factory
{
    protected $model = DeckImport::class;

    public function definition(): array
    {
        return [
            'user_id' => Admin::factory(),
            'deck_id' => null,
            'target_deck_id' => null,
            'filename' => fake()->word() . '.csv',
            'original_filename' => fake()->word() . '.csv',
            'file_path' => 'imports/' . fake()->uuid() . '.csv',
            'format' => ImportFormat::CSV->value,
            'status' => ImportStatus::PENDING->value,
            'imported_cards_count' => 0,
            'updated_cards_count' => 0,
            'skipped_rows' => 0,
            'total_rows' => 0,
            'error_message' => null,
            'validation_errors' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ImportStatus::COMPLETED->value,
                'imported_cards_count' => fake()->numberBetween(1, 100),
                'started_at' => now()->subMinutes(5),
                'completed_at' => now(),
            ];
        });
    }

    public function failed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ImportStatus::FAILED->value,
                'error_message' => 'Import failed: ' . fake()->sentence(),
                'started_at' => now()->subMinutes(5),
                'completed_at' => now(),
            ];
        });
    }

    public function processing(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ImportStatus::PROCESSING->value,
                'started_at' => now(),
            ];
        });
    }
}



