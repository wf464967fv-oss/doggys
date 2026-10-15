{{--
    Admin page shell: title bar + section navigation (scrollable tabs on mobile, sidebar from lg).
    <x-admin-layout :title="__('admin.claims.title')">…</x-admin-layout>
    Props: title, subtitle.
--}}
@props(['title', 'subtitle' => null])

@php
    $sections = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'dashboard', 'icon' => 'home'],
        ['route' => 'admin.claims.index', 'pattern' => 'admin.claims.*', 'label' => 'claims', 'icon' => 'building'],
        ['route' => 'admin.badges.index', 'pattern' => 'admin.badges.*', 'label' => 'badges', 'icon' => 'badge-check'],
        ['route' => 'admin.providers.index', 'pattern' => 'admin.providers.*', 'label' => 'providers', 'icon' => 'map-pin'],
        ['route' => 'admin.provider-reports.index', 'pattern' => 'admin.provider-reports.*', 'label' => 'provider_reports', 'icon' => 'alert'],
        ['route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'label' => 'reviews', 'icon' => 'star'],
        ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'users', 'icon' => 'users'],
        ['route' => 'admin.contact.index', 'pattern' => 'admin.contact.*', 'label' => 'messages', 'icon' => 'mail'],
        ['route' => 'admin.posts.index', 'pattern' => 'admin.posts.*', 'label' => 'posts', 'icon' => 'book'],
        ['route' => 'admin.log', 'pattern' => 'admin.log', 'label' => 'log', 'icon' => 'clock'],
    ];
@endphp

<x-layouts.app :title="$title.' · '.__('admin.nav.title')" noindex>
    <div class="on-dark border-b border-line bg-brown text-cream-light [&_:focus-visible]:outline-orange">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-5 sm:px-6">
            <div class="min-w-0">
                <p class="flex items-center gap-1.5 text-sm font-extrabold tracking-wider text-orange uppercase"><x-icon name="key" class="size-4" />{{ __('admin.nav.title') }}</p>
                <h1 class="mt-1 font-display text-2xl leading-tight font-semibold break-words text-cream-light sm:text-3xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-cream">{{ $subtitle }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </div>
    </div>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:py-8">
        <nav aria-label="{{ __('admin.nav.label') }}" class="-mx-4 snap-x overflow-x-auto px-4 [mask-image:linear-gradient(to_right,#000_90%,transparent)] lg:mx-0 lg:[mask-image:none] lg:overflow-visible lg:px-0">
            <ul class="flex gap-1 lg:sticky lg:top-24 lg:flex-col" role="list">
                @foreach ($sections as $section)
                    @php($active = request()->routeIs($section['pattern']))
                    <li class="shrink-0">
                        <a href="{{ route($section['route']) }}" @if ($active) aria-current="page" @endif
                            @class([
                                'flex min-h-11 items-center gap-2.5 rounded-2xl px-3 text-sm font-bold whitespace-nowrap transition',
                                'bg-orange-soft text-brown ring-1 ring-orange/40' => $active,
                                'text-brown-dark hover:bg-cream' => ! $active,
                            ])>
                            <x-icon :name="$section['icon']" class="size-5 text-orange-dark" />{{ __('admin.nav.'.$section['label']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>
</x-layouts.app>
