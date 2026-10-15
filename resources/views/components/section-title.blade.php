{{--
    Section heading with optional eyebrow and subtitle.
    <x-section-title :eyebrow="__('home.categories_eyebrow')" :title="__('home.categories_title')" :subtitle="…" />
    Props: title, eyebrow, subtitle, as (h1|h2|h3, default h2), align left|center. An id attribute goes on the heading (for aria-labelledby).
--}}
@props(['title', 'eyebrow' => null, 'subtitle' => null, 'as' => 'h2', 'align' => 'left'])

<div {{ $attributes->except('id')->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p @class(['mb-2 inline-flex items-center gap-1.5 text-sm font-extrabold tracking-wider text-orange-dark uppercase', 'justify-center' => $align === 'center'])>
            <x-icon name="paw" class="size-4" />{{ $eyebrow }}
        </p>
    @endif
    <{{ $as }} @if ($attributes->has('id')) id="{{ $attributes->get('id') }}" @endif class="font-display text-[1.75rem] leading-tight font-semibold text-brown sm:text-4xl">{{ $title }}</{{ $as }}>
    @if ($subtitle)
        <p class="mt-2 text-base leading-relaxed text-muted sm:mt-3 sm:text-lg">{{ $subtitle }}</p>
    @endif
</div>
