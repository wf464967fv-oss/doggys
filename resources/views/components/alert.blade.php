{{--
    Message box. <x-alert variant="success" :title="__('…')">Body text</x-alert>
    Props: variant success|error|warning|info, title. Errors are announced (role="alert"), others politely (role="status").
--}}
@props(['variant' => 'info', 'title' => null])

@php
    $variants = [
        'success' => ['bg-verified-soft text-verified ring-verified/25', 'check-circle'],
        'error' => ['bg-rose-soft text-rose-dark ring-rose/30', 'alert'],
        'warning' => ['bg-orange-soft text-orange-dark ring-orange/40', 'alert'],
        'info' => ['bg-regulated-soft text-regulated ring-regulated/25', 'info'],
    ];
    [$classes, $icon] = $variants[$variant] ?? $variants['info'];
@endphp

<div role="{{ $variant === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['flex gap-3 rounded-2xl p-4 ring-1 ring-inset', $classes]) }}>
    <x-icon :name="$icon" class="mt-0.5 size-5" />
    <div class="min-w-0 space-y-1 text-sm leading-relaxed">
        @if ($title)
            <p class="font-bold">{{ $title }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="text-brown-dark">{{ $slot }}</div>
        @endif
    </div>
</div>
