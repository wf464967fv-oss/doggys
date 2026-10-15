<?php

/*
 * Localisation helpers. Every public page is registered once per locale by Route::localized()
 * under the name "{locale}.{name}" (e.g. "nl.login" => /nl/inloggen).
 */

if (! function_exists('localized_route')) {
    /**
     * URL of a localized route in the current locale (or the given one).
     *
     * @param  array<string, mixed>  $parameters
     */
    function localized_route(string $name, mixed $parameters = [], ?string $locale = null, bool $absolute = true): string
    {
        return route(($locale ?? app()->getLocale()).'.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('localized_route_is')) {
    /**
     * Whether the current route matches the given (unprefixed) route name pattern(s), e.g. "account.*".
     */
    function localized_route_is(string ...$patterns): bool
    {
        return request()->routeIs(...array_map(fn (string $pattern) => app()->getLocale().'.'.$pattern, $patterns));
    }
}

if (! function_exists('localized_alternates')) {
    /**
     * URLs of the current page in every supported locale, keyed by locale (language switcher and hreflang).
     * Pages whose parameters differ per locale (e.g. translated slugs) pass their own array to the layout instead.
     *
     * @return array<string, string>
     */
    function localized_alternates(): array
    {
        $route = request()->route();
        $locales = config('app.locales');
        $name = $route?->getName();
        $prefix = $name ? strstr($name, '.', true) : null;

        if (! in_array($prefix, $locales, true)) {
            return array_combine($locales, array_map(fn (string $locale) => route($locale.'.home'), $locales));
        }

        $unprefixed = substr($name, strlen($prefix) + 1);

        return array_combine($locales, array_map(
            fn (string $locale) => route($locale.'.'.$unprefixed, $route->parameters()),
            $locales,
        ));
    }
}
