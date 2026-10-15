@php
    $locale = app()->getLocale();
    $plural = $category->pluralLabel();
    $pluralInSentence = $category->pluralLabel(inSentence: true);
    $radiusKm = \App\Http\Controllers\CategoryController::CITY_RADIUS_KM;
    $h1 = __('directory.city.h1', ['category' => $plural, 'city' => $cityName]);
    $km = fn (float $distance) => \Illuminate\Support\Number::format($distance, maxPrecision: 1, locale: $locale);
    $choose = trans('category_texts.'.$category->slug.'.choose');
    $markers = $providers->getCollection()->map(fn ($provider) => [
        'lat' => (float) $provider->lat,
        'lng' => (float) $provider->lng,
        'name' => $provider->name,
        'url' => $provider->isThin() ? null : localized_route('providers.show', $provider),
        'meta' => __('directory.km', ['km' => $km($provider->distance_km)]),
    ])->all();
    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $h1,
        'numberOfItems' => $total,
        // Listings too thin to be indexed are left out of the structured list.
        'itemListElement' => $providers->getCollection()->reject->isThin()->values()->map(fn ($provider, $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => localized_route('providers.show', $provider),
            'name' => $provider->name,
        ])->all(),
    ];

    // Introduction and FAQ built from the real data of this page.
    $intro = [trans_choice('directory.city.intro', $total, ['count' => $total, 'category' => $pluralInSentence, 'city' => $cityName, 'km' => $radiusKm])];
    if ($neighbourhoods->isNotEmpty()) {
        $intro[] = __('directory.city.intro_neighbours', ['places' => $neighbourhoods->join(', ', ' '.__('directory.and').' ')]);
    }
    if ($stats['saturday'] > 0) {
        $intro[] = trans_choice('directory.city.intro_saturday', $stats['saturday'], ['count' => $stats['saturday']]);
    }
    if ($stats['mobile'] > 0) {
        $intro[] = trans_choice('directory.city.intro_mobile', $stats['mobile'], ['count' => $stats['mobile']]);
    }

    $faq = [[
        __('directory.city.faq_count_q', ['category' => $pluralInSentence, 'city' => $cityName]),
        trans_choice('directory.city.faq_count_a', $total, ['count' => $total, 'city' => $cityName, 'km' => $radiusKm])
            .($stats['verified'] > 0 ? ' '.trans_choice('directory.city.faq_count_verified', $stats['verified'], ['count' => $stats['verified']]) : ''),
    ], [
        __('directory.city.faq_closest_q', ['city' => $cityName]),
        __('directory.city.faq_closest_a', ['name' => $closest->name, 'km' => $km($closest->distance_km), 'city' => $cityName]),
    ]];
    if ($stats['saturday'] > 0) {
        $faq[] = [
            __('directory.city.faq_saturday_q', ['category' => $pluralInSentence, 'city' => $cityName]),
            trans_choice('directory.city.faq_saturday_a', $stats['saturday'], ['count' => $stats['saturday']]),
        ];
    }
    if ($choose !== 'category_texts.'.$category->slug.'.choose') {
        $faq[] = [__('directory.city.faq_choose_q', ['category' => $pluralInSentence, 'city' => $cityName]), $choose];
    }
@endphp

<x-layouts.app
    :title="\Illuminate\Support\Str::limit($h1, 50, '…')"
    :description="\Illuminate\Support\Str::limit(trans_choice('directory.city.meta_description', $total, ['count' => $total, 'category' => $pluralInSentence, 'city' => $cityName]), 155, '…')"
    :alternates="$alternates" :noindex="! $indexable">
    <x-slot:head>
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
    </x-slot:head>

    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <x-paw class="absolute right-[30%] bottom-4 hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-8 sm:px-6 sm:pt-8 sm:pb-10">
            <x-breadcrumbs :items="[
                __('directory.breadcrumb_home') => localized_route('home'),
                $plural => localized_route('categories.show', ['category' => $category->routeSlug()]),
                $cityName => null,
            ]" />
            <div class="mt-4 max-w-3xl">
                <p class="inline-flex min-h-9 items-center gap-2 rounded-full bg-white/80 px-3.5 text-sm font-bold text-brown ring-1 ring-line">
                    <x-icon :name="$category->icon" class="size-4 text-orange-dark" />{{ $category->label() }}
                </p>
                <h1 class="mt-3 font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl lg:text-5xl">{{ $h1 }}</h1>
                <p class="mt-4 text-lg leading-relaxed text-brown-dark">{{ implode(' ', $intro) }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)] lg:gap-8">
            <section id="results" aria-labelledby="list-title">
                <h2 id="list-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.city.list_title', ['city' => $cityName]) }}</h2>
                <ul class="mt-5 grid gap-4" role="list">
                    @foreach ($providers as $provider)
                        <li><x-provider-card :provider="$provider" :link-thin="false" /></li>
                    @endforeach
                </ul>
                <div class="mt-8">{{ $providers->onEachSide(1)->links() }}</div>
            </section>
            {{-- After the list in the page (first screen = title and introduction); beside it on large screens. --}}
            <div>
                <x-map :markers="$markers" :label="__('directory.city.map_label', ['category' => $pluralInSentence, 'city' => $cityName])"
                    class="h-64 sm:h-80 lg:sticky lg:top-28 lg:h-[calc(100dvh-9rem)]" />
            </div>
        </div>

        <section class="mt-14 max-w-3xl" aria-labelledby="faq-title">
            <h2 id="faq-title" class="font-display text-2xl font-semibold text-brown sm:text-3xl">{{ __('directory.city.faq_title') }}</h2>
            <div class="mt-5 space-y-3">
                @foreach ($faq as [$question, $answer])
                    <details class="group rounded-2xl bg-white ring-1 ring-line shadow-soft" @if ($loop->first) open @endif>
                        <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 font-display text-lg font-semibold text-brown [&::-webkit-details-marker]:hidden">
                            <h3>{{ $question }}</h3>
                            <x-icon name="chevron-down" class="size-5 shrink-0 transition group-open:rotate-180" />
                        </summary>
                        <p class="px-5 pb-5 leading-relaxed text-brown-dark">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <div class="mt-14 grid gap-10 lg:grid-cols-2">
            @if ($nearbyCities->isNotEmpty())
                <section aria-labelledby="nearby-title">
                    <h2 id="nearby-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.city.nearby_title', ['category' => $pluralInSentence]) }}</h2>
                    <ul class="mt-5 flex flex-wrap gap-2">
                        @foreach ($nearbyCities as $item)
                            <li>
                                <a href="{{ localized_route('categories.city', ['category' => $category->routeSlug(), 'city' => $item['commune']->slug()]) }}"
                                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 font-bold text-brown ring-1 ring-line transition hover:bg-cream hover:ring-brown-mid">
                                    <x-icon name="map-pin" class="size-4 text-orange-dark" />{{ $item['commune']->label() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($otherCategories->isNotEmpty())
                <section aria-labelledby="other-title">
                    <h2 id="other-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.city.other_title', ['city' => $cityName]) }}</h2>
                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($otherCategories as $item)
                            <li>
                                <a href="{{ localized_route('categories.city', ['category' => $item['category']->routeSlug(), 'city' => $commune->slug()]) }}"
                                    class="flex min-h-14 items-center gap-3 rounded-2xl bg-white p-3 font-bold text-brown ring-1 ring-line transition hover:ring-brown-mid">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cream"><x-icon :name="$item['category']->icon" class="size-5" /></span>
                                    <span class="min-w-0 flex-1">{{ $item['category']->pluralLabel() }}</span>
                                    <span class="rounded-full bg-cream px-2 py-0.5 text-xs">{{ $item['count'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <x-add-business class="mt-14" />
    </div>
</x-layouts.app>
