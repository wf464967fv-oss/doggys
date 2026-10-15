{{--
    Decorative paw print (hidden from assistive tech). Colour via text-*, size via size-*.
    <x-paw class="absolute top-4 right-6 size-10 -rotate-12 text-orange/30" />
--}}
<x-icon name="paw" {{ $attributes->merge(['class' => 'pointer-events-none select-none']) }} />
