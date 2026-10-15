@php
    $style = \App\Http\Controllers\GuideController::CATEGORIES[$guide->category] ?? ['icon' => 'book', 'tone' => 'bg-cream text-brown'];
    $updatedOn = $guide->updated_at->locale(app()->getLocale())->isoFormat('LL');
    $categoryUrl = fn ($category) => localized_route('categories.show', ['category' => $category->routeSlug()]);
    $isEmergency = $guide->translation_key === 'emergencies';
    $articleJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $guide->title,
        'description' => $guide->meta_description ?: $guide->excerpt,
        'inLanguage' => $guide->locale,
        'datePublished' => $guide->published_at->toIso8601String(),
        'dateModified' => $guide->updated_at->toIso8601String(),
        'mainEntityOfPage' => url()->current(),
        'image' => asset('images/og-default.png'),
        'author' => ['@type' => 'Organization', 'name' => __('blog.author'), 'url' => localized_route('guides')],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Doggys',
            'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-doggys.png')],
        ],
    ];
@endphp

<x-layouts.app :title="$guide->meta_title ?: $guide->title" :description="$guide->meta_description ?: $guide->excerpt" :alternates="$alternates" og-type="article">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $guide->published_at->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $guide->updated_at->toIso8601String() }}">
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">@json($articleJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
    </x-slot:head>

    <article>
        <header class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
            <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
            <x-paw class="absolute right-[22%] bottom-6 hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />
            <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8 sm:pb-14">
                <x-breadcrumbs :items="[
                    __('blog.breadcrumb_home') => localized_route('home'),
                    __('blog.breadcrumb_guides') => localized_route('guides'),
                    $guide->title => null,
                ]" />

                <div class="mt-6 max-w-3xl sm:mt-8">
                    <a href="{{ localized_route('guides') }}#{{ $guide->category }}" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white/90 py-1 pr-3.5 pl-1.5 text-sm font-bold text-brown ring-1 ring-line transition hover:ring-brown-mid">
                        <span class="flex size-7 items-center justify-center rounded-full {{ $style['tone'] }}"><x-icon :name="$style['icon']" class="size-4" /></span>
                        {{ __('blog.categories.'.$guide->category) }}
                    </a>
                    <h1 class="mt-4 font-display text-3xl leading-tight font-semibold break-words hyphens-auto text-brown sm:text-4xl lg:text-5xl">{{ $guide->title }}</h1>
                    <p class="mt-4 text-lg leading-relaxed text-muted sm:text-xl">{{ $guide->excerpt }}</p>
                    <p class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm font-bold text-brown">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="pencil" class="size-4 text-orange-dark" />{{ __('blog.author') }}</span>
                        @if ($body['sources'] > 0)
                            <a href="#{{ $body['sources_id'] }}" class="inline-flex min-h-11 items-center gap-1.5 underline decoration-orange/60 decoration-2 underline-offset-4 hover:decoration-brown">
                                <x-icon name="shield-check" class="size-4 text-orange-dark" />{{ trans_choice('blog.sources_count', $body['sources'], ['count' => $body['sources']]) }}
                            </a>
                        @endif
                        <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-orange-dark" />{{ __('blog.reading_time', ['minutes' => $guide->reading_minutes]) }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="size-4 text-orange-dark" /><time datetime="{{ $guide->updated_at->toDateString() }}">{{ __('blog.updated_on', ['date' => $updatedOn]) }}</time></span>
                    </p>
                </div>
            </div>
        </header>

        @if ($isEmergency)
            <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
                <section class="rounded-3xl bg-rose-soft p-5 ring-1 ring-rose/30 sm:p-6" aria-labelledby="emergency-title">
                    <h2 id="emergency-title" class="flex items-center gap-2 font-display text-xl font-semibold text-rose-dark sm:text-2xl">
                        <x-icon name="siren" class="size-6" />{{ __('blog.emergency_title') }}
                    </h2>
                    <p class="mt-2 leading-relaxed text-brown-dark">{{ __('blog.emergency_text') }}</p>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        @foreach ($proCategories->sortBy(fn ($category) => array_search($category->slug, $guide->related_categories, true)) as $category)
                            <x-button :href="$categoryUrl($category)" :icon="$category->icon" :variant="$loop->first ? 'danger' : 'soft'">{{ $category->label() }}</x-button>
                        @endforeach
                        <x-button :href="localized_route('categories.index')" variant="soft" icon="search">{{ __('blog.emergency_all') }}</x-button>
                    </div>
                </section>
            </div>
        @endif

        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-8 sm:px-6 sm:py-14 lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-14">
            @if (count($body['toc']) > 1)
                <aside class="lg:order-2">
                    <details data-toc class="group rounded-3xl bg-white px-5 py-2 ring-1 ring-line shadow-soft lg:sticky lg:top-24 lg:py-4">
                        <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-2 font-display text-lg font-semibold text-brown lg:cursor-default [&::-webkit-details-marker]:hidden">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="book" class="size-5 text-orange-dark" />{{ __('blog.toc_title') }}
                                <span class="font-sans text-sm font-semibold text-muted">· {{ trans_choice('blog.toc_parts', count($body['toc']), ['count' => count($body['toc'])]) }}</span>
                            </span>
                            <x-icon name="chevron-down" class="size-5 transition group-open:rotate-180 lg:hidden" />
                        </summary>
                        <nav aria-label="{{ __('blog.toc_title') }}" class="pb-3">
                            <ol class="mt-1 space-y-1 border-l-2 border-line">
                                @foreach ($body['toc'] as $entry)
                                    <li>
                                        <a href="#{{ $entry['id'] }}" class="-ml-0.5 flex min-h-11 items-center border-l-2 border-transparent py-1.5 pl-4 text-[0.95rem] leading-snug font-semibold text-brown-dark transition hover:border-orange hover:text-brown">{{ $entry['title'] }}</a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    </details>
                </aside>
            @endif

            <div class="min-w-0 lg:order-1">
                <div class="prose-doggys">
                    {!! $body['html'] !!}
                </div>

                <p class="mt-10 flex max-w-[36rem] gap-3 rounded-3xl bg-regulated-soft p-5 leading-relaxed text-brown-dark ring-1 ring-regulated/20">
                    <x-icon name="stethoscope" class="mt-0.5 size-6 shrink-0 text-regulated" />
                    <span>{{ __('blog.vet_notice') }}</span>
                </p>
            </div>
        </div>
    </article>

    @if ($proCategories->count() >= 2)
        <section class="on-dark relative overflow-hidden bg-brown py-12 text-cream-light sm:py-16" aria-labelledby="find-pro-title">
            <x-paw class="absolute top-6 right-[6%] size-16 rotate-12 text-white/10" />
            <x-paw class="absolute bottom-6 left-[3%] size-10 -rotate-12 text-white/10" />
            <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
                <h2 id="find-pro-title" class="flex items-center gap-3 font-display text-2xl font-semibold text-cream-light sm:text-3xl">
                    <span class="flex size-11 items-center justify-center rounded-full bg-orange text-brown-dark"><x-icon name="map-pin" class="size-6" /></span>
                    {{ __('blog.find_pro_title') }}
                </h2>
                <p class="mt-2 max-w-2xl text-cream/90">{{ __('blog.find_pro_text') }}</p>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($proCategories as $category)
                        <li>
                            <a href="{{ $categoryUrl($category) }}" class="group flex h-full min-h-11 items-center gap-3 rounded-2xl bg-white/95 p-3 text-brown ring-1 ring-white/20 transition hover:bg-white">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-cream text-brown transition group-hover:bg-orange group-hover:text-brown-dark"><x-icon :name="$category->icon" class="size-6" /></span>
                                <span class="font-display text-lg leading-snug font-semibold">{{ $category->label() }}</span>
                                <x-icon name="arrow-right" class="ml-auto size-5 shrink-0 text-orange-dark transition group-hover:translate-x-0.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">
                    <x-button :href="localized_route('categories.index')" icon="search">{{ __('blog.find_pro_all') }}</x-button>
                </div>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16" aria-labelledby="related-title">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-title id="related-title" :title="__('blog.related_title')" />
                <x-button variant="soft" size="sm" :href="localized_route('guides')" icon="book">{{ __('blog.all_guides') }}</x-button>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                @foreach ($related as $relatedGuide)
                    <x-guide-card :guide="$relatedGuide" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
