<x-layouts.app :title="__('reviews.policy.meta_title')" :description="__('reviews.policy.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <x-paw class="absolute right-[26%] bottom-6 hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />
        <div class="relative mx-auto max-w-4xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8 sm:pb-12">
            <x-breadcrumbs :items="[__('directory.breadcrumb_home') => localized_route('home'), __('reviews.policy.breadcrumb') => null]" />
            <p class="mt-6 inline-flex items-center gap-1.5 text-sm font-extrabold tracking-wider text-orange-dark uppercase">
                <x-icon name="shield-check" class="size-4" />{{ __('reviews.policy.eyebrow') }}
            </p>
            <h1 class="mt-2 font-display text-3xl leading-tight font-semibold text-brown sm:text-5xl">{{ __('reviews.policy.title') }}</h1>
            <p class="mt-4 max-w-3xl text-lg leading-relaxed text-brown-dark">{{ __('reviews.policy.intro') }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
        <x-card as="section" class="bg-orange-soft" aria-labelledby="summary-title">
            <h2 id="summary-title" class="font-display text-2xl font-semibold text-brown">{{ __('reviews.policy.summary_title') }}</h2>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2" role="list">
                @foreach (__('reviews.policy.summary') as $point)
                    <li class="flex gap-3 rounded-2xl bg-white p-4 ring-1 ring-orange/30">
                        <x-icon name="check-circle" class="mt-0.5 size-5 shrink-0 text-verified" />
                        <span class="text-brown-dark">{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <div class="mt-10 space-y-10">
            @foreach (__('reviews.policy.sections') as $index => $section)
                <section aria-labelledby="policy-{{ $index }}">
                    <h2 id="policy-{{ $index }}" class="flex items-start gap-3 font-display text-2xl leading-tight font-semibold text-brown">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-cream text-base text-brown" aria-hidden="true">{{ $index + 1 }}</span>
                        <span class="pt-0.5">{{ $section['title'] }}</span>
                    </h2>
                    <div class="mt-3 space-y-3 text-base sm:pl-12 sm:text-lg leading-relaxed text-brown-dark">
                        @foreach ($section['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="on-dark relative mt-12 overflow-hidden rounded-3xl bg-brown p-6 text-cream-light shadow-soft sm:p-8 [&_:focus-visible]:outline-orange">
            <x-paw class="absolute -right-4 -bottom-4 size-28 -rotate-12 text-white/10" />
            <h2 class="relative font-display text-2xl font-semibold text-cream-light">{{ __('reviews.policy.cta_title') }}</h2>
            <p class="relative mt-2 text-cream">{{ __('reviews.policy.cta_text') }}</p>
            <x-button :href="localized_route('contact')" icon="mail" class="relative mt-5">{{ __('reviews.policy.cta') }}</x-button>
        </div>
    </div>
</x-layouts.app>
