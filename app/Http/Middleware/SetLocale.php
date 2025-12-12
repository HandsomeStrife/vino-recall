<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LocalizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private LocalizationService $localizationService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Check if user is logged in and has a saved locale preference
        if ($user = $request->user()) {
            $locale = $user->locale;
        }

        // 2. If no user preference, auto-detect from Accept-Language header
        if ($locale === null && config('localization.auto_detect')) {
            $locale = $this->localizationService->detectLocaleFromHeader($request);
        }

        // 3. Fall back to default locale
        if ($locale === null || ! in_array($locale, config('localization.supported_locales'))) {
            $locale = config('localization.default_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
