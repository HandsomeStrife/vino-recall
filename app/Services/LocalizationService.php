<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;

class LocalizationService
{
    /**
     * Detect locale from Accept-Language header
     */
    public function detectLocaleFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,es;q=0.8")
        $locales = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', $lang);
            $locale = strtolower(trim($parts[0]));

            // Extract language code (e.g., "en-US" -> "en")
            if (str_contains($locale, '-')) {
                $locale = substr($locale, 0, strpos($locale, '-'));
            }

            // Validate locale format: only lowercase letters, exactly 2 characters
            if (! preg_match('/^[a-z]{2}$/', $locale)) {
                continue; // Skip invalid locales
            }

            $quality = 1.0;
            if (isset($parts[1]) && str_starts_with($parts[1], 'q=')) {
                $quality = (float) substr($parts[1], 2);
            }

            $locales[$locale] = $quality;
        }

        // Sort by quality
        arsort($locales);

        // Find first supported locale
        $supportedLocales = config('localization.supported_locales');
        foreach (array_keys($locales) as $locale) {
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        return config('localization.supported_locales');
    }

    /**
     * Get human-readable label for a locale
     */
    public function getLocaleLabel(string $locale): string
    {
        return match ($locale) {
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'zh' => '中文',
            'ja' => '日本語',
            default => $locale,
        };
    }
}

