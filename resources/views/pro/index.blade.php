<x-layouts.app :title="__('pro.space.title')" :description="__('pro.space.meta')" noindex>
    <x-account-header :title="__('pro.space.title')" :subtitle="__('pro.space.subtitle')" />

    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section aria-labelledby="listings-title">
            <h2 id="listings-title" class="font-display text-2xl font-semibold text-brown">{{ __('pro.space.listings') }}</h2>

            @if ($providers->isEmpty())
                <x-empty-state class="mt-5" icon="building" :title="__('pro.space.empty_title')">
                    {{ __('pro.space.empty_text') }}
                    <x-slot:action>
                        <x-button icon="search" :href="localized_route('search')">{{ __('pro.space.find') }}</x-button>
                        <x-button variant="soft" icon="plus" :href="localized_route('listing.create')">{{ __('pro.space.add') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <ul class="mt-5 space-y-4" role="list">
                    @foreach ($providers as $provider)
                        <li>
                            <x-card as="article" aria-labelledby="listing-{{ $provider->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 id="listing-{{ $provider->id }}" class="font-display text-xl font-semibold break-words text-brown">{{ $provider->name }}</h3>
                                        <p class="mt-1 text-sm text-muted">{{ $provider->categories->map->label()->join(', ') }} · {{ $provider->city }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($provider->isRegulated())
                                            <x-badge variant="regulated" />
                                        @endif
                                        @if ($provider->isVerified())
                                            <x-badge variant="verified" />
                                        @endif
                                        @if ($provider->status !== \App\Enums\ProviderStatus::Active)
                                            <x-badge variant="rose" icon="lock">{{ __('pro.status.'.$provider->status->value) }}</x-badge>
                                        @endif
                                    </div>
                                </div>
                                @if ($provider->reviews_count > 0)
                                    <x-stars class="mt-3" :rating="$provider->rating_avg" :count="$provider->reviews_count" />
                                @endif
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <x-button size="sm" icon="pencil" :href="localized_route('pro.edit', $provider)">{{ __('pro.space.edit') }}</x-button>
                                    <x-button size="sm" variant="soft" icon="message-circle" :href="localized_route('pro.reviews', $provider)">
                                        {{ __('pro.space.reviews') }}
                                        @if ($provider->unanswered_reviews_count > 0)
                                            <span class="ml-1 rounded-full bg-orange px-2 text-xs text-brown-dark">{{ trans_choice('pro.space.to_answer', $provider->unanswered_reviews_count, ['count' => $provider->unanswered_reviews_count]) }}</span>
                                        @endif
                                    </x-button>
                                    @unless ($provider->isRegulated())
                                        @if ($provider->badgeRequests->isNotEmpty())
                                            <x-badge variant="neutral" icon="clock" class="self-center">{{ __('pro.badge.pending_short') }}</x-badge>
                                        @else
                                            <x-button size="sm" variant="soft" icon="badge-check" :href="localized_route('pro.badge', $provider)">{{ __('pro.space.badge') }}</x-button>
                                        @endif
                                    @endunless
                                    @if ($provider->status === \App\Enums\ProviderStatus::Active)
                                        <x-button size="sm" variant="ghost" icon="arrow-right" :href="localized_route('providers.show', $provider)">{{ __('pro.space.view') }}</x-button>
                                    @endif
                                </div>
                            </x-card>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($claims->isNotEmpty())
                <h2 class="mt-10 font-display text-2xl font-semibold text-brown">{{ __('pro.space.requests') }}</h2>
                <ul class="mt-4 divide-y divide-line rounded-3xl bg-white ring-1 ring-line" role="list">
                    @foreach ($claims as $claim)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4 sm:px-5">
                            <div class="min-w-0">
                                <p class="font-bold break-words text-brown">{{ $claim->provider?->name }}</p>
                                <p class="text-sm text-muted">
                                    {{ $claim->creates_listing ? __('pro.space.request_listing') : __('pro.claim.actions.'.$claim->action->value) }}
                                    · <time datetime="{{ $claim->created_at->toDateString() }}">{{ $claim->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time>
                                </p>
                                @if ($claim->admin_note)
                                    <p class="mt-1 text-sm text-brown-dark">{{ __('pro.space.admin_note', ['note' => $claim->admin_note]) }}</p>
                                @endif
                            </div>
                            <x-badge :variant="['pending' => 'orange', 'approved' => 'verified', 'rejected' => 'rose'][$claim->status->value]">{{ __('pro.status.'.$claim->status->value) }}</x-badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-4">
            <x-card class="bg-orange-soft">
                <span class="flex size-12 items-center justify-center rounded-full bg-orange text-brown-dark"><x-icon name="plus" class="size-6" /></span>
                <h2 class="mt-3 font-display text-xl font-semibold text-brown">{{ __('pro.space.add_title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-brown-dark">{{ __('pro.space.add_text') }}</p>
                <x-button class="mt-4" size="sm" icon="plus" :href="localized_route('listing.create')" full>{{ __('pro.space.add') }}</x-button>
            </x-card>
            <x-card>
                <h2 class="font-display text-xl font-semibold text-brown">{{ __('pro.space.badges_title') }}</h2>
                <ul class="mt-3 space-y-3 text-sm text-muted">
                    <li><x-badge variant="verified" /><p class="mt-1.5">{{ __('ui.badge_verified_help') }}</p></li>
                    <li><x-badge variant="regulated" /><p class="mt-1.5">{{ __('ui.badge_regulated_help') }}</p></li>
                </ul>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
