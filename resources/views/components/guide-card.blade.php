{{--
    Clickable guide card (guides index, related guides). Compact (icon on the side) on phones, illustrated band from 640px.
    <x-guide-card :guide="$post" />   $post: App\Models\Post with translation_key, slug, title, excerpt, category, reading_minutes.
    Props: guide, heading (h2|h3, default h3), wide (horizontal layout at every width, for a card spanning the grid).
--}}
@props(['guide', 'heading' => 'h3', 'wide' => false])

@php
    $style = \App\Http\Controllers\GuideController::CATEGORIES[$guide->category] ?? ['icon' => 'book', 'tone' => 'bg-cream text-brown'];
    $icon = \App\Http\Controllers\GuideController::ICONS[$guide->translation_key] ?? $style['icon'];
@endphp

<x-card as="article" padding="none" interactive {{ $attributes->class(['group relative flex h-full flex-row', 'sm:flex-col' => ! $wide]) }}>
    <div @class([
        'relative flex w-20 shrink-0 items-start justify-center overflow-hidden rounded-l-3xl pt-5',
        $wide ? 'sm:w-48 sm:items-center sm:pt-0 lg:w-64' : 'sm:h-28 sm:w-auto sm:items-center sm:justify-start sm:rounded-t-3xl sm:rounded-bl-none sm:px-6 sm:pt-0',
        $style['tone'],
    ]) aria-hidden="true">
        <x-paw class="absolute -right-3 -bottom-4 hidden size-24 rotate-12 opacity-15 sm:block" />
        <x-paw class="absolute top-3 right-20 hidden size-8 -rotate-12 opacity-10 sm:block" />
        <span class="relative flex size-12 items-center justify-center rounded-2xl bg-white/85 shadow-soft sm:size-16">
            <x-icon :name="$icon" class="size-6 sm:size-8" />
        </span>
    </div>
    <div @class(['flex min-w-0 flex-1 flex-col p-4 sm:p-6', 'lg:p-8' => $wide])>
        <p class="text-xs font-extrabold tracking-wider text-muted uppercase">{{ __('blog.categories.'.$guide->category) }}</p>
        <{{ $heading }} @class(['mt-1 font-display text-lg leading-snug font-semibold break-words hyphens-auto text-brown sm:mt-1.5 sm:text-xl', 'lg:text-2xl' => $wide])>
            <a href="{{ localized_route('guides.show', ['slug' => $guide->slug]) }}" class="after:absolute after:inset-0 after:rounded-3xl focus-visible:outline-none focus-visible:after:outline-3 focus-visible:after:-outline-offset-3 focus-visible:after:outline-brown">{{ $guide->title }}</a>
        </{{ $heading }}>
        <p @class(['mt-2 line-clamp-3 flex-1 text-[0.95rem] leading-relaxed text-muted sm:line-clamp-none sm:text-base', 'max-w-2xl' => $wide])>{{ $guide->excerpt }}</p>
        <p class="mt-3 flex items-center justify-between gap-3 text-sm font-bold text-brown sm:mt-4">
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-orange-dark" />{{ __('blog.reading_time', ['minutes' => $guide->reading_minutes]) }}</span>
            <span class="hidden items-center gap-1 text-orange-dark transition group-hover:translate-x-0.5 sm:inline-flex" aria-hidden="true">{{ __('blog.read_guide') }}<x-icon name="arrow-right" class="size-4" /></span>
        </p>
    </div>
</x-card>
