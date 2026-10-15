{{--
    Provider summary card used in search results, category and city pages. The whole card is clickable.
    <x-provider-card :provider="$provider" />  (shows $provider->distance_km when set by ProviderSearch)
    Props: provider, heading (h2|h3), linkThin (false on indexable pages: listings without address nor contact
    are noindex, so they are shown without a link there).
--}}
@props(['provider', 'heading' => 'h3', 'linkThin' => true])

@php
    $mainCategory = $provider->categories->first();
    $distance = $provider->getAttribute('distance_km');
    $linked = $linkThin || ! $provider->isThin();
@endphp

<x-card as="article" padding="none" :interactive="$linked" {{ $attributes->class(['group relative flex h-full flex-col gap-3 p-4 sm:p-5']) }}>
    <div class="flex items-start gap-3">
        <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-cream text-brown transition group-hover:bg-orange group-hover:text-brown-dark" aria-hidden="true">
            <x-icon :name="$mainCategory?->icon ?? 'paw'" class="size-6" />
        </span>
        <div class="min-w-0 flex-1">
            <{{ $heading }} class="font-display text-lg leading-snug font-semibold text-brown">
                @if ($linked)
                    <a href="{{ localized_route('providers.show', $provider) }}" class="rounded-md after:absolute after:inset-0 after:rounded-3xl focus-visible:outline-none focus-visible:after:outline-3 focus-visible:after:outline-offset-2 focus-visible:after:outline-brown">{{ $provider->name }}</a>
                @else
                    {{ $provider->name }}
                @endif
            </{{ $heading }}>
            <p class="mt-0.5 text-sm leading-snug text-muted">{{ $provider->categories->map->label()->join(' · ') }}</p>
        </div>
        @if ($distance !== null)
            <span class="shrink-0 rounded-full bg-cream-light px-2.5 py-1 text-sm font-bold whitespace-nowrap text-brown ring-1 ring-line">
                {{ __('directory.km', ['km' => \Illuminate\Support\Number::format($distance, maxPrecision: 1, locale: app()->getLocale())]) }}
            </span>
        @endif
    </div>

    <p class="flex items-start gap-1.5 text-sm text-brown-dark">
        @if ($provider->is_mobile)
            <x-icon name="van" class="mt-0.5 size-4 shrink-0 text-orange-dark" />{{ __('directory.comes_to_you') }}
        @else
            <x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-orange-dark" />{{ collect([trim($provider->street.' '.$provider->house_number), trim($provider->postal_code.' '.$provider->city)])->filter()->join(', ') }}
        @endif
    </p>

    <div class="mt-auto flex flex-wrap items-center gap-2">
        @if ($provider->isRegulated())
            <x-badge variant="regulated" />
        @endif
        @if ($provider->isVerified())
            <x-badge variant="verified" />
        @endif
        @if ($provider->reviews_count > 0)
            <x-stars :rating="$provider->rating_avg" :count="$provider->reviews_count" />
        @endif
        @if ($provider->is_mobile)
            <x-badge variant="orange" icon="van">{{ __('directory.card.mobile') }}</x-badge>
        @endif
        @if ($provider->phone)
            <x-badge icon="phone">{{ __('directory.card.phone') }}</x-badge>
        @endif
        @if ($provider->opening_hours)
            <x-badge icon="clock">{{ __('directory.card.hours') }}</x-badge>
        @endif
    </div>
</x-card>
