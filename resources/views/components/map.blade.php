{{--
    Leaflet map (OpenStreetMap tiles). Leaflet is downloaded only when such an element is on the page.
    <x-map :markers="[['lat' => 50.6, 'lng' => 5.5, 'name' => 'Pro', 'url' => '…', 'meta' => '2 km']]" :center="['lat' => …, 'lng' => …, 'label' => '…']" :label="__('…')" />
    Props: markers, center (search origin, optional), circle (radius in km drawn around the first marker), label (accessible name).
    The map is a visual extra: every marker is also listed in the page.
--}}
@props(['markers' => [], 'center' => null, 'circle' => null, 'label'])

<div {{ $attributes->class(['relative isolate overflow-hidden rounded-3xl bg-cream ring-1 ring-line shadow-soft']) }}>
    <div data-map
        data-markers="{{ json_encode(array_values($markers)) }}"
        data-center="{{ json_encode($center) }}"
        @if ($circle) data-circle-km="{{ $circle }}" @endif
        data-pin-label="{{ __('directory.map_you_are_here') }}"
        data-open-label="{{ __('directory.map_open_profile') }}"
        role="region" aria-label="{{ $label }}"
        class="size-full min-h-64">
        <div class="flex size-full min-h-64 flex-col items-center justify-center gap-2 p-6 text-center text-sm font-semibold text-muted">
            <x-icon name="map-pin" class="size-8 text-brown-mid" />{{ __('directory.map_loading') }}
        </div>
    </div>
</div>
