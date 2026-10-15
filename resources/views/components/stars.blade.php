{{--
    Star rating, read-only. <x-stars :rating="4.3" :count="12" />
    Props: rating (0–5), count (number of reviews, shown next to the stars), size (Tailwind size class).
--}}
@props(['rating', 'count' => null, 'size' => 'size-4'])

@php
    $rounded = (int) round((float) $rating);
    $formatted = \Illuminate\Support\Number::format((float) $rating, maxPrecision: 1, locale: app()->getLocale());
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex items-center gap-0.5" role="img" aria-label="{{ __('directory.rating_label', ['rating' => $formatted]) }}">
        @for ($i = 1; $i <= 5; $i++)
            <x-icon name="star" @class([$size, 'fill-orange stroke-orange-dark text-orange-dark' => $i <= $rounded, 'fill-none stroke-brown-mid text-brown-mid' => $i > $rounded]) />
        @endfor
    </span>
    <span class="text-sm font-bold text-brown-dark">{{ $formatted }}</span>
    @if ($count !== null)
        <span class="text-sm text-muted">({{ trans_choice('directory.reviews_count', $count, ['count' => $count]) }})</span>
    @endif
</span>
