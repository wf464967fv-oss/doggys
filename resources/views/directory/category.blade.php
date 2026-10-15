@php
    $locale = app()->getLocale();
    $total = $providers->total();
    $plural = $category->pluralLabel();
    $about = trans('category_texts.'.$category->slug.'.about');
    $itemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => __('directory.category.h1', ['category' => $plural]),
        'numberOfItems' => $total,
        // Listings too thin to be indexed are left out of the structured list.
        'itemListElement' => $providers->getCollection()->reject->isThin()->values()->map(fn ($provider, $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => localized_route('providers.show', $provider),
            'name' => $provider->name,
        ])->all(),
    ];
@endphp

<x-layouts.app
    :title="__('directory.category.title', ['category' => $plural])"
    :description="\Illuminate\Support\Str::limit(trans_choice('directory.category.meta_description', $total, ['count' => $total, 'category' => $category->pluralLabel(inSentence: true), 'communes' => $communesCount]), 155, '…')"
    :alternates="$alternates" :noindex="$total === 0 || $providers->currentPage() > 1">
    @if ($total > 0)
        <x-slot:head>
            <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
        </x-slot:head>
    @endif

    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8 sm:pb-12">
            <x-breadcrumbs :items="[
                __('directory.breadcrumb_home') => localized_route('home'),
                __('directory.categories.breadcrumb') => localized_route('categories.index'),
                $plural => null,
            ]" />
            <div class="mt-4 flex items-start gap-4">
                <span class="hidden size-16 shrink-0 items-center justify-center rounded-3xl bg-white text-brown shadow-soft ring-1 ring-line sm:flex" aria-hidden="true">
                    <x-icon :name="$category->icon" class="size-8" />
                </span>
                <div class="max-w-3xl">
                    <h1 class="font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl">{{ __('directory.category.h1', ['category' => $plural]) }}</h1>
                    <p class="mt-3 text-lg leading-relaxed text-brown-dark">
                        {{ $total > 0
                            ? trans_choice('directory.category.intro', $total, ['count' => $total, 'cities' => $communesCount])
                            : __('directory.category.intro_empty') }}
                    </p>
                </div>
            </div>
            <form method="GET" action="{{ localized_route('search') }}" role="search" class="mt-6 flex max-w-2xl flex-col gap-3 rounded-3xl bg-white p-3 shadow-soft ring-1 ring-line sm:flex-row sm:items-end">
                <input type="hidden" name="category" value="{{ $category->slug }}">
                <x-input name="location" :label="__('directory.category.search_label')" :placeholder="__('home.search_location_placeholder')" icon="map-pin"
                    autocomplete="off" list="place-suggestions" class="flex-1" x-data="placeSuggest" x-on:input="suggest" :data-endpoint="route('api.v1.places')" />
                <datalist id="place-suggestions"></datalist>
                <x-button icon="search">{{ __('home.search_submit') }}</x-button>
            </form>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
        @if ($cities->isNotEmpty())
            <section aria-labelledby="cities-title">
                <h2 id="cities-title" class="font-display text-2xl font-semibold text-brown sm:text-3xl">{{ __('directory.category.cities_title', ['category' => $category->pluralLabel(inSentence: true)]) }}</h2>
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($cities as $item)
                        <li>
                            <a href="{{ localized_route('categories.city', ['category' => $category->routeSlug(), 'city' => $item['commune']->slug()]) }}"
                                class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 font-bold text-brown ring-1 ring-line transition hover:bg-cream hover:ring-brown-mid">
                                {{ $item['commune']->label() }}
                                <span class="rounded-full bg-cream px-2 py-0.5 text-xs text-brown">{{ $item['count'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($about !== 'category_texts.'.$category->slug.'.about')
            <section class="mt-12 max-w-3xl" aria-labelledby="about-title">
                <h2 id="about-title" class="font-display text-2xl font-semibold text-brown sm:text-3xl">{{ __('directory.category.about_title', ['category' => $category->pluralLabel(inSentence: true)]) }}</h2>
                <div class="mt-4 space-y-4 text-lg leading-relaxed text-brown-dark">
                    @foreach (explode("\n\n", $about) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
                @if (Route::has($locale.'.guides'))
                    <a href="{{ localized_route('guides') }}" class="link mt-4 inline-flex min-h-11 items-center gap-1">{{ __('directory.category.guides_link') }}<x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </section>
        @endif

        <section class="mt-12" aria-labelledby="list-title">
            <h2 id="list-title" class="font-display text-2xl font-semibold text-brown sm:text-3xl">{{ __('directory.category.list_title') }}</h2>
            @if ($total > 0)
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list">
                    @foreach ($providers as $provider)
                        <li><x-provider-card :provider="$provider" :link-thin="false" /></li>
                    @endforeach
                </ul>
                <div class="mt-8">{{ $providers->onEachSide(1)->links() }}</div>
            @else
                <x-empty-state :icon="$category->icon" :title="__('directory.category.empty_title')" class="mt-5">
                    {{ __('directory.category.empty_text') }}
                    <x-slot:action>
                        <x-button :href="localized_route('register', ['type' => 'pro'])" icon="plus">{{ __('directory.add_business') }}</x-button>
                        <x-button variant="soft" :href="localized_route('categories.index')" icon="paw">{{ __('directory.search.browse_categories') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @endif
        </section>

        @if ($related->isNotEmpty())
            <section class="mt-14" aria-labelledby="related-title">
                <h2 id="related-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.related_title') }}</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <li>
                            <a href="{{ localized_route('categories.show', ['category' => $other->routeSlug()]) }}" class="flex min-h-14 items-center gap-3 rounded-2xl bg-white p-3 font-bold text-brown ring-1 ring-line transition hover:ring-brown-mid">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-cream"><x-icon :name="$other->icon" class="size-5" /></span>{{ $other->pluralLabel() }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.app>
