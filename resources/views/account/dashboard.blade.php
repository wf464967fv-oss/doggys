<x-layouts.app :title="__('account.dashboard_title')" :description="__('account.dashboard_meta')" noindex>
    <x-account-header :title="__('account.hello', ['name' => $user->name])" :subtitle="__('account.dashboard_subtitle')" />

    @if (request()->boolean('verified'))
        <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
            <x-alert variant="success">{{ __('account.verified') }}</x-alert>
        </div>
    @endif

    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[1fr_20rem]">
        <section aria-labelledby="dogs-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="dogs-title" class="font-display text-2xl font-semibold text-brown">{{ __('dogs.my_dogs') }}</h2>
                @if ($dogs->isNotEmpty())
                    <x-button size="sm" icon="plus" :href="localized_route('dogs.create')">{{ __('dogs.add') }}</x-button>
                @endif
            </div>

            @if ($dogs->isEmpty())
                <x-empty-state class="mt-5" icon="paw" :title="__('dogs.empty_title')">
                    {{ __('dogs.empty_text') }}
                    <x-slot:action>
                        <x-button icon="plus" :href="localized_route('dogs.create')">{{ __('dogs.add_first') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <ul class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($dogs as $dog)
                        <li>
                            <x-card as="article" class="flex h-full flex-col">
                                <div class="flex items-start gap-4">
                                    <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-orange font-display text-2xl font-semibold text-brown-dark" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($dog->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate font-display text-xl font-semibold text-brown">{{ $dog->name }}</h3>
                                        <p class="truncate text-muted">{{ $dog->breed ?: __('dogs.breed_unknown') }}</p>
                                    </div>
                                </div>
                                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-xl bg-cream-light px-3 py-2">
                                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('dogs.age') }}</dt>
                                        <dd class="text-base font-bold text-brown-dark">
                                            @if ($dog->birth_date)
                                                @php($age = $dog->birth_date->diff(now()))
                                                {{ $age->y > 0 ? trans_choice('dogs.age_years', $age->y) : trans_choice('dogs.age_months', max(1, $age->m)) }}
                                            @else
                                                —
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="rounded-xl bg-cream-light px-3 py-2">
                                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('dogs.sex') }}</dt>
                                        <dd class="text-base font-bold text-brown-dark">{{ $dog->sex ? __('dogs.sex_'.$dog->sex->value) : '—' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-cream-light px-3 py-2">
                                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('dogs.weight') }}</dt>
                                        <dd class="text-base font-bold text-brown-dark">{{ $dog->weight_kg ? __('dogs.weight_value', ['kg' => \Illuminate\Support\Number::format((float) $dog->weight_kg, maxPrecision: 1, locale: app()->getLocale())]) : '—' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-cream-light px-3 py-2">
                                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('dogs.vaccinations') }}</dt>
                                        <dd class="text-base font-bold text-brown-dark">{{ trans_choice('dogs.vaccinations_count', count($dog->vaccinations ?? [])) }}</dd>
                                    </div>
                                </dl>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($dog->microchip_number)
                                        <x-badge variant="orange" icon="chip">{{ __('dogs.chipped') }}</x-badge>
                                    @endif
                                    @if ($dog->is_neutered)
                                        <x-badge variant="neutral" icon="check">{{ $dog->sex === \App\Enums\DogSex::Female ? __('dogs.neutered_female') : __('dogs.neutered_male') }}</x-badge>
                                    @endif
                                </div>
                                <div class="mt-auto pt-5">
                                    <x-button variant="soft" size="sm" icon="pencil" :href="localized_route('dogs.edit', $dog)" full>
                                        {{ __('dogs.edit_named', ['name' => $dog->name]) }}
                                    </x-button>
                                </div>
                            </x-card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-4" aria-label="{{ __('account.shortcuts') }}">
            @if ($user->isPro())
                <x-card class="bg-orange-soft">
                    <x-badge variant="brown" icon="building">{{ __('account.role_pro') }}</x-badge>
                    <h2 class="mt-3 font-display text-xl font-semibold text-brown">{{ __('account.pro_title') }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-brown-dark">{{ __('account.pro_text') }}</p>
                    <x-button class="mt-4" size="sm" icon="search" :href="localized_route('search')" full>{{ __('account.pro_cta') }}</x-button>
                </x-card>
            @endif
            <x-card>
                <h2 class="font-display text-xl font-semibold text-brown">{{ __('account.privacy_title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('account.privacy_text') }}</p>
                <ul class="mt-3 space-y-1">
                    <li><a href="{{ localized_route('account.profile') }}" class="link inline-flex min-h-11 items-center gap-2"><x-icon name="user" class="size-4" />{{ __('account.edit_profile') }}</a></li>
                    <li><a href="{{ localized_route('account.export') }}" class="link inline-flex min-h-11 items-center gap-2"><x-icon name="download" class="size-4" />{{ __('account.export_link') }}</a></li>
                </ul>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
