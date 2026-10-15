<x-layouts.app :title="__('blog.meta_title')" :description="__('blog.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <div class="pointer-events-none absolute -top-24 -right-24 size-72 rounded-full bg-orange/15 blur-2xl"></div>
        <x-paw class="absolute top-10 right-[8%] size-10 rotate-12 text-orange/25" />

        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-8 sm:px-6 sm:pt-8 sm:pb-12">
            <x-breadcrumbs :items="[__('blog.breadcrumb_home') => localized_route('home'), __('blog.breadcrumb_guides') => null]" />

            <x-section-title as="h1" class="mt-6 sm:mt-8" :eyebrow="__('blog.eyebrow')" :title="__('blog.title')" :subtitle="__('blog.subtitle')" />

            @if ($guides->isNotEmpty())
                <div class="mt-8" role="group" aria-labelledby="guide-topics">
                    <p id="guide-topics" class="text-sm font-bold text-brown">
                        {{ __('blog.jump_to') }} · <span class="font-semibold text-muted">{{ trans_choice('blog.count', $guides->count(), ['count' => $guides->count()]) }}</span>
                    </p>
                    <ul class="-mx-4 mt-3 flex gap-2 overflow-x-auto px-4 py-1 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0 sm:py-0">
                        @foreach (['' => ['icon' => 'book']] + $themes as $theme => $style)
                            <li class="shrink-0">
                                <button type="button" data-guide-filter="{{ $theme }}" aria-pressed="{{ $theme === '' ? 'true' : 'false' }}"
                                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-bold whitespace-nowrap text-brown ring-1 ring-line ring-inset transition hover:bg-cream hover:ring-brown-mid aria-pressed:bg-brown aria-pressed:text-cream-light aria-pressed:ring-brown">
                                    <x-icon :name="$style['icon']" class="size-4" />
                                    {{ $theme === '' ? __('blog.filter_all') : __('blog.categories.'.$theme) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pt-4 pb-16 sm:px-6 sm:pb-20">
        @if ($guides->isNotEmpty())
            <ul data-guide-grid class="grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                {{-- A wide first card when it makes the rest fill complete rows of 2 and 3 (13 guides = 1 + 12). --}}
                @php($featureFirst = $guides->count() > 1 && ($guides->count() - 1) % 6 === 0)
                @foreach ($guides as $guide)
                    <li data-guide-category="{{ $guide->category }}" @class(['sm:col-span-2 lg:col-span-3' => $featureFirst && $loop->first])>
                        <x-guide-card :guide="$guide" heading="h2" :wide="$featureFirst && $loop->first" />
                    </li>
                @endforeach
            </ul>
        @else
            <x-empty-state icon="book" :title="__('blog.empty_title')" class="mt-10">
                {{ __('blog.empty_text') }}
                <x-slot:action>
                    <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @endif
    </div>
</x-layouts.app>
