@use('Illuminate\Support\Str')
@php
    $locale = app()->getLocale();
    $url = localized_route('providers.show', $provider);
    $street = trim($provider->street.' '.$provider->house_number);
    $address = collect([$street, trim($provider->postal_code.' '.$provider->city)])->filter()->join(', ');
    $hours = $provider->openingHoursLines();
    $website = $provider->safeWebsite();
    $isPlace = $provider->isPlace();
    $directions = 'https://www.openstreetmap.org/directions?route='.rawurlencode(';'.$provider->lat.','.$provider->lng);

    // Title "Name – Category à City" (≤ 50 characters before " | Doggys"): the category gives way first, never the name.
    $categoryLabel = $category?->label() ?? __('directory.provider.fallback_category');
    $makeTitle = fn (string $label) => $provider->city
        ? __('directory.provider.title', ['name' => $provider->name, 'category' => $label, 'city' => $provider->city])
        : __('directory.provider.title_no_city', ['name' => $provider->name, 'category' => $label]);
    $title = $makeTitle($categoryLabel);
    if (mb_strlen($title) > 50) {
        $room = mb_strlen($categoryLabel) - (mb_strlen($title) - 50);
        $title = $room >= 6 ? $makeTitle(Str::limit($categoryLabel, $room - 1, '…')) : $provider->name.($provider->city ? ' – '.$provider->city : '');
    }

    // Meta description built from what the listing really has.
    $details = collect([
        'address' => filled($provider->street),
        'phone' => (bool) $provider->phone,
        'website' => (bool) $website,
        'hours' => $hours !== [],
        'reviews' => $provider->reviews_count > 0,
    ])->filter()->keys()->map(fn (string $key) => __('directory.provider.meta_parts.'.$key));
    $description = __('directory.provider.meta_intro', ['name' => $provider->name, 'category' => $categoryLabel, 'city' => $provider->city ?? __('directory.belgium')])
        .' '.($details->isNotEmpty()
            ? __('directory.provider.meta_details', ['details' => Str::ucfirst($details->join(', ', ' '.__('directory.and').' '))])
            : __('directory.provider.meta_fallback'));

    $jsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => $provider->schemaType(),
        '@id' => $url.'#business',
        'name' => $provider->name,
        'url' => $url,
        'description' => $provider->description,
        'telephone' => $provider->phone,
        'email' => $provider->email,
        'sameAs' => $website ? [$website] : null,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $street ?: null,
            'postalCode' => $provider->postal_code,
            'addressLocality' => $provider->city,
            'addressCountry' => 'BE',
        ]),
        'geo' => ['@type' => 'GeoCoordinates', 'latitude' => (float) $provider->lat, 'longitude' => (float) $provider->lng],
        'areaServed' => $provider->is_mobile && $provider->service_radius_km
            ? ['@type' => 'GeoCircle', 'geoMidpoint' => ['@type' => 'GeoCoordinates', 'latitude' => (float) $provider->lat, 'longitude' => (float) $provider->lng], 'geoRadius' => $provider->service_radius_km * 1000]
            : null,
        'openingHours' => $provider->schemaOpeningHours() ?: null,
        'aggregateRating' => $provider->reviews_count > 0
            ? ['@type' => 'AggregateRating', 'ratingValue' => (float) $provider->rating_avg, 'reviewCount' => $provider->reviews_count, 'bestRating' => 5, 'worstRating' => 1]
            : null,
    ]);

    $breadcrumbs = [__('directory.breadcrumb_home') => localized_route('home')];
    if ($category) {
        $breadcrumbs[$category->pluralLabel()] = localized_route('categories.show', ['category' => $category->routeSlug()]);
    }
    $breadcrumbs[$provider->name] = null;

    $reportOpen = $errors->hasAny(['type', 'message', 'email', 'form']);
    $mapMarker = [['lat' => (float) $provider->lat, 'lng' => (float) $provider->lng, 'name' => $provider->name, 'meta' => $provider->is_mobile ? __('directory.comes_to_you') : $address]];
    $mapLabel = __('directory.provider.map_label', ['name' => $provider->name]);
@endphp

<x-layouts.app :title="$title" :description="Str::limit($description, 155, '…')"
    :noindex="$provider->isThin() || $reviews->currentPage() > 1">
    <x-slot:head>
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
    </x-slot:head>

    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <x-paw class="absolute right-[24%] bottom-6 hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-8 sm:px-6 sm:pt-8 sm:pb-10">
            <x-breadcrumbs :items="$breadcrumbs" />

            <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-start">
                <span class="flex size-16 shrink-0 items-center justify-center rounded-3xl bg-white text-brown shadow-soft ring-1 ring-line sm:size-20" aria-hidden="true">
                    <x-icon :name="$category?->icon ?? 'paw'" class="size-8 sm:size-10" />
                </span>
                <div class="min-w-0">
                    <h1 class="font-display text-3xl leading-tight font-semibold break-words text-brown sm:text-4xl">{{ $provider->name }}</h1>
                    <p class="mt-2 flex items-start gap-1.5 text-brown-dark">
                        <x-icon :name="$provider->is_mobile ? 'van' : 'map-pin'" class="mt-1 size-4 shrink-0 text-orange-dark" />
                        {{ $provider->is_mobile ? __('directory.comes_to_you') : $address }}
                    </p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($provider->categories as $providerCategory)
                            <li>
                                <a href="{{ localized_route('categories.show', ['category' => $providerCategory->routeSlug()]) }}"
                                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full bg-white px-4 text-sm font-bold text-brown ring-1 ring-line transition hover:ring-brown-mid">
                                    <x-icon :name="$providerCategory->icon" class="size-4 text-orange-dark" />{{ $providerCategory->label() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($provider->isVerified() || $provider->reviews_count > 0)
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            @if ($provider->isRegulated())
                                <x-badge variant="regulated" />
                            @endif
                            @if ($provider->isVerified())
                                <x-badge variant="verified" />
                            @endif
                            @if ($provider->reviews_count > 0)
                                <a href="#reviews" class="inline-flex min-h-11 items-center rounded-full"><x-stars :rating="$provider->rating_avg" :count="$provider->reviews_count" /></a>
                            @endif
                        </div>
                    @endif

                    {{-- Main actions for dog owners --}}
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if ($provider->phone)
                            <x-button icon="phone" :href="'tel:'.$provider->phone">{{ __('directory.provider.call', ['phone' => $provider->phoneForDisplay()]) }}</x-button>
                        @endif
                        <x-button :variant="$provider->phone ? 'soft' : 'primary'" icon="route" :href="$directions" rel="nofollow noopener" target="_blank">
                            {{ __('directory.provider.directions') }}<span class="sr-only"> ({{ __('directory.new_window') }})</span>
                        </x-button>
                        @if ($website)
                            <x-button variant="soft" icon="globe" :href="$website" rel="nofollow noopener" target="_blank">
                                {{ __('directory.provider.website') }}<span class="sr-only"> ({{ __('directory.new_window') }})</span>
                            </x-button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-8">
        <div class="space-y-6">
            {{-- Contact --}}
            <x-card as="section" aria-labelledby="contact-title">
                <h2 id="contact-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.provider.contact') }}</h2>
                @if ($provider->description)
                    <p class="mt-3 leading-relaxed whitespace-pre-line text-brown-dark">{{ $provider->description }}</p>
                @endif
                <dl class="mt-4 divide-y divide-line">
                    <div class="flex gap-3 py-3">
                        <dt class="flex size-10 shrink-0 items-center justify-center rounded-full bg-cream text-brown">
                            <x-icon :name="$provider->is_mobile ? 'van' : 'map-pin'" class="size-5" /><span class="sr-only">{{ $provider->is_mobile ? __('directory.provider.service_area') : __('directory.provider.address') }}</span>
                        </dt>
                        <dd class="min-w-0 self-center text-brown-dark">
                            @if ($provider->is_mobile)
                                <span class="font-bold">{{ __('directory.comes_to_you') }}</span>
                                <span class="block text-sm text-muted">
                                    {{ $provider->service_radius_km
                                        ? __('directory.provider.service_radius', ['km' => $provider->service_radius_km, 'city' => $provider->city])
                                        : __('directory.provider.service_base', ['city' => $provider->city]) }}
                                </span>
                            @else
                                {{ $address }}
                            @endif
                        </dd>
                    </div>
                    @if ($provider->phone)
                        <div class="flex gap-3 py-3">
                            <dt class="flex size-10 shrink-0 items-center justify-center rounded-full bg-cream text-brown"><x-icon name="phone" class="size-5" /><span class="sr-only">{{ __('directory.provider.phone') }}</span></dt>
                            <dd class="self-center"><a href="tel:{{ $provider->phone }}" class="link inline-flex min-h-11 items-center">{{ $provider->phoneForDisplay() }}</a></dd>
                        </div>
                    @endif
                    @if ($provider->email)
                        <div class="flex gap-3 py-3">
                            <dt class="flex size-10 shrink-0 items-center justify-center rounded-full bg-cream text-brown"><x-icon name="mail" class="size-5" /><span class="sr-only">{{ __('directory.provider.email') }}</span></dt>
                            <dd class="min-w-0 self-center"><a href="mailto:{{ $provider->email }}" class="link inline-flex min-h-11 items-center break-all">{{ $provider->email }}</a></dd>
                        </div>
                    @endif
                    @if ($website)
                        <div class="flex gap-3 py-3">
                            <dt class="flex size-10 shrink-0 items-center justify-center rounded-full bg-cream text-brown"><x-icon name="globe" class="size-5" /><span class="sr-only">{{ __('directory.provider.website') }}</span></dt>
                            <dd class="min-w-0 self-center">
                                <a href="{{ $website }}" rel="nofollow noopener" target="_blank" class="link inline-flex min-h-11 items-center break-all">
                                    {{ Str::of($website)->after('://')->rtrim('/')->limit(48) }}<span class="sr-only"> ({{ __('directory.new_window') }})</span>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
                @unless ($provider->phone || $provider->email || $website)
                    <p class="mt-2 rounded-2xl bg-cream-light p-4 text-sm text-muted">{{ __($isPlace ? 'directory.provider.no_contact_place' : 'directory.provider.no_contact') }}</p>
                @endunless

                {{-- On phones the map follows the address; on large screens it sits in the side column. --}}
                <x-map :markers="$mapMarker" :circle="$provider->is_mobile ? $provider->service_radius_km : null" :label="$mapLabel" class="mt-4 h-56 lg:hidden" />
            </x-card>

            {{-- Opening hours --}}
            @if ($hours)
                <x-card as="section" aria-labelledby="hours-title">
                    <h2 id="hours-title" class="flex items-center gap-2 font-display text-2xl font-semibold text-brown"><x-icon name="clock" class="size-6 text-orange-dark" />{{ __('directory.provider.hours') }}</h2>
                    <ul class="mt-4 space-y-2">
                        @foreach ($hours as $line)
                            <li class="rounded-2xl bg-cream-light px-4 py-2.5 text-brown-dark">
                                {{ $line['hours'] }}
                                {{-- Free text typed by OpenStreetMap contributors, in an unknown language. --}}
                                @if ($line['note'])<span lang="und" class="text-muted">{{ $line['hours'] !== '' ? '('.$line['note'].')' : '« '.$line['note'].' »' }}</span>@endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-sm text-muted">{{ __('directory.provider.hours_hint') }}</p>
                </x-card>
            @endif

            {{-- Reviews: form, list, reports and replies (reviews work package) --}}
            @include('reviews._section')
        </div>

        <aside class="space-y-6">
            <x-map :markers="$mapMarker" :circle="$provider->is_mobile ? $provider->service_radius_km : null" :label="$mapLabel" class="hidden h-72 lg:block" />

            @unless ($isPlace)
                <x-card>
                    <p class="font-display text-xl font-semibold text-brown">{{ __('directory.provider.claim_title') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('directory.provider.claim_text') }}</p>
                    <x-button variant="soft" :href="localized_route('claim.create', ['provider' => $provider->slug])" icon="building" class="mt-4" full>{{ __('directory.provider.claim_cta') }}</x-button>
                </x-card>
            @endunless

            <x-card>
                <p class="font-display text-lg font-semibold text-brown">{{ __('directory.provider.trust_title') }}</p>
                @if ($provider->isVerified())
                    <ul class="mt-3 space-y-3">
                        @if ($provider->isRegulated())
                            <li><x-badge variant="regulated" /><p class="mt-1.5 text-sm text-muted">{{ __('ui.badge_regulated_help') }}</p></li>
                        @endif
                        <li><x-badge variant="verified" /><p class="mt-1.5 text-sm text-muted">{{ __('ui.badge_verified_help') }}</p></li>
                    </ul>
                @else
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __($isPlace ? 'directory.provider.not_verified_place' : 'directory.provider.not_verified') }}</p>
                @endif
                <p class="mt-4 border-t border-line pt-3 text-xs leading-relaxed text-muted">
                    @if ($provider->source === \App\Enums\ProviderSource::Osm)
                        {{ __('directory.provider.source_osm') }}
                        <a href="https://www.openstreetmap.org/{{ $provider->source_ref }}" rel="nofollow noopener" target="_blank" class="inline-flex min-h-11 items-center underline hover:text-brown">{{ __('directory.provider.source_osm_link') }}</a>
                    @else
                        {{ __('directory.provider.source_'.$provider->source->value) }}
                    @endif
                    @if ($provider->imported_at)
                        · {{ __('directory.provider.updated', ['date' => $provider->imported_at->locale($locale)->isoFormat('LL')]) }}
                    @endif
                </p>
            </x-card>
        </aside>

        {{-- Report an error / request removal: secondary, always last --}}
        <x-card as="section" id="report" aria-labelledby="report-title" class="lg:col-start-1">
            <details @if ($reportOpen) open @endif class="group">
                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                    <span>
                        <span id="report-title" class="block font-display text-xl font-semibold text-brown">{{ __('directory.report.title') }}</span>
                        <span class="mt-1 block text-sm text-muted">{{ __('directory.report.intro') }}</span>
                    </span>
                    <x-icon name="chevron-down" class="size-5 shrink-0 text-brown transition group-open:rotate-180" />
                </summary>
                <form method="POST" action="{{ localized_route('providers.report', $provider) }}" class="mt-5 space-y-4" novalidate>
                    @csrf
                    <fieldset>
                        <legend class="text-sm font-bold text-brown">{{ __('directory.report.type') }}</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach (\App\Enums\ProviderReportType::cases() as $type)
                                <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl bg-cream-light px-4 py-2 ring-2 ring-line transition has-checked:bg-orange-soft has-checked:ring-orange">
                                    <input type="radio" name="type" value="{{ $type->value }}" @checked(old('type', 'error') === $type->value) class="size-5 accent-orange-dark">
                                    <span class="font-semibold text-brown-dark">{{ __('directory.report.types.'.$type->value) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')<p class="mt-1.5 text-sm font-semibold text-rose-dark">{{ $message }}</p>@enderror
                    </fieldset>
                    <x-textarea name="message" :label="__('directory.report.message')" :hint="__('directory.report.message_hint')" rows="4" maxlength="2000" required />
                    <x-input name="email" type="email" :label="__('directory.report.email')" :hint="__('directory.report.email_hint')" autocomplete="email" maxlength="255" />
                    <x-antibot />
                    <p class="text-sm text-muted">{{ __('directory.report.privacy') }}</p>
                    <x-button icon="check">{{ __('directory.report.submit') }}</x-button>
                </form>
            </details>
        </x-card>
    </div>

    @if ($similar->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-14 sm:px-6 sm:pb-20" aria-labelledby="similar-title">
            <x-section-title id="similar-title" :eyebrow="__('directory.provider.similar_eyebrow')" :title="__('directory.provider.similar_title')" />
            <ul class="mt-6 grid gap-4 sm:grid-cols-2" role="list">
                @foreach ($similar as $other)
                    <li><x-provider-card :provider="$other" /></li>
                @endforeach
            </ul>
            @if ($category)
                <x-button variant="soft" class="mt-6" :href="localized_route('search', ['category' => $category->slug, 'lat' => round((float) $provider->lat, 5), 'lng' => round((float) $provider->lng, 5), 'radius' => 25])" icon-right="arrow-right">
                    {{ __('directory.provider.similar_more') }}
                </x-button>
            @endif
        </section>
    @endif

    {{-- Phones: call and directions always at hand. --}}
    <div class="h-20 sm:hidden" aria-hidden="true"></div>
    <div class="fixed inset-x-0 bottom-0 z-30 flex gap-2 border-t border-line bg-cream-light/95 p-3 backdrop-blur sm:hidden">
        @if ($provider->phone)
            <x-button icon="phone" :href="'tel:'.$provider->phone" class="flex-1">{{ __('directory.provider.call_short') }}</x-button>
        @endif
        <x-button :variant="$provider->phone ? 'soft' : 'primary'" icon="route" :href="$directions" rel="nofollow noopener" target="_blank" class="flex-1">
            {{ __('directory.provider.directions') }}<span class="sr-only"> ({{ __('directory.new_window') }})</span>
        </x-button>
    </div>
</x-layouts.app>
