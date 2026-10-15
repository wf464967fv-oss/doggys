{{--
    Centered full-page message (coming soon, errors). Slot: action buttons.
    <x-message-page code="404" icon="search" :title="…" :text="…">…buttons…</x-message-page>
    Props: title, text, icon, eyebrow, code (large decorative number).
--}}
@props(['title', 'text' => null, 'icon' => 'paw', 'eyebrow' => null, 'code' => null])

<section class="relative overflow-hidden">
    <x-paw class="absolute top-12 left-[10%] size-10 -rotate-12 text-orange/25" />
    <x-paw class="absolute right-[12%] bottom-16 size-14 rotate-12 text-brown-mid/20" />
    <x-paw class="absolute top-1/2 right-[6%] size-7 rotate-45 text-rose/25" />

    <div class="relative mx-auto flex max-w-2xl flex-col items-center px-4 py-16 text-center sm:px-6 sm:py-24">
        <div class="relative">
            <div class="flex size-28 items-center justify-center rounded-full bg-cream ring-8 ring-orange/15 sm:size-32">
                <img src="{{ asset('images/logo-mark-240.webp') }}" alt="" width="240" height="240" class="size-24 sm:size-28">
            </div>
            <span class="absolute -right-2 -bottom-1 flex size-11 items-center justify-center rounded-full bg-white text-orange-dark shadow-soft ring-1 ring-line">
                <x-icon :name="$icon" class="size-6" />
            </span>
        </div>

        @if ($code)
            <p class="mt-6 font-display text-6xl font-semibold text-orange sm:text-7xl" aria-hidden="true">{{ $code }}</p>
        @elseif ($eyebrow)
            <p class="mt-6 text-sm font-extrabold tracking-wider text-orange-dark uppercase">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-3 font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl">{{ $title }}</h1>
        @if ($text)
            <p class="mt-4 max-w-lg text-lg leading-relaxed text-muted">{{ $text }}</p>
        @endif

        @if ($slot->isNotEmpty())
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">{{ $slot }}</div>
        @endif
    </div>
</section>
