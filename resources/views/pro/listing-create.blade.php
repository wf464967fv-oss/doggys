<x-layouts.app :title="__('pro.listing.title')" :description="__('pro.listing.meta')" noindex>
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-8 pb-10 sm:px-6">
            <p class="text-sm font-extrabold tracking-wider text-orange-dark uppercase">{{ __('pro.listing.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl">{{ __('pro.listing.title') }}</h1>
            <p class="mt-3 max-w-2xl text-lg leading-relaxed text-brown-dark">{{ __('pro.listing.intro') }}</p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-8">
        <x-card padding="lg">
            <form method="POST" action="{{ localized_route('listing.store') }}" enctype="multipart/form-data" class="space-y-8" novalidate>
                @csrf
                @if ($errors->any())
                    <x-alert variant="error" :title="__('ui.form_has_errors')" />
                @endif

                <section class="space-y-5" aria-labelledby="listing-identity">
                    <h2 id="listing-identity" class="font-display text-2xl font-semibold text-brown">{{ __('pro.listing.identity') }}</h2>
                    <x-input name="name" :label="__('pro.fields.name')" :hint="__('pro.hints.name')" autocomplete="organization" maxlength="150" required />
                    <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_8rem]">
                        <x-input name="street" :label="__('pro.fields.street')" autocomplete="address-line1" maxlength="150" required />
                        <x-input name="house_number" :label="__('pro.fields.house_number')" maxlength="16" optional />
                    </div>
                    <div class="grid gap-5 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <x-input name="postal_code" :label="__('pro.fields.postal_code')" autocomplete="postal-code" inputmode="numeric" maxlength="4" required />
                        <x-input name="city" :label="__('pro.fields.city')" :hint="__('pro.hints.city')" autocomplete="address-level2" maxlength="100" optional />
                    </div>
                </section>

                <section class="space-y-5 border-t border-line pt-8" aria-labelledby="listing-contact">
                    <h2 id="listing-contact" class="font-display text-2xl font-semibold text-brown">{{ __('pro.listing.contact') }}</h2>
                    @include('pro._listing-fields', ['provider' => new \App\Models\Provider(['is_mobile' => false])])
                </section>

                <section class="space-y-5 border-t border-line pt-8" aria-labelledby="listing-proof">
                    <h2 id="listing-proof" class="font-display text-2xl font-semibold text-brown">{{ __('pro.listing.proof') }}</h2>
                    @include('pro._claim-fields')
                </section>

                <x-antibot />
                <x-button icon="check">{{ __('pro.listing.submit') }}</x-button>
            </form>
        </x-card>

        <aside class="space-y-6">
            @include('pro._steps')
        </aside>
    </div>
</x-layouts.app>
