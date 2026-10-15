{{--
    Frame for authentication pages: form card + warm side panel on large screens.
    <x-auth-shell :title="…" :subtitle="…">form<x-slot:footer>links</x-slot:footer></x-auth-shell>
    Props: title (the page h1), subtitle, icon.
--}}
@props(['title', 'subtitle' => null, 'icon' => 'paw'])

<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute -top-32 -right-24 size-96 rounded-full bg-orange/10 blur-3xl"></div>
    <div class="relative mx-auto grid max-w-6xl gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1fr_1.1fr] lg:items-center">
        <aside class="relative hidden overflow-hidden rounded-[2rem] bg-brown p-10 text-cream-light lg:block" aria-hidden="true">
            <x-paw class="absolute -top-4 -right-4 size-32 rotate-12 text-white/10" />
            <x-paw class="absolute -right-3 -bottom-3 size-20 rotate-45 text-orange/30" />
            <img src="{{ asset('images/logo-mark-240.webp') }}" alt="" width="240" height="240" class="size-36 rounded-full ring-8 ring-white/10">
            <p class="mt-8 font-display text-3xl leading-tight font-semibold text-cream-light">{{ __('ui.slogan') }}</p>
            <ul class="mt-6 space-y-3 text-lg">
                @foreach (['find', 'profile', 'reviews'] as $perk)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-orange text-brown-dark"><x-icon name="check" class="size-4" /></span>
                        <span>{{ __('auth.perk_'.$perk) }}</span>
                    </li>
                @endforeach
            </ul>
        </aside>

        <div class="mx-auto w-full max-w-lg">
            <div class="rounded-[2rem] bg-white p-6 shadow-lift ring-1 ring-line sm:p-9">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-cream text-brown"><x-icon :name="$icon" class="size-7" /></span>
                <h1 class="mt-5 font-display text-3xl leading-tight font-semibold text-brown">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-2 text-muted">{{ $subtitle }}</p>
                @endif
                <div class="mt-7">{{ $slot }}</div>
            </div>
            @isset($footer)
                <div class="mt-6 text-center text-brown-dark">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
