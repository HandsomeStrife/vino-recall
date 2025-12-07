<?php

declare(strict_types=1);

namespace Domain\Deck\Helpers;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class DeckImageHelper
{
    private const DEFAULT_IMAGES_COUNT = 10;

    private const DEFAULT_IMAGES_PATH = 'img/defaults/';

    /**
     * Get the image path for a deck
     * Returns custom image if set, otherwise returns a consistent default image
     */
    public static function getImagePath(DeckData|Deck $deck): string
    {
        if ($deck instanceof DeckData) {
            $deckId = $deck->id;
            $imagePath = $deck->image_path;
        } else {
            $deckId = $deck->id;
            $imagePath = $deck->image_path;
        }

        if ($imagePath !== null && $imagePath !== '') {
            return asset('storage/' . $imagePath);
        }

        return self::getDefaultImagePath($deckId);
    }

    /**
     * Get a consistent default image path based on deck ID
     * Uses deck ID modulo 10 to assign one of the 10 default images
     */
    public static function getDefaultImagePath(int $deckId): string
    {
        $imageNumber = ($deckId % self::DEFAULT_IMAGES_COUNT) + 1;

        return asset(self::DEFAULT_IMAGES_PATH . $imageNumber . '.jpg');
    }
}

