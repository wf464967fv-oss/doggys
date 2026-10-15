{{--
    Breadcrumb trail + its BreadcrumbList JSON-LD.
    <x-breadcrumbs :items="[__('blog.breadcrumb_home') => localized_route('home'), $guide->title => null]" />
    Props: items [label => url]; the last item is the current page (url may be null, the current URL is used in JSON-LD).
--}}
@props(['items'])

@php
    $labels = array_keys($items);
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn (string $label, int $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $label,
            'item' => $items[$label] ?? url()->current(),
        ], $labels, array_keys($labels)),
    ];
@endphp

<nav aria-label="{{ __('ui.breadcrumb') }}" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-1.5 text-sm font-semibold text-muted">
        @foreach ($items as $label => $url)
            {{-- On phones the current page (repeated by the H1 just below) is hidden. --}}
            <li @class(['flex items-center gap-1.5', 'max-sm:hidden' => $loop->last])>
                @if (! $loop->last)
                    <a href="{{ $url }}" class="inline-flex min-h-11 items-center rounded-md underline-offset-4 hover:text-brown hover:underline">{{ $label }}</a>
                    <x-icon name="chevron-right" :class="$loop->remaining === 1 ? 'size-4 text-brown-mid max-sm:hidden' : 'size-4 text-brown-mid'" />
                @else
                    <span aria-current="page" class="inline-flex min-h-11 items-center text-brown-dark">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
<script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
