<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the locale of a localized route group ("locale:nl") to the application (plain "locale": the
 * browser language, used by the unprefixed admin area),
 * and points Fortify's post-action redirects to the pages of that locale.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next, ?string $locale = null): Response
    {
        $locale ??= $request->getPreferredLanguage(config('app.locales')) ?? config('app.locales')[0];
        app()->setLocale($locale);

        config([
            'fortify.home' => route($locale.'.account'),
            'fortify.redirects.logout' => route($locale.'.home'),
            'fortify.redirects.password-reset' => route($locale.'.login'),
        ]);

        return $next($request);
    }
}
