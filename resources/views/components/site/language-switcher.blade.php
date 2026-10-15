{{--
    Language switcher linking to the current page in each locale. Works without JavaScript (<details>).
    Props: alternates [locale => url], inline (flat list for the mobile menu).
--}}
@props(['alternates' => [], 'inline' => false])

@php
    $names = ['fr' => 'Français', 'nl' => 'Nederlands', 'en' => 'English', 'de' => 'Deutsch'];
    $current = app()->getLocale();
@endphp

@if ($inline)
    <nav aria-label="{{ __('ui.language') }}" {{ $attributes }}>
        <ul class="grid grid-cols-2 gap-2">
            @foreach ($alternates as $locale => $url)
                <li>
                    <a href="{{ $url }}" hreflang="{{ $locale }}" lang="{{ $locale }}"
                        @class(['flex min-h-11 items-center justify-center rounded-full px-3 font-bold ring-2 transition', 'bg-brown text-cream-light ring-brown' => $locale === $current, 'bg-white text-brown ring-line hover:ring-brown-mid' => $locale !== $current])
                        @if ($locale === $current) aria-current="true" @endif>{{ $names[$locale] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
@else
    <details {{ $attributes->class(['group relative']) }} x-data="dropdown" x-on:keydown.escape="close" x-on:click.outside="close">
        <summary class="flex min-h-11 cursor-pointer list-none items-center gap-1.5 rounded-full px-3 font-bold text-brown transition hover:bg-cream [&::-webkit-details-marker]:hidden">
            <x-icon name="globe" class="size-5" />
            <span class="sr-only">{{ __('ui.language') }}:</span>
            <span>{{ strtoupper($current) }}</span>
            <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" />
        </summary>
        <ul class="absolute right-0 mt-2 w-48 rounded-2xl bg-white p-2 shadow-lift ring-1 ring-line">
            @foreach ($alternates as $locale => $url)
                <li>
                    <a href="{{ $url }}" hreflang="{{ $locale }}" lang="{{ $locale }}"
                        @class(['flex min-h-11 items-center justify-between rounded-xl px-3 font-semibold hover:bg-cream-light', 'text-brown' => $locale === $current, 'text-brown-dark' => $locale !== $current])
                        @if ($locale === $current) aria-current="true" @endif>
                        {{ $names[$locale] }}
                        @if ($locale === $current)<x-icon name="check" class="size-4 text-orange-dark" />@endif
                    </a>
                </li>
            @endforeach
        </ul>
    </details>
@endif
