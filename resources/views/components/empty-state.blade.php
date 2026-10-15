{{--
    Friendly empty state. <x-empty-state icon="paw" :title="__('dogs.empty_title')">Text<x-slot:action>…</x-slot:action></x-empty-state>
    Props: icon, title. Slots: default (description), action (buttons).
--}}
@props(['icon' => 'paw', 'title'])

<div {{ $attributes->class(['relative overflow-hidden rounded-3xl border-2 border-dashed border-brown-mid/50 bg-cream-light px-6 py-10 text-center']) }}>
    <x-paw class="absolute -top-3 -right-3 size-20 rotate-12 text-cream" />
    <div class="relative mx-auto flex size-16 items-center justify-center rounded-full bg-cream text-brown">
        <x-icon :name="$icon" class="size-8" />
    </div>
    <p class="relative mt-4 font-display text-xl font-semibold text-brown">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="relative mx-auto mt-2 max-w-md text-muted">{{ $slot }}</div>
    @endif
    @isset($action)
        <div class="relative mt-6 flex flex-wrap justify-center gap-3">{{ $action }}</div>
    @endisset
</div>
