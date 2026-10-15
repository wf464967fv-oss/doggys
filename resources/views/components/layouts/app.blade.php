{{--
    Main page layout.
    <x-layouts.app :title="__('…')" :description="__('…')">…</x-layouts.app>
    Props:
      title        page title (without the " | Doggys" suffix, added automatically; keep ≤ 50 chars)
      description  meta description (≤ 155 chars)
      alternates   [locale => url] when the page URL differs per locale beyond route params (default: localized_alternates())
      noindex      true for private, thin or internal-search pages
      bare         true on 5xx error pages: no session/database-dependent UI
      fullTitle    true when title already contains the brand (home page)
      ogType       Open Graph type (default website; guides pass article and add article:* tags in the head slot)
    Slot "head": extra tags (JSON-LD, preload…). Inline scripts need nonce="{{ Vite::cspNonce() }}".
--}}
@props([
    'title',
    'description' => null,
    'alternates' => null,
    'noindex' => false,
    'bare' => false,
    'fullTitle' => false,
    'ogType' => 'website',
])

@php
    $locale = app()->getLocale();
    $alternates ??= localized_alternates();
    $pageTitle = $fullTitle ? $title : $title.' | Doggys';
    $description ??= __('ui.meta_description');
    $canonical = $alternates[$locale] ?? url()->current();
    $ogLocales = ['fr' => 'fr_BE', 'nl' => 'nl_BE', 'en' => 'en_GB', 'de' => 'de_BE'];
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}"{{ app()->isProduction() ? " data-sw" : "" }}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($noindex)
        <meta name="robots" content="noindex, follow">
    @else
        <link rel="canonical" href="{{ $canonical }}">
        @foreach ($alternates as $alternateLocale => $alternateUrl)
            <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('app.locales')[0]] ?? $canonical }}">
    @endif

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="Doggys">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ asset('images/og-default.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ $ogLocales[$locale] }}">
    @foreach (array_diff_key($ogLocales, [$locale => true]) as $ogLocale)
        <meta property="og:locale:alternate" content="{{ $ogLocale }}">
    @endforeach
    <meta property="og:image:alt" content="{{ __('ui.og_image_alt') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/og-default.png') }}">

    <meta name="theme-color" content="#f5a043">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <link rel="preload" href="{{ asset('fonts/fredoka-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/nunito-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="flex min-h-dvh flex-col">
    <a href="#main" class="sr-only z-50 rounded-full bg-brown px-5 py-3 font-bold text-cream-light focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        {{ __('ui.skip_to_content') }}
    </a>

    <x-site.header :bare="$bare" :alternates="$alternates" />

    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        @unless ($bare)
            <x-flash />
        @endunless
        {{ $slot }}
    </main>

    <x-site.footer />
</body>
</html>
