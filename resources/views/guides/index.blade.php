<x-layouts.app :title="__('blog.meta_title')" :description="__('blog.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <div class="pointer-events-none absolute -top-24 -right-24 size-72 rounded-full bg-orange/15 blur-2xl"></div>
        <x-paw class="absolute top-10 right-[8%] size-10 rotate-12 text-orange/25" />
        <x-paw class="absolute bottom-8 left-[46%] hidden size-8 -rotate-12 text-brown-mid/25 sm:block" />

        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-12 sm:px-6 sm:pt-8 sm:pb-16">
            <x-breadcrumbs :items="[__('blog.breadcrumb_home') => localized_route('home'), __('blog.breadcrumb_guides') => null]" />

            <x-section-title as="h1" class="mt-8" :eyebrow="__('blog.eyebrow')" :title="__('blog.title')" :subtitle="__('blog.subtitle')" />

            @if ($groups->isNotEmpty())
                <nav class="mt-8" aria-labelledby="guide-topics">
                    <p id="guide-topics" class="text-sm font-bold text-brown">
                        {{ __('blog.jump_to') }} · <span class="font-semibold text-muted">{{ trans_choice('blog.count', $count, ['count' => $count]) }}</span>
                    </p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($groups as $category => $guides)
                            <li>
                                <a href="#{{ $category }}" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-bold text-brown ring-1 ring-line transition hover:bg-cream hover:ring-brown-mid">
                                    <x-icon :name="\App\Http\Controllers\GuideController::CATEGORIES[$category]['icon']" class="size-4 text-orange-dark" />
                                    {{ __('blog.categories.'.$category) }}
                                    <span class="rounded-full bg-cream-light px-2 py-0.5 text-xs text-muted">{{ $guides->count() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pt-4 pb-16 sm:px-6 sm:pb-20">
        @forelse ($groups as $category => $guides)
            @php($style = \App\Http\Controllers\GuideController::CATEGORIES[$category])
            <section id="{{ $category }}" class="scroll-mt-24 pt-10" aria-labelledby="{{ $category }}-title">
                <h2 id="{{ $category }}-title" class="flex items-center gap-3 font-display text-2xl font-semibold text-brown sm:text-3xl">
                    <span class="flex size-11 items-center justify-center rounded-2xl {{ $style['tone'] }}"><x-icon :name="$style['icon']" class="size-6" /></span>
                    {{ __('blog.categories.'.$category) }}
                </h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($guides as $guide)
                        <x-guide-card :guide="$guide" />
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state icon="book" :title="__('blog.empty_title')" class="mt-10">
                {{ __('blog.empty_text') }}
                <x-slot:action>
                    <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @endforelse
    </div>
</x-layouts.app>
