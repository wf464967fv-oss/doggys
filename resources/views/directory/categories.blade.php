<x-layouts.app :title="__('directory.categories.title')" :description="__('directory.categories.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8">
            <x-breadcrumbs :items="[__('directory.breadcrumb_home') => localized_route('home'), __('directory.categories.breadcrumb') => null]" />
            <div class="mt-4 max-w-3xl">
                <h1 class="font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl lg:text-5xl">{{ __('directory.categories.h1') }}</h1>
                <p class="mt-4 text-lg leading-relaxed text-muted">{{ trans_choice('directory.categories.intro', $total, ['count' => $total]) }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
        {{-- One continuous grid, most represented specialities first. --}}
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($available as $category)
                <li>
                    <a href="{{ localized_route('categories.show', ['category' => $category->routeSlug()]) }}"
                        class="group flex h-full flex-col items-start gap-2 rounded-3xl bg-white p-3 ring-1 ring-line shadow-soft transition duration-200 hover:-translate-y-0.5 hover:shadow-lift hover:ring-brown-mid/60 sm:p-4">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cream text-brown transition group-hover:bg-orange group-hover:text-brown-dark sm:size-12">
                            <x-icon :name="$category->icon" class="size-6" />
                        </span>
                        <span class="text-xs font-extrabold tracking-wider text-orange-dark uppercase">{{ __('categories.groups.'.$category->group->value) }}</span>
                        <span class="block font-display text-base leading-tight font-semibold text-brown hyphens-auto sm:text-lg">{{ $category->label() }}</span>
                        <span class="hidden text-sm leading-snug text-muted sm:block">{{ $category->summary() }}</span>
                        <span class="mt-auto inline-flex rounded-full bg-cream-light px-2.5 py-0.5 text-xs font-bold text-brown ring-1 ring-line">
                            {{ trans_choice('directory.categories.count', $category->providers_count, ['count' => $category->providers_count]) }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($comingSoon->isNotEmpty())
            <section class="mt-12 rounded-3xl bg-cream p-5 sm:p-8" aria-labelledby="coming-title">
                <h2 id="coming-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.categories.coming_title') }}</h2>
                <p class="mt-2 max-w-2xl text-brown-dark">{{ __('directory.categories.coming_text') }}</p>
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($comingSoon as $category)
                        <li class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-bold text-brown ring-1 ring-line">
                            <x-icon :name="$category->icon" class="size-4 text-orange-dark" />{{ $category->label() }}
                        </li>
                    @endforeach
                </ul>
                <x-button variant="secondary" class="mt-6" :href="localized_route('register', ['type' => 'pro'])" icon="plus">{{ __('directory.categories.coming_cta') }}</x-button>
            </section>
        @endif
    </div>
</x-layouts.app>
