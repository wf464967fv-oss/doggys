<x-layouts.app :title="__('directory.categories.title')" :description="__('directory.categories.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8">
            <x-breadcrumbs :items="[__('directory.breadcrumb_home') => localized_route('home'), __('directory.categories.breadcrumb') => null]" />
            <div class="mt-6 max-w-3xl">
                <h1 class="font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl lg:text-5xl">{{ __('directory.categories.h1') }}</h1>
                <p class="mt-4 text-lg leading-relaxed text-muted">{{ trans_choice('directory.categories.intro', $total, ['count' => $total]) }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 sm:py-14">
        @foreach ($categoryGroups as $group => $categories)
            <section aria-labelledby="group-{{ $group }}">
                <h2 id="group-{{ $group }}" class="flex items-center gap-2 font-display text-2xl font-semibold text-brown">
                    <span class="h-2 w-6 rounded-full bg-orange"></span>{{ __('categories.groups.'.$group) }}
                </h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ localized_route('categories.show', ['category' => $category->routeSlug()]) }}"
                                class="group flex h-full items-start gap-3 rounded-3xl bg-white p-4 ring-1 ring-line shadow-soft transition duration-200 hover:-translate-y-0.5 hover:shadow-lift hover:ring-brown-mid/60">
                                <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-cream text-brown transition group-hover:bg-orange group-hover:text-brown-dark">
                                    <x-icon :name="$category->icon" class="size-6" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-display text-lg leading-snug font-semibold text-brown">{{ $category->label() }}</span>
                                    <span class="mt-0.5 block text-sm leading-snug text-muted">{{ $category->summary() }}</span>
                                    <span class="mt-2 inline-flex rounded-full bg-cream-light px-2.5 py-0.5 text-xs font-bold text-brown ring-1 ring-line">
                                        {{ $category->providers_count > 0 ? trans_choice('directory.categories.count', $category->providers_count, ['count' => $category->providers_count]) : __('directory.categories.coming') }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.app>
