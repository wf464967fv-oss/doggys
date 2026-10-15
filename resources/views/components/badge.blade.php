{{--
    Small pill label.
    <x-badge variant="verified" />    green "Pro verified" (BCE + identity checked), default label & icon
    <x-badge variant="regulated" />   blue "Licensed" (regulated profession checked), default label & icon
    <x-badge variant="orange" icon="paw">Puppy</x-badge>
    Props: variant verified|regulated|neutral|orange|rose|brown, icon.
--}}
@props(['variant' => 'neutral', 'icon' => null])

@php
    $variants = [
        'verified' => ['bg-verified-soft text-verified ring-verified/25', 'shield-check', __('ui.badge_verified')],
        'regulated' => ['bg-regulated-soft text-regulated ring-regulated/25', 'badge-check', __('ui.badge_regulated')],
        'neutral' => ['bg-cream-light text-muted ring-line', null, null],
        'orange' => ['bg-orange-soft text-orange-dark ring-orange/30', null, null],
        'rose' => ['bg-rose-soft text-rose-dark ring-rose/30', null, null],
        'brown' => ['bg-brown text-cream-light ring-brown', null, null],
    ];
    [$classes, $defaultIcon, $defaultLabel] = $variants[$variant] ?? $variants['neutral'];
    $icon ??= $defaultIcon;
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset whitespace-nowrap', $classes]) }}
    @if (in_array($variant, ['verified', 'regulated'], true)) title="{{ __('ui.badge_'.$variant.'_help') }}" @endif>
    @if ($icon)<x-icon :name="$icon" class="size-3.5" />@endif
    <span>{{ $slot->isEmpty() ? $defaultLabel : $slot }}</span>
</span>
