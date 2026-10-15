@props(['bare' => false, 'alternates' => []])

@php
    $user = $bare ? null : auth()->user();
    $nav = [
        ['route' => 'search', 'label' => __('ui.nav_find'), 'icon' => 'search'],
        ['route' => 'guides', 'label' => __('ui.nav_guides'), 'icon' => 'book'],
        ['route' => 'reviews.policy', 'label' => __('ui.nav_trust'), 'icon' => 'shield-check'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-line/70 bg-cream-light/95 backdrop-blur supports-[backdrop-filter]:bg-cream-light/85">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:h-20 sm:px-6">
        <a href="{{ localized_route('home') }}" class="-ml-1 flex shrink-0 items-center gap-2 rounded-2xl p-1" aria-label="{{ __('ui.home_link') }}">
            <img src="{{ asset('images/logo-mark-96.webp') }}" alt="" width="48" height="48" class="size-10 sm:size-12">
            <img src="{{ asset('images/logo-wordmark-160.webp') }}"
                srcset="{{ asset('images/logo-wordmark-160.webp') }} 160w, {{ asset('images/logo-wordmark-320.webp') }} 320w"
                sizes="(min-width: 640px) 156px, 125px" alt="Doggys" width="160" height="41" class="h-8 w-auto sm:h-10">
        </a>

        <nav aria-label="{{ __('ui.nav_main') }}" class="ml-6 hidden lg:block">
            <ul class="flex items-center gap-1">
                @foreach ($nav as $item)
                    <li>
                        <a href="{{ localized_route($item['route']) }}"
                            @class([
                                'inline-flex min-h-11 items-center rounded-full px-4 font-bold transition hover:bg-cream hover:text-brown',
                                'bg-cream text-brown' => localized_route_is($item['route']),
                                'text-brown-dark' => ! localized_route_is($item['route']),
                            ])
                            @if (localized_route_is($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <x-site.language-switcher :alternates="$alternates" class="hidden sm:block" />

            @unless ($bare)
                @if ($user)
                    <details class="group relative hidden lg:block" x-data="dropdown" x-on:keydown.escape="close" x-on:click.outside="close">
                        <summary class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full bg-white py-1.5 pr-3 pl-1.5 font-bold text-brown ring-2 ring-line transition hover:ring-brown-mid [&::-webkit-details-marker]:hidden">
                            <span class="flex size-8 items-center justify-center rounded-full bg-orange font-display text-brown-dark">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <span class="max-w-36 truncate">{{ $user->name }}</span>
                            <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" />
                        </summary>
                        <div class="absolute right-0 mt-2 w-60 rounded-2xl bg-white p-2 shadow-lift ring-1 ring-line">
                            <a href="{{ localized_route('account') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 font-semibold text-brown-dark hover:bg-cream-light">
                                <x-icon name="home" class="text-orange-dark" />{{ __('ui.account_dashboard') }}
                            </a>
                            <a href="{{ localized_route('account.profile') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 font-semibold text-brown-dark hover:bg-cream-light">
                                <x-icon name="user" class="text-orange-dark" />{{ __('ui.account_profile') }}
                            </a>
                            <form method="POST" action="{{ localized_route('logout') }}" class="mt-1 border-t border-line pt-1">
                                @csrf
                                <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 font-semibold text-brown-dark hover:bg-cream-light">
                                    <x-icon name="log-out" class="text-orange-dark" />{{ __('ui.logout') }}
                                </button>
                            </form>
                        </div>
                    </details>
                @else
                    <div class="hidden lg:block">
                        <x-button variant="ghost" size="sm" :href="localized_route('login')">{{ __('ui.login') }}</x-button>
                    </div>
                    <div class="hidden sm:block">
                        <x-button size="sm" :href="localized_route('register')">{{ __('ui.register') }}</x-button>
                    </div>
                @endif
            @endunless

            <button type="button" class="inline-flex size-11 items-center justify-center rounded-full text-brown ring-2 ring-line transition hover:bg-cream lg:hidden"
                x-on:click="open = ! open" x-bind:aria-expanded="open" aria-controls="mobile-menu">
                <span class="sr-only">{{ __('ui.menu') }}</span>
                <x-icon name="menu" x-show="! open" />
                <x-icon name="x" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu" x-show="open" x-cloak x-transition.opacity.duration.150ms x-on:keydown.escape.window="open = false"
        class="border-t border-line bg-cream-light lg:hidden">
        <nav aria-label="{{ __('ui.nav_main') }}" class="mx-auto max-w-6xl space-y-1 px-4 py-4 sm:px-6">
            @foreach ($nav as $item)
                <a href="{{ localized_route($item['route']) }}" class="flex min-h-12 items-center gap-3 rounded-2xl px-3 text-lg font-bold text-brown-dark hover:bg-cream"
                    @if (localized_route_is($item['route'])) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="text-orange-dark" />{{ $item['label'] }}
                </a>
            @endforeach

            @unless ($bare)
                <div class="mt-3 border-t border-line pt-4">
                    @if ($user)
                        <p class="px-3 pb-2 text-sm font-bold text-muted">{{ __('ui.signed_in_as', ['name' => $user->name]) }}</p>
                        <a href="{{ localized_route('account') }}" class="flex min-h-12 items-center gap-3 rounded-2xl px-3 text-lg font-bold text-brown-dark hover:bg-cream">
                            <x-icon name="home" class="text-orange-dark" />{{ __('ui.account_dashboard') }}
                        </a>
                        <a href="{{ localized_route('account.profile') }}" class="flex min-h-12 items-center gap-3 rounded-2xl px-3 text-lg font-bold text-brown-dark hover:bg-cream">
                            <x-icon name="user" class="text-orange-dark" />{{ __('ui.account_profile') }}
                        </a>
                        <form method="POST" action="{{ localized_route('logout') }}">
                            @csrf
                            <button type="submit" class="flex min-h-12 w-full items-center gap-3 rounded-2xl px-3 text-lg font-bold text-brown-dark hover:bg-cream">
                                <x-icon name="log-out" class="text-orange-dark" />{{ __('ui.logout') }}
                            </button>
                        </form>
                    @else
                        {{-- From sm the header already shows "Sign up": only "Log in" is repeated here. --}}
                        <div class="grid grid-cols-1 gap-3 min-[400px]:grid-cols-2 sm:grid-cols-1">
                            <x-button variant="soft" :href="localized_route('login')" full>{{ __('ui.login') }}</x-button>
                            <div class="sm:hidden">
                                <x-button :href="localized_route('register')" full>{{ __('ui.register') }}</x-button>
                            </div>
                        </div>
                    @endif
                </div>
            @endunless

            <div class="mt-3 border-t border-line pt-4 sm:hidden">
                <x-site.language-switcher :alternates="$alternates" inline />
            </div>
        </nav>
    </div>
</header>
