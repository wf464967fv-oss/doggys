{{--
    Rounded white card. <x-card>…</x-card>, <x-card as="article" interactive padding="lg">…</x-card>
    Props: as (tag), padding none|sm|md|lg, interactive (hover lift, for clickable cards).
--}}
@props(['as' => 'div', 'padding' => 'md', 'interactive' => false])

@php
    $paddings = ['none' => '', 'sm' => 'p-4', 'md' => 'p-5 sm:p-6', 'lg' => 'p-6 sm:p-8'];
@endphp

<{{ $as }} {{ $attributes->class([
    'rounded-3xl bg-white ring-1 ring-line shadow-soft',
    $paddings[$padding] ?? $paddings['md'],
    'transition duration-200 hover:-translate-y-0.5 hover:shadow-lift hover:ring-brown-mid/60' => $interactive,
]) }}>
    {{ $slot }}
</{{ $as }}>
