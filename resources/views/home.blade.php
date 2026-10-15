<x-layouts.app :title="__('home.meta_title')" :description="__('home.meta_description')" full-title>
    @php
        $homeUrl = localized_route('home');
        $structuredData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/').'/#organization',
                    'name' => 'Doggys',
                    'url' => $homeUrl,
                    'logo' => asset('icon-512.png'),
                    'email' => 'contact@doggys.be',
                    'slogan' => __('ui.slogan'),
                    'areaServed' => ['@type' => 'Country', 'name' => 'Belgium'],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $homeUrl.'#website',
                    'name' => 'Doggys',
                    'url' => $homeUrl,
                    'description' => __('home.meta_description'),
                    'inLanguage' => ['fr' => 'fr-BE', 'nl' => 'nl-BE', 'en' => 'en', 'de' => 'de-BE'][app()->getLocale()],
                    'publisher' => ['@id' => url('/').'/#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => localized_route('search').'?location={search_term_string}'],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    <x-slot:head>
        <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <div class="pointer-events-none absolute -top-24 -left-24 size-72 rounded-full bg-orange/15 blur-2xl"></div>
        <div class="pointer-events-none absolute top-1/3 -right-20 size-80 rounded-full bg-rose/10 blur-2xl"></div>
        <x-paw class="absolute top-10 right-[8%] size-10 rotate-12 text-orange/25" />
        <x-paw class="absolute bottom-16 left-[4%] size-8 -rotate-12 text-brown-mid/25" />

        <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-4 pt-8 pb-10 sm:px-6 sm:pt-14 sm:pb-14 lg:grid-cols-[1.15fr_0.85fr] lg:pb-20">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3.5 py-1.5 text-sm font-bold text-brown ring-1 ring-line">
                    <x-icon name="heart" class="size-4 text-rose-dark" />{{ __('home.hero_eyebrow') }}
                </p>
                <h1 class="mt-5 font-display text-4xl leading-[1.08] font-semibold text-brown sm:text-5xl lg:text-6xl">
                    {{ __('home.hero_title') }}
                    <span class="relative whitespace-nowrap text-orange-dark">{{ __('home.hero_title_highlight') }}<svg aria-hidden="true" viewBox="0 0 200 12" class="absolute -bottom-2 left-0 h-3 w-full text-orange" preserveAspectRatio="none"><path d="M2 9c40-6 120-8 196-3" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg></span>
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted sm:text-xl">{{ __('home.hero_subtitle') }}</p>

                <form method="GET" action="{{ localized_route('search') }}" role="search" x-data="locateMe"
                    class="mt-8 rounded-3xl bg-white p-4 shadow-lift ring-1 ring-line sm:p-5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-select name="category" :label="__('home.search_category')" :placeholder="__('home.search_all_categories')"
                            :options="$categoryOptions" />
                        <x-input name="location" :label="__('home.search_location')" :placeholder="__('home.search_location_placeholder')"
                            icon="map-pin" autocomplete="off" list="place-suggestions"
                            x-data="placeSuggest" x-on:input="suggest" :data-endpoint="route('api.v1.places')" />
                        <datalist id="place-suggestions"></datalist>
                    </div>
                    <input type="hidden" name="lat" value="">
                    <input type="hidden" name="lng" value="">
                    <input type="hidden" name="near_me" value="">
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
            </div>

            <div class="relative mx-auto hidden aspect-square w-full max-w-md lg:block" aria-hidden="true">
                <div class="absolute inset-6 rounded-full bg-white shadow-lift ring-8 ring-orange/20"></div>
                <img src="{{ asset('images/logo-mark-240.webp') }}" srcset="{{ asset('images/logo-mark-240.webp') }} 240w, {{ asset('images/logo-mark-576.webp') }} 576w"
                    sizes="288px" alt="" width="288" height="288" loading="lazy" class="absolute inset-0 m-auto size-72">
                <div class="absolute top-6 -left-4 flex items-center gap-2 rounded-2xl bg-white px-4 py-3 font-bold text-brown shadow-soft ring-1 ring-line">
                    <span class="flex size-9 items-center justify-center rounded-full bg-orange-soft text-orange-dark"><x-icon name="scissors" /></span>{{ __('home.hero_chip_grooming') }}
                </div>
                <div class="absolute top-1/2 -right-6 flex items-center gap-2 rounded-2xl bg-white px-4 py-3 font-bold text-brown shadow-soft ring-1 ring-line">
                    <span class="flex size-9 items-center justify-center rounded-full bg-rose-soft text-rose-dark"><x-icon name="stethoscope" /></span>{{ __('home.hero_chip_vet') }}
                </div>
                <div class="absolute bottom-8 left-2 flex flex-col gap-2 rounded-2xl bg-white px-4 py-3 shadow-soft ring-1 ring-line">
                    <span class="flex items-center gap-2 font-bold text-brown"><x-icon name="graduation-cap" class="text-orange-dark" />{{ __('home.hero_chip_trainer') }}</span>
                    <x-badge variant="verified" />
                </div>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section class="mx-auto max-w-6xl px-4 pt-4 pb-10 sm:px-6 sm:pt-10 sm:pb-20" aria-labelledby="categories-title">
        <x-section-title id="categories-title" :eyebrow="__('home.categories_eyebrow')" :title="__('home.categories_title')" :subtitle="__('home.categories_subtitle')" />

        {{-- One continuous grid (no half-empty rows per group); the group is shown as a small label in each card. --}}
        <ul class="mt-6 grid grid-cols-2 gap-2 sm:mt-10 sm:grid-cols-3 sm:gap-3 lg:grid-cols-4">
            @foreach ($categoryGroups as $group => $groupCategories)
                @foreach ($groupCategories as $category)
                    <li>
                        <a href="{{ localized_route('categories.show', ['category' => $category->routeSlug()]) }}"
                            class="group flex h-full min-h-16 flex-col items-start gap-2 rounded-2xl bg-white p-3 ring-1 ring-line shadow-soft transition duration-200 hover:-translate-y-0.5 hover:shadow-lift hover:ring-brown-mid/60 sm:rounded-3xl sm:p-4">
                            <span class="flex shrink-0 items-center justify-between gap-2 sm:w-full">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cream text-brown transition group-hover:bg-orange group-hover:text-brown-dark sm:size-12 sm:rounded-2xl">
                                    <x-icon :name="$category->icon" class="size-5 sm:size-6" />
                                </span>
                                <span class="hidden truncate text-[0.6875rem] font-extrabold tracking-wide text-orange-dark uppercase sm:block">{{ __('categories.groups.'.$group) }}</span>
                            </span>
                            <span class="block min-w-0 font-display text-sm leading-tight font-semibold text-brown hyphens-auto sm:text-lg sm:leading-snug">{{ $category->label() }}</span>
                            <span class="hidden text-sm leading-snug text-muted sm:block">{{ $category->summary() }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </section>

    {{-- A whole life together --}}
    <section class="relative overflow-hidden bg-cream py-10 sm:py-20" aria-labelledby="life-title">
        <x-paw class="absolute top-8 left-[6%] size-12 -rotate-12 text-white/60" />
        <x-paw class="absolute right-[5%] bottom-10 size-16 rotate-12 text-white/60" />
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
            <x-section-title id="life-title" align="center" :eyebrow="__('home.life_eyebrow')" :title="__('home.life_title')" :subtitle="__('home.life_subtitle')" />

            @php
                $stages = [
                    ['key' => 'puppy', 'icon' => 'sparkles', 'tone' => 'bg-orange-soft text-orange-dark', 'categories' => ['eleveur-agree', 'refuge', 'veterinaire', 'educateur']],
                    ['key' => 'adult', 'icon' => 'route', 'tone' => 'bg-rose-soft text-rose-dark', 'categories' => ['toiletteur-salon', 'promeneur', 'pension', 'lieu-dog-friendly']],
                    ['key' => 'senior', 'icon' => 'heart', 'tone' => 'bg-regulated-soft text-regulated', 'categories' => ['kine-physio', 'hydrotherapie', 'osteopathe', 'toiletteur-domicile']],
                ];
                $categoriesBySlug = $categories->keyBy('slug');
            @endphp

            {{-- Swipeable row on small screens, three columns from lg. --}}
            <ol class="-mx-4 mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pt-5 pb-3 sm:mt-10 lg:mx-0 lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible lg:px-0">
                @foreach ($stages as $index => $stage)
                    <li class="relative w-[85%] shrink-0 snap-start rounded-3xl bg-white p-6 shadow-soft ring-1 ring-line sm:w-[60%] lg:w-auto">
                        <span class="absolute -top-4 left-6 flex size-9 items-center justify-center rounded-full bg-brown font-display text-lg font-semibold text-cream-light">{{ $index + 1 }}</span>
                        <span class="flex size-14 items-center justify-center rounded-2xl {{ $stage['tone'] }}"><x-icon :name="$stage['icon']" class="size-7" /></span>
                        <h3 class="mt-4 font-display text-2xl font-semibold text-brown">{{ __('home.life_'.$stage['key'].'_title') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted sm:text-base">{{ __('home.life_'.$stage['key'].'_text') }}</p>
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($stage['categories'] as $slug)
                                @isset($categoriesBySlug[$slug])
                                    <li>
                                        <a href="{{ localized_route('categories.show', ['category' => $categoriesBySlug[$slug]->routeSlug()]) }}" class="inline-flex min-h-11 items-center rounded-full bg-cream-light px-4 text-sm font-bold text-brown ring-1 ring-line transition hover:bg-cream">
                                            {{ $categoriesBySlug[$slug]->label() }}
                                        </a>
                                    </li>
                                @endisset
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>

            <div class="on-dark mt-8 flex flex-col items-center gap-4 rounded-3xl bg-brown p-6 text-center text-cream-light sm:flex-row sm:p-8 sm:text-left [&_:focus-visible]:outline-orange">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-orange text-brown-dark"><x-icon name="paw" class="size-8" /></span>
                <div class="flex-1">
                    <p class="font-display text-2xl font-semibold text-cream-light">{{ __('home.profile_title') }}</p>
                    <p class="mt-1 text-cream/90">{{ __('home.profile_text') }}</p>
                </div>
                <x-button :href="auth()->check() ? localized_route('dogs.create') : localized_route('register')" icon-right="arrow-right">{{ __('home.profile_cta') }}</x-button>
            </div>
        </div>
    </section>

    {{-- Trust --}}
    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-20" aria-labelledby="trust-title">
        <x-section-title id="trust-title" :eyebrow="__('home.trust_eyebrow')" :title="__('home.trust_title')" :subtitle="__('home.trust_subtitle')" />

        {{-- Swipeable row on small screens, three columns from lg. --}}
        <div class="-mx-4 mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:mt-10 lg:mx-0 lg:grid lg:grid-cols-3 lg:gap-5 lg:overflow-visible lg:px-0">
            <x-card class="w-[85%] shrink-0 snap-start sm:w-[60%] lg:w-auto">
                <x-badge variant="verified" />
                <h3 class="mt-4 font-display text-xl font-semibold text-brown">{{ __('home.trust_verified_title') }}</h3>
                <p class="mt-2 leading-relaxed text-muted">{{ __('home.trust_verified_text') }}</p>
            </x-card>
            <x-card class="w-[85%] shrink-0 snap-start sm:w-[60%] lg:w-auto">
                <x-badge variant="regulated" />
                <h3 class="mt-4 font-display text-xl font-semibold text-brown">{{ __('home.trust_regulated_title') }}</h3>
                <p class="mt-2 leading-relaxed text-muted">{{ __('home.trust_regulated_text') }}</p>
            </x-card>
            <x-card class="w-[85%] shrink-0 snap-start sm:w-[60%] lg:w-auto">
                <span class="inline-flex items-center gap-0.5 text-orange-dark" role="img" aria-label="{{ __('home.trust_reviews_stars') }}">
                    @for ($i = 0; $i < 5; $i++)<x-icon name="star" class="size-5 fill-orange stroke-orange-dark" />@endfor
                </span>
                <h3 class="mt-4 font-display text-xl font-semibold text-brown">{{ __('home.trust_reviews_title') }}</h3>
                <p class="mt-2 leading-relaxed text-muted">{{ __('home.trust_reviews_text') }}</p>
                <a href="{{ localized_route('reviews.policy') }}" class="link mt-3 inline-flex min-h-11 items-center gap-1">{{ __('home.trust_reviews_link') }}<x-icon name="arrow-right" class="size-4" /></a>
            </x-card>
        </div>

        <ul class="mt-8 flex flex-wrap justify-center gap-3 text-sm font-bold text-brown">
            @foreach (['free', 'no_ads', 'no_tracking', 'belgium'] as $promise)
                <li class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 ring-1 ring-line">
                    <x-icon name="check" class="size-4 text-verified" />{{ __('home.promise_'.$promise) }}
                </li>
            @endforeach
        </ul>
    </section>

    {{-- For professionals --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6" aria-labelledby="pros-title">
        <div class="relative overflow-hidden rounded-[2rem] bg-orange px-5 py-8 sm:px-10 sm:py-12">
            <x-paw class="absolute -right-6 -bottom-6 size-40 -rotate-12 text-white/20" />
            <x-paw class="absolute top-6 right-1/3 size-10 rotate-12 text-white/25" />
            <div class="relative grid items-center gap-6 sm:gap-8 lg:grid-cols-[1.2fr_1fr]">
                <div>
                    <p class="inline-flex items-center gap-2 text-sm font-extrabold tracking-wider text-brown-dark uppercase"><x-icon name="building" class="size-4" />{{ __('home.pros_eyebrow') }}</p>
                    <h2 id="pros-title" class="mt-2 font-display text-3xl font-semibold text-brown-dark sm:text-4xl">{{ __('home.pros_title') }}</h2>
                    <p class="mt-3 text-base leading-relaxed sm:text-lg text-brown-dark">{{ __('home.pros_text') }}</p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <x-button variant="secondary" size="lg" :href="localized_route('register', ['type' => 'pro'])" icon-right="arrow-right">{{ __('home.pros_cta') }}</x-button>
                        <x-button variant="ghost" size="lg" :href="localized_route('search')" class="text-brown-dark hover:bg-white/30">{{ __('home.pros_find') }}</x-button>
                    </div>
                </div>
                <ul class="space-y-2 sm:space-y-3">
                    @foreach (['claim', 'badge', 'reply', 'free'] as $benefit)
                        <li class="flex items-start gap-3 rounded-2xl bg-white/85 p-3 text-sm text-brown-dark sm:p-4 sm:text-base">
                            <span class="flex size-6 shrink-0 sm:size-8 items-center justify-center rounded-full bg-brown text-cream-light"><x-icon name="check" class="size-4" /></span>
                            <span class="font-semibold">{{ __('home.pros_benefit_'.$benefit) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</x-layouts.app>
