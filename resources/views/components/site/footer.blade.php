<footer class="relative mt-12 overflow-hidden sm:mt-16 bg-brown-dark text-cream [&_:focus-visible]:outline-orange">
    <x-paw class="absolute -top-6 right-8 size-28 rotate-12 text-white/5" />
    <x-paw class="absolute bottom-10 -left-6 size-20 -rotate-12 text-white/5" />

    <div class="relative mx-auto grid max-w-6xl grid-cols-2 gap-x-6 gap-y-8 px-4 py-12 sm:px-6 lg:grid-cols-4 lg:gap-10">
        <div class="col-span-2 space-y-4 lg:col-span-1">
            <a href="{{ localized_route('home') }}" class="inline-flex items-center gap-3 rounded-2xl" aria-label="{{ __('ui.home_link') }}">
                <img src="{{ asset('images/logo-mark-96.webp') }}" alt="" width="48" height="48" loading="lazy" class="size-12">
                <span class="font-display text-3xl font-semibold text-orange">Doggys</span>
            </a>
            <p class="font-display text-lg text-cream">{{ __('ui.slogan') }}</p>
            <p class="text-sm leading-relaxed text-cream/80">{{ __('ui.footer_pitch') }}</p>
        </div>

        <nav aria-labelledby="footer-explore">
            <h2 id="footer-explore" class="font-display text-lg font-semibold text-orange">{{ __('ui.footer_explore') }}</h2>
            <ul class="mt-3 space-y-1">
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('search') }}">{{ __('ui.nav_find') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('guides') }}">{{ __('ui.nav_guides') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('register', ['type' => 'pro']) }}">{{ __('ui.footer_for_pros') }}</a></li>
            </ul>
        </nav>

        <nav aria-labelledby="footer-trust">
            <h2 id="footer-trust" class="font-display text-lg font-semibold text-orange">{{ __('ui.footer_trust') }}</h2>
            <ul class="mt-3 space-y-1">
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('reviews.policy') }}">{{ __('ui.footer_reviews_policy') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('contact') }}">{{ __('ui.footer_contact') }}</a></li>
            </ul>
        </nav>

        <nav aria-labelledby="footer-legal">
            <h2 id="footer-legal" class="font-display text-lg font-semibold text-orange">{{ __('ui.footer_legal') }}</h2>
            <ul class="mt-3 space-y-1">
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('legal.notice') }}">{{ __('ui.footer_legal_notice') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('legal.privacy') }}">{{ __('ui.footer_privacy') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('legal.terms') }}">{{ __('ui.footer_terms') }}</a></li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white hover:underline" href="{{ localized_route('legal.cookies') }}">{{ __('ui.footer_cookies') }}</a></li>
            </ul>
        </nav>
    </div>

    <div class="relative border-t border-white/10">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-cream/80 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <p>&copy; {{ date('Y') }} Doggys · {{ __('ui.footer_free') }}</p>
            <p class="flex flex-wrap items-center gap-x-4">
                <span>{!! __('ui.footer_osm', ['link' => '<a class="inline-flex min-h-11 items-center underline hover:text-white" href="https://www.openstreetmap.org/copyright" rel="noopener">OpenStreetMap</a>']) !!}</span>
                <span>{!! __('ui.footer_geonames', ['link' => '<a class="inline-flex min-h-11 items-center underline hover:text-white" href="https://www.geonames.org/" rel="noopener">GeoNames</a>']) !!}</span>
                <span>{{ __('ui.footer_made_by') }} Novexa Digital</span>
            </p>
        </div>
    </div>
</footer>
