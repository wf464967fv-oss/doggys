{{--
    Button or link styled as a button (min. 44px touch target).
    <x-button>Save</x-button>
    <x-button variant="secondary" :href="localized_route('register')" icon="plus">Add</x-button>
    Props: variant primary|secondary|ghost|soft|danger, size sm|md|lg, href (renders <a>), type, icon, icon-right, full (w-full).
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'icon' => null,
    'iconRight' => null,
    'full' => false,
])

@php
    $variants = [
        'primary' => 'bg-orange text-brown-dark shadow-[0_3px_0_0_var(--color-orange-dark)] hover:bg-[#f7ad5c] active:translate-y-0.5 active:shadow-[0_1px_0_0_var(--color-orange-dark)]',
        'secondary' => 'bg-brown text-cream-light shadow-[0_3px_0_0_var(--color-brown-dark)] hover:bg-[#7a4825] active:translate-y-0.5 active:shadow-[0_1px_0_0_var(--color-brown-dark)]',
        'ghost' => 'bg-transparent text-brown hover:bg-cream/70',
        'soft' => 'bg-white text-brown ring-2 ring-line hover:ring-brown-mid',
        'danger' => 'bg-rose-dark text-white shadow-[0_3px_0_0_#7d1229] hover:bg-[#9e1b37] active:translate-y-0.5 active:shadow-[0_1px_0_0_#7d1229]',
    ];
    $sizes = [
        'sm' => 'min-h-11 px-4 text-sm gap-1.5',
        'md' => 'min-h-12 px-5 text-base gap-2',
        'lg' => 'min-h-14 px-7 text-lg gap-2.5',
    ];
    $classes = 'inline-flex items-center justify-center rounded-full font-display font-semibold tracking-wide transition duration-150 select-none disabled:cursor-not-allowed disabled:opacity-60 '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']).($full ? ' w-full' : '');
    $iconClass = $size === 'lg' ? 'size-6' : 'size-5';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" :class="$iconClass" />@endif
        <span>{{ $slot }}</span>
        @if ($iconRight)<x-icon :name="$iconRight" :class="$iconClass" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" :class="$iconClass" />@endif
        <span>{{ $slot }}</span>
        @if ($iconRight)<x-icon :name="$iconRight" :class="$iconClass" />@endif
    </button>
@endif
