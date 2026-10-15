{{--
    Header of account pages: title + tabs. <x-account-header :title="…" :subtitle="…" />
--}}
@props(['title', 'subtitle' => null])

@php
    $tabs = [
        ['route' => 'account', 'label' => __('ui.account_dashboard'), 'icon' => 'home', 'active' => localized_route_is('account', 'dogs.*')],
        ['route' => 'account.reviews', 'label' => __('reviews.mine.tab'), 'icon' => 'star', 'active' => localized_route_is('account.reviews', 'reviews.edit')],
        ['route' => 'account.profile', 'label' => __('ui.account_profile'), 'icon' => 'user', 'active' => localized_route_is('account.profile')],
    ];
    if (auth()->user()?->isPro()) {
        array_splice($tabs, 2, 0, [['route' => 'pro.index', 'label' => __('pro.space.tab'), 'icon' => 'building', 'active' => localized_route_is('pro.*')]]);
    }
@endphp

<div class="border-b border-line bg-cream">
    <div class="relative mx-auto max-w-6xl overflow-hidden px-4 pt-8 sm:px-6 sm:pt-10">
        <x-paw class="absolute top-6 right-6 size-14 rotate-12 text-white/70" />
        <h1 class="relative font-display text-3xl font-semibold text-brown sm:text-4xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="relative mt-2 max-w-2xl text-muted">{{ $subtitle }}</p>
        @endif
        <nav aria-label="{{ __('ui.account_nav') }}" class="relative mt-6 -mb-px flex gap-1 snap-x overflow-x-auto [mask-image:linear-gradient(to_right,#000_90%,transparent)] lg:[mask-image:none]">
            @foreach ($tabs as $tab)
                <a href="{{ localized_route($tab['route']) }}"
                    @class([
                        'inline-flex min-h-12 shrink-0 items-center gap-2 rounded-t-2xl border border-b-0 px-4 font-bold transition',
                        'border-line bg-cream-light text-brown' => $tab['active'],
                        'border-transparent text-brown-dark hover:bg-white/50' => ! $tab['active'],
                    ])
                    @if ($tab['active']) aria-current="page" @endif>
                    <x-icon :name="$tab['icon']" class="size-5 text-orange-dark" />{{ $tab['label'] }}
                </a>
            @endforeach
            @can('admin')
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-12 shrink-0 items-center gap-2 rounded-t-2xl border border-b-0 border-transparent px-4 font-bold text-brown-dark transition hover:bg-white/50">
                    <x-icon name="key" class="size-5 text-orange-dark" />{{ __('admin.nav.title') }}
                </a>
            @endcan
        </nav>
    </div>
</div>
