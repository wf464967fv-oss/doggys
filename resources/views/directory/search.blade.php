@php
    $title = $category ? __('directory.search.title_category', ['category' => $category->pluralLabel()]) : __('directory.search.title');
    $hasOrigin = isset($origin['lat']);
    $km = fn (float $distance) => \Illuminate\Support\Number::format($distance, maxPrecision: 1, locale: app()->getLocale());
    $markers = $hasOrigin ? $results->getCollection()->map(fn ($provider) => [
        'lat' => (float) $provider->lat,
        'lng' => (float) $provider->lng,
        'name' => $provider->name,
        'url' => localized_route('providers.show', $provider),
        'meta' => __('directory.km', ['km' => $km($provider->distance_km)]),
    ])->all() : [];
    $summary = collect([$category?->pluralLabel() ?? __('directory.search.all_pros'), $origin['label'] ?? null, __('directory.km', ['km' => $radius])])->filter()->join(' · ');
@endphp

<x-layouts.app :title="$title" :description="__('directory.search.meta_description')" noindex>
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light" aria-labelledby="search-title">
        <x-paw class="absolute top-6 right-[6%] size-10 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-6 sm:px-6 sm:pt-10 sm:pb-8">
            <h1 id="search-title" class="font-display text-2xl leading-tight font-semibold text-brown sm:text-4xl">
                @if ($hasOrigin)
                    {{ __('directory.search.heading_near', ['what' => $category?->pluralLabel() ?? __('directory.search.all_pros'), 'place' => $origin['label']]) }}
                @else
                    {{ $title }}
                @endif
            </h1>

            {{-- After a search, phones get a one-line summary that unfolds into the form. --}}
            <details class="group mt-4 md:mt-6 md:block" @if (! $hasOrigin) open @endif data-search-form>
                <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 rounded-2xl bg-white px-4 font-bold text-brown shadow-soft ring-1 ring-line md:hidden [&::-webkit-details-marker]:hidden">
                    <span class="min-w-0 truncate">{{ $summary }}</span>
                    <span class="inline-flex shrink-0 items-center gap-1 text-orange-dark">{{ __('directory.search.edit') }}<x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" /></span>
                </summary>

                <form method="GET" action="{{ localized_route('search') }}" role="search" x-data="locateMe"
                    class="mt-3 rounded-3xl bg-white p-4 shadow-lift ring-1 ring-line sm:p-5 md:mt-0">
                    <div class="grid gap-3 md:grid-cols-[1fr_1fr_10rem]">
                        <x-select name="category" :label="__('home.search_category')" :placeholder="__('home.search_all_categories')"
                            :options="$categoryOptions" :value="$category?->slug" />
                        <x-input name="location" :label="__('home.search_location')" :placeholder="__('home.search_location_placeholder')"
                            icon="map-pin" autocomplete="off" list="place-suggestions" :value="$hasOrigin && request()->filled('lat') ? '' : request('location')"
                            x-data="placeSuggest" x-on:input="suggest" :data-endpoint="route('api.v1.places')" />
                        <x-select name="radius" :label="__('directory.search.radius')" :value="$radius"
                            :options="collect(\App\Services\ProviderSearch::RADII)->mapWithKeys(fn ($km) => [$km => __('directory.km', ['km' => $km])])->all()" />
                    </div>
                    <datalist id="place-suggestions"></datalist>
                    <input type="hidden" name="lat" value="{{ request('lat') }}">
                    <input type="hidden" name="lng" value="{{ request('lng') }}">
                    <input type="hidden" name="near_me" value="{{ request('near_me') }}">

                    @if (in_array(true, $availableFilters, true) || $filtersUsed)
                        <details class="group/filters mt-3" @if ($filtersUsed) open @endif>
                            <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full px-1 font-bold text-brown [&::-webkit-details-marker]:hidden">
                                <x-icon name="sparkles" class="size-5 text-orange-dark" />{{ __('directory.search.more_filters') }}
                                <x-icon name="chevron-down" class="size-4 transition group-open/filters:rotate-180" />
                            </summary>
                            <div class="mt-2 grid gap-3 rounded-2xl bg-cream-light p-4 ring-1 ring-line sm:grid-cols-2 lg:grid-cols-3">
                                @if ($availableFilters['mobile'] || request()->boolean('mobile'))
                                    <x-checkbox name="mobile" :checked="request()->boolean('mobile')">{{ __('directory.search.filter_mobile') }}</x-checkbox>
                                @endif
                                @if ($availableFilters['verified'] || request()->boolean('verified'))
                                    <x-checkbox name="verified" :checked="request()->boolean('verified')">{{ __('directory.search.filter_verified') }}</x-checkbox>
                                @endif
                                @if ($availableFilters['rating'] || request()->filled('min_rating'))
                                    <x-select name="min_rating" :label="__('directory.search.filter_rating')" :value="request('min_rating')" :placeholder="__('directory.search.any_rating')"
                                        :options="collect([4, 3, 2])->mapWithKeys(fn ($stars) => [$stars => trans_choice('directory.search.rating_option', $stars, ['count' => $stars])])->all()" />
                                @endif
                            </div>
                        </details>
                    @endif

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <x-button icon="search" size="lg" class="sm:flex-1">{{ __('home.search_submit') }}</x-button>
                        <x-button type="button" variant="soft" size="lg" icon="locate" x-on:click="locate" x-bind:disabled="locating">
                            <span x-show="! locating">{{ __('home.search_near_me') }}</span>
                            <span x-show="locating" x-cloak>{{ __('home.search_locating') }}</span>
                        </x-button>
                    </div>
                    <p x-show="failed" x-cloak role="alert" class="mt-3 flex items-start gap-2 text-sm font-semibold text-rose-dark">
                        <x-icon name="alert" class="mt-0.5 size-4" />{{ __('home.search_locate_failed') }}
                    </p>
                </form>
            </details>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pt-4 pb-14 sm:px-6 sm:pt-6 sm:pb-20">
        @if ($errors->any())
            <x-alert variant="error" :title="__('directory.search.invalid_title')" class="mb-6">{{ __('directory.search.invalid_text') }}</x-alert>
        @endif

        @if (! $origin)
            <x-empty-state icon="map-pin" :title="__('directory.search.prompt_title')">
                {{ __('directory.search.prompt_text') }}
                <x-slot:action>
                    <x-button variant="soft" :href="localized_route('categories.index')" icon="paw">{{ __('directory.search.browse_categories') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @elseif (! $hasOrigin)
            <x-empty-state icon="search" :title="__('directory.search.unknown_place_title', ['place' => request('location')])">
                {{ __('directory.search.unknown_place_text') }}
                <x-slot:action>
                    <x-button variant="soft" :href="localized_route('categories.index')" icon="paw">{{ __('directory.search.browse_categories') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @elseif ($results->isEmpty())
            <x-empty-state icon="paw" :title="__('directory.search.empty_title')">
                @if ($filtersUsed)
                    {{ __('directory.search.empty_text_filters') }}
                @else
                    {{ $widerRadius ? __('directory.search.empty_text_wider', ['km' => $widerRadius]) : __('directory.search.empty_text') }}
                @endif
                <x-slot:action>
                    @if ($filtersUsed)
                        <x-button :href="localized_route('search', request()->except(['mobile', 'verified', 'min_rating', 'page']))" icon="x">{{ __('directory.search.remove_filters') }}</x-button>
                    @elseif ($widerRadius)
                        <x-button :href="localized_route('search', array_merge(request()->except('page'), ['radius' => $widerRadius]))" icon="locate">{{ __('directory.search.widen', ['km' => $widerRadius]) }}</x-button>
                    @endif
                    <x-button variant="soft" :href="localized_route('register', ['type' => 'pro'])" icon="plus">{{ __('directory.add_business') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)] lg:gap-8">
                <div id="results" tabindex="-1" class="focus:outline-none">
                    <p class="font-bold text-brown" role="status">
                        {{ $results->total() > \App\Services\ProviderSearch::COUNT_CAP
                            ? __('directory.search.results_many', ['count' => \Illuminate\Support\Number::format(\App\Services\ProviderSearch::COUNT_CAP, locale: app()->getLocale()), 'km' => $radius])
                            : trans_choice('directory.search.results_count', $results->total(), ['count' => $results->total(), 'km' => $radius]) }}
                    </p>
                    <ul class="mt-4 grid gap-4" role="list">
                        @foreach ($results as $provider)
                            <li><x-provider-card :provider="$provider" heading="h2" /></li>
                        @endforeach
                    </ul>
                    <div class="mt-8">{{ $results->onEachSide(1)->links() }}</div>
                </div>
                {{-- After the list in the page (and for keyboards); beside it on large screens. --}}
                <div>
                    <x-map :markers="$markers" :center="['lat' => $origin['lat'], 'lng' => $origin['lng'], 'label' => $origin['label']]" :label="__('directory.search.map_label')"
                        class="h-64 sm:h-80 lg:sticky lg:top-28 lg:h-[calc(100dvh-9rem)]" />
                </div>
            </div>
            <x-add-business class="mt-10" />
        @endif
    </div>
</x-layouts.app>
