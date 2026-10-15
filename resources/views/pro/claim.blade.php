<x-layouts.app :title="__('pro.claim.title')" :description="__('pro.claim.meta')" noindex>
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-8 pb-10 sm:px-6">
            <a href="{{ localized_route('providers.show', $provider) }}" class="link inline-flex min-h-11 items-center gap-1.5 text-sm">
                <x-icon name="chevron-right" class="size-4 rotate-180" />{{ __('pro.claim.back', ['name' => $provider->name]) }}
            </a>
            <p class="mt-4 text-sm font-extrabold tracking-wider text-orange-dark uppercase">{{ __('pro.claim.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-3xl leading-tight font-semibold break-words text-brown sm:text-4xl">{{ __('pro.claim.heading', ['name' => $provider->name]) }}</h1>
            <p class="mt-3 max-w-2xl text-lg leading-relaxed text-brown-dark">{{ __('pro.claim.intro') }}</p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-8">
        <x-card padding="lg">
            @if ($pendingClaim)
                <x-empty-state icon="clock" :title="__('pro.claim.pending_title')">
                    {{ __('pro.claim.pending_text', ['date' => $pendingClaim->created_at->locale(app()->getLocale())->isoFormat('LL')]) }}
                    <x-slot:action>
                        <x-button :href="localized_route('pro.index')" icon="building">{{ __('pro.space.title') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                @if ($provider->claimed_by_user_id)
                    <x-alert variant="warning" class="mb-5">{{ __('pro.claim.already_managed') }}</x-alert>
                @endif
                <form method="POST" action="{{ localized_route('claim.store', $provider) }}" enctype="multipart/form-data" class="space-y-5" novalidate>
                    @csrf
                    @if ($errors->any())
                        <x-alert variant="error" :title="__('ui.form_has_errors')" />
                    @endif
                    <fieldset>
                        <legend class="text-sm font-bold text-brown">{{ __('pro.fields.action') }}</legend>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            @foreach (['manage' => 'pencil', 'delete' => 'trash'] as $action => $actionIcon)
                                <label class="flex cursor-pointer gap-3 rounded-2xl bg-cream-light p-4 ring-2 ring-line transition has-checked:bg-orange-soft has-checked:ring-orange has-focus-visible:outline-3 has-focus-visible:outline-brown">
                                    <input type="radio" name="action" value="{{ $action }}" @checked(old('action', 'manage') === $action) class="mt-1 size-5 shrink-0 accent-orange-dark">
                                    <span>
                                        <span class="flex items-center gap-2 font-bold text-brown"><x-icon :name="$actionIcon" class="size-5 text-orange-dark" />{{ __('pro.claim.actions.'.$action) }}</span>
                                        <span class="mt-1 block text-sm text-muted">{{ __('pro.claim.actions.'.$action.'_help') }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('action')<p class="mt-1.5 text-sm font-semibold text-rose-dark">{{ $message }}</p>@enderror
                    </fieldset>
                    @include('pro._claim-fields')
                    <x-antibot />
                    <x-button icon="check">{{ __('pro.claim.submit') }}</x-button>
                </form>
            @endif
        </x-card>

        <aside class="space-y-6">
            @include('pro._steps')
        </aside>
    </div>
</x-layouts.app>
