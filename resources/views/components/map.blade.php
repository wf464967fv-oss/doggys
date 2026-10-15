{{--
    Leaflet map (OpenStreetMap tiles). Leaflet is only downloaded when the map scrolls into view.
    <x-map :markers="[['lat' => 50.6, 'lng' => 5.5, 'name' => 'Pro', 'url' => '…', 'meta' => '2 km']]" :center="['lat' => …, 'lng' => …, 'label' => '…']" :label="__('…')" />
    Props: markers, center (search origin, optional), circle (radius in km drawn around the first marker),
    label (accessible name), skipTo (id of the element the "skip the map" link jumps to; default: right after the map).
    The map is a visual extra: every marker is also listed in the page, so markers are not keyboard stops.
--}}
@props(['markers' => [], 'center' => null, 'circle' => null, 'label', 'skipTo' => null, 'click' => false])

@php
    $anchor = 'after-map-'.\Illuminate\Support\Str::random(6);
@endphp

<div {{ $attributes->class(['relative isolate overflow-hidden rounded-3xl bg-cream ring-1 ring-line shadow-soft']) }}>
    <a href="#{{ $skipTo ?? $anchor }}" class="sr-only z-[1000] rounded-full bg-brown px-4 py-2.5 font-bold text-cream-light focus:not-sr-only focus:absolute focus:top-3 focus:left-3">
        {{ __('directory.map_skip') }}
    </a>
    <div data-map @if ($click) data-map-click @endif
        data-markers="{{ json_encode(array_values($markers)) }}"
        data-center="{{ json_encode($center) }}"
        @if ($circle) data-circle-km="{{ $circle }}" @endif
        data-pin-label="{{ __('directory.map_you_are_here') }}"
        data-open-label="{{ __('directory.map_open_profile') }}"
        role="region" aria-label="{{ $label }}"
        class="size-full min-h-56 bg-gradient-to-b from-cream to-cream-light">
        <div class="flex size-full min-h-56 flex-col items-center justify-center gap-2 p-6 text-center text-sm font-semibold text-muted">
            <x-icon name="map-pin" class="size-8 text-brown-mid" />@if ($click)<button type="button" data-map-load class="inline-flex min-h-11 items-center rounded-full bg-brown px-5 font-bold text-cream-light hover:bg-brown-dark focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-orange">{{ __('directory.map_show') }}</button>@else{{ __('directory.map_loading') }}@endif
        </div>
    </div>
</div>
@unless ($skipTo)
    <span id="{{ $anchor }}" tabindex="-1" class="block"></span>
@endunless
