{{--
    Legal pages (notice, privacy, terms, cookies). Texts: lang/{locale}/legal.php, one block per $page.
    Placeholders in texts: :bce :address :email (config('doggys.legal.*'), never invented: "to be completed" when empty).
--}}
@php
    $content = __('legal.'.$page);
    $legal = config('doggys.legal');
    $todo = __('legal.placeholder');
    $replace = [
        ':bce' => $legal['bce'] ?: $todo,
        ':address' => $legal['address'] ?: $todo,
        ':email' => $legal['email'] ?: $todo,
    ];
    $fill = fn (string $text) => strtr($text, $replace);
    $updated = \Illuminate\Support\Carbon::parse(__('legal.updated_on'))->locale(app()->getLocale())->isoFormat('LL');
    $linkRoutes = ['reviews' => 'reviews.policy', 'privacy' => 'legal.privacy', 'cookies' => 'legal.cookies', 'terms' => 'legal.terms', 'notice' => 'legal.notice', 'contact' => 'contact'];
@endphp
<x-layouts.app :title="$content['meta_title']" :description="$content['meta_description']">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-4xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8 sm:pb-12">
            <x-breadcrumbs :items="[__('directory.breadcrumb_home') => localized_route('home'), $content['breadcrumb'] => null]" />
            <h1 class="mt-6 font-display text-3xl leading-tight font-semibold text-brown sm:text-5xl">{{ $content['title'] }}</h1>
            <p class="mt-4 max-w-3xl text-lg leading-relaxed text-brown-dark">{{ $content['intro'] }}</p>
            <p class="mt-3 text-sm font-semibold text-muted">{{ __('legal.updated', ['date' => $updated]) }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
        <x-card as="nav" aria-labelledby="toc-title" class="bg-orange-soft">
            <h2 id="toc-title" class="font-display text-xl font-semibold text-brown">{{ __('legal.toc') }}</h2>
            <ol class="mt-3 grid gap-x-6 gap-y-1 sm:grid-cols-2">
                @foreach ($content['sections'] as $index => $section)
                    <li><a class="link inline-flex min-h-11 items-center" href="#s{{ $index + 1 }}">{{ $index + 1 }}. {{ $section['title'] }}</a></li>
                @endforeach
            </ol>
        </x-card>

        <div class="prose-doggys mt-6 !max-w-none">
            @foreach ($content['sections'] as $index => $section)
                <h2 id="s{{ $index + 1 }}" class="font-display text-brown">{{ $section['title'] }}</h2>
                @foreach ($section['paragraphs'] ?? [] as $paragraph)
                    <p>{{ $fill($paragraph) }}</p>
                @endforeach
                @if (! empty($section['list']))
                    <ul>
                        @foreach ($section['list'] as $item)
                            <li>{{ $fill($item) }}</li>
                        @endforeach
                    </ul>
                @endif
                @foreach ($section['treatments'] ?? [] as $treatment)
                    <x-card class="mt-5">
                        <h3 class="!mt-0 font-display text-brown">{{ $treatment['title'] }}</h3>
                        <dl class="mt-3 space-y-2 text-base">
                            @foreach (['purpose', 'basis', 'retention', 'recipients'] as $key)
                                <div><dt class="inline font-extrabold text-brown">{{ __('legal.labels.'.$key) }} :</dt> <dd class="inline">{{ $treatment[$key] }}</dd></div>
                            @endforeach
                        </dl>
                    </x-card>
                @endforeach
                @if (! empty($section['links']))
                    <p>
                        @foreach ($section['links'] as $key => $label)
                            <a href="{{ localized_route($linkRoutes[$key]) }}" class="inline-flex min-h-11 items-center">{{ $label }}</a>@unless ($loop->last) · @endunless
                        @endforeach
                    </p>
                @endif
            @endforeach
        </div>

        <div class="on-dark relative mt-12 overflow-hidden rounded-3xl bg-brown p-6 text-cream-light shadow-soft sm:p-8 [&_:focus-visible]:outline-orange">
            <x-paw class="absolute -right-4 -bottom-4 size-28 -rotate-12 text-white/10" />
            <h2 class="relative font-display text-2xl font-semibold text-cream-light">{{ __('legal.cta_title') }}</h2>
            <p class="relative mt-2 text-cream">{{ __('legal.cta_text') }}</p>
            <x-button :href="localized_route('contact')" icon="mail" class="relative mt-5">{{ __('legal.cta') }}</x-button>
        </div>
    </div>
</x-layouts.app>
