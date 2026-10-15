{{--
    Clickable guide card (guides index, related guides).
    <x-guide-card :guide="$post" />   $post: App\Models\Post with slug, title, excerpt, category, reading_minutes.
    Props: guide, heading (h2|h3, default h3).
--}}
@props(['guide', 'heading' => 'h3'])

@php
    $style = \App\Http\Controllers\GuideController::CATEGORIES[$guide->category] ?? ['icon' => 'book', 'tone' => 'bg-cream text-brown'];
    $icon = \App\Http\Controllers\GuideController::ICONS[$guide->translation_key] ?? $style['icon'];
@endphp

<x-card as="article" padding="none" interactive {{ $attributes->class(['group relative flex h-full flex-col overflow-hidden']) }}>
    <div class="relative flex h-28 items-center overflow-hidden px-6 {{ $style['tone'] }}" aria-hidden="true">
        <x-paw class="absolute -right-3 -bottom-4 size-24 rotate-12 opacity-15" />
        <x-paw class="absolute top-3 right-20 size-8 -rotate-12 opacity-10" />
        <span class="relative flex size-16 items-center justify-center rounded-2xl bg-white/85 shadow-soft">
            <x-icon :name="$icon" class="size-8" />
        </span>
    </div>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <p class="text-xs font-extrabold tracking-wider text-muted uppercase">{{ __('blog.categories.'.$guide->category) }}</p>
        <{{ $heading }} class="mt-1.5 font-display text-xl leading-snug font-semibold break-words hyphens-auto text-brown">
            <a href="{{ localized_route('guides.show', ['slug' => $guide->slug]) }}" class="after:absolute after:inset-0 after:rounded-3xl focus-visible:outline-none focus-visible:after:outline-3 focus-visible:after:outline-offset-2 focus-visible:after:outline-brown">{{ $guide->title }}</a>
        </{{ $heading }}>
        <p class="mt-2 flex-1 leading-relaxed text-muted">{{ $guide->excerpt }}</p>
        <p class="mt-4 flex items-center justify-between gap-3 text-sm font-bold text-brown">
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-orange-dark" />{{ __('blog.reading_time', ['minutes' => $guide->reading_minutes]) }}</span>
            <span class="inline-flex items-center gap-1 text-orange-dark transition group-hover:translate-x-0.5" aria-hidden="true">{{ __('blog.read_guide') }}<x-icon name="arrow-right" class="size-4" /></span>
        </p>
    </div>
</x-card>
