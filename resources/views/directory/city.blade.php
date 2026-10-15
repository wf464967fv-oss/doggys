@php
    $locale = app()->getLocale();
    $plural = $category->pluralLabel();
    $h1 = __('directory.city.h1', ['category' => $plural, 'city' => $cityName]);
    $km = fn (float $distance) => \Illuminate\Support\Number::format($distance, maxPrecision: 1, locale: $locale);
    $neighbourNames = $neighbourhoods->map(fn ($item) => $item['city']['names'][$locale])->values();
    $markers = $providers->getCollection()->map(fn ($provider) => [
        'lat' => (float) $provider->lat,
        'lng' => (float) $provider->lng,
        'name' => $provider->name,
        'url' => localized_route('providers.show', $provider),
        'meta' => __('directory.km', ['km' => $km($provider->distance_km)]),
    ])->all();
    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $h1,
        'numberOfItems' => $total,
        'itemListElement' => $providers->getCollection()->values()->map(fn ($provider, $index) => [
            '@type' => 'ListItem',
            'position' => $providers->firstItem() + $index,
            'url' => localized_route('providers.show', $provider),
            'name' => $provider->name,
        ])->all(),
    ];
    $faq = [
        [
            __('directory.city.faq_count_q', ['category' => $category->pluralLabel(inSentence: true), 'city' => $cityName]),
            trans_choice('directory.city.faq_count_a', $total, ['count' => $total, 'city' => $cityName, 'km' => \App\Http\Controllers\CategoryController::CITY_RADIUS_KM])
                .($verifiedCount > 0 ? ' '.trans_choice('directory.city.faq_count_verified', $verifiedCount, ['count' => $verifiedCount]) : ''),
        ],
        [
            __('directory.city.faq_closest_q', ['city' => $cityName]),
            __('directory.city.faq_closest_a', ['name' => $closest->name, 'km' => $km($closest->distance_km), 'city' => $cityName]),
        ],
        [
            __('directory.city.faq_choose_q', ['category' => $category->pluralLabel(inSentence: true), 'city' => $cityName]),
            __('directory.city.faq_choose_a'),
        ],
    ];
@endphp

<x-layouts.app
    :title="\Illuminate\Support\Str::limit($h1, 50, '…')"
    :description="\Illuminate\Support\Str::limit(trans_choice('directory.city.meta_description', $total, ['count' => $total, 'category' => $category->pluralLabel(inSentence: true), 'city' => $cityName]), 155, '…')"
    :alternates="$alternates" :noindex="$total < 3 || $providers->currentPage() > 1">
    <x-slot:head>
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
    </x-slot:head>

    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <x-paw class="absolute right-[30%] bottom-4 hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8">
            <x-breadcrumbs :items="[
                __('directory.breadcrumb_home') => localized_route('home'),
                $plural => localized_route('categories.show', ['category' => $category->routeSlug()]),
                $cityName => null,
            ]" />
            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3.5 py-1.5 text-sm font-bold text-brown ring-1 ring-line">
                    <x-icon :name="$category->icon" class="size-4 text-orange-dark" />{{ $category->label() }}
                </p>
                <h1 class="mt-4 font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl lg:text-5xl">{{ $h1 }}</h1>
                <p class="mt-4 text-lg leading-relaxed text-brown-dark">
                    {{ trans_choice('directory.city.intro', $total, ['count' => $total, 'category' => $category->pluralLabel(inSentence: true), 'city' => $cityName, 'km' => \App\Http\Controllers\CategoryController::CITY_RADIUS_KM]) }}
                    @if ($neighbourNames->isNotEmpty())
                        {{ __('directory.city.intro_neighbours', ['places' => $neighbourNames->join(', ', ' '.__('directory.and').' ')]) }}
                    @endif
                    {{ $category->summary() }}
                </p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)] lg:gap-8">
            <div class="lg:order-2">
                <x-map :markers="$markers" :label="__('directory.city.map_label', ['category' => $category->pluralLabel(inSentence: true), 'city' => $cityName])"
                    class="h-72 sm:h-80 lg:sticky lg:top-28 lg:h-[calc(100dvh-9rem)]" />
            </div>
            <section aria-labelledby="list-title">
                <h2 id="list-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.city.list_title', ['city' => $cityName]) }}</h2>
                <ul class="mt-5 grid gap-4" role="list">
                    @foreach ($providers as $provider)
                        <li><x-provider-card :provider="$provider" /></li>
                    @endforeach
                </ul>
                <div class="mt-8">{{ $providers->onEachSide(1)->links() }}</div>
            </section>
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
                    <h2 id="nearby-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.city.nearby_title', ['category' => $category->pluralLabel(inSentence: true)]) }}</h2>
                    <ul class="mt-5 flex flex-wrap gap-2">
                        @foreach ($nearbyCities as $item)
                            <li>
                                <a href="{{ localized_route('categories.city', ['category' => $category->routeSlug(), 'city' => $item['city']['slugs'][$locale]]) }}"
                                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 font-bold text-brown ring-1 ring-line transition hover:bg-cream hover:ring-brown-mid">
                                    <x-icon name="map-pin" class="size-4 text-orange-dark" />{{ $item['city']['names'][$locale] }}
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
                                <a href="{{ localized_route('categories.city', ['category' => $item['category']->routeSlug(), 'city' => $city['slugs'][$locale]]) }}"
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
