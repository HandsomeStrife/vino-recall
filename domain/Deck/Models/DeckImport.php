<?php

declare(strict_types=1);

namespace Domain\Deck\Models;

use Database\Factories\DeckImportFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckImport extends Model
{
    use HasFactory;

    protected static function newFactory(): DeckImportFactory
    {
        return DeckImportFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'deck_id',
        'filename',
        'original_filename',
        'file_path',
        'format',
        'status',
        'imported_cards_count',
        'updated_cards_count',
        'total_rows',
        'skipped_rows',
        'error_message',
        'validation_errors',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'format' => \Domain\Deck\Enums\ImportFormat::class,
            'status' => \Domain\Deck\Enums\ImportStatus::class,
            'validation_errors' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function isProcessing(): bool
    {
        return $this->status === \Domain\Deck\Enums\ImportStatus::PROCESSING;
    }

    public function isPending(): bool
    {
        return $this->status === \Domain\Deck\Enums\ImportStatus::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === \Domain\Deck\Enums\ImportStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === \Domain\Deck\Enums\ImportStatus::FAILED;
    }

    public function getProgressPercentage(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        $processed = $this->imported_cards_count + $this->updated_cards_count + $this->skipped_rows;

        return min(100, (int) (($processed / $this->total_rows) * 100));
    }
}
