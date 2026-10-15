<x-admin-layout :title="$provider->exists ? $provider->name : __('admin.providers.create')" :subtitle="$provider->exists ? __('admin.providers.edit_subtitle', ['id' => $provider->id]) : null">
    @if ($provider->exists)
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                @if ($provider->status === \App\Enums\ProviderStatus::Active)
                    <x-button size="sm" variant="soft" icon="arrow-right" :href="localized_route('providers.show', $provider)" target="_blank">{{ __('admin.view_public') }}</x-button>
                @endif
            </div>
        </x-slot:actions>
    @endif

    <x-card padding="lg">
        <form method="POST" action="{{ $provider->exists ? route('admin.providers.update', $provider) : route('admin.providers.store') }}" class="space-y-8" novalidate>
            @csrf
            @if ($provider->exists)
                @method('PUT')
            @endif
            @if ($errors->any())
                <x-alert variant="error" :title="__('ui.form_has_errors')" />
            @endif

            <section class="space-y-5" aria-labelledby="identity-title">
                <h2 id="identity-title" class="font-display text-xl font-semibold text-brown">{{ __('admin.providers.identity') }}</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-input name="name" :label="__('pro.fields.name')" :value="$provider->name" maxlength="150" required />
                    <x-input name="slug" :label="__('admin.providers.slug')" :hint="__('admin.providers.slug_hint')" :value="$provider->slug" maxlength="120" />
                </div>
                <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_8rem]">
                    <x-input name="street" :label="__('pro.fields.street')" :value="$provider->street" maxlength="150" />
                    <x-input name="house_number" :label="__('pro.fields.house_number')" :value="$provider->house_number" maxlength="16" />
                </div>
                <div class="grid gap-5 md:grid-cols-3">
                    <x-input name="postal_code" :label="__('pro.fields.postal_code')" :value="$provider->postal_code" inputmode="numeric" maxlength="4" required />
                    <x-input name="city" :label="__('pro.fields.city')" :value="$provider->city" maxlength="100" required />
                    <x-select name="region" :label="__('admin.providers.region')" :options="__('admin.regions')" :value="$provider->region?->value" :placeholder="__('ui.choose')" />
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-input name="lat" type="number" step="0.0000001" :label="__('admin.providers.lat')" :value="$provider->lat" required />
                    <x-input name="lng" type="number" step="0.0000001" :label="__('admin.providers.lng')" :value="$provider->lng" required />
                </div>
            </section>

            <section class="space-y-5 border-t border-line pt-8" aria-labelledby="trust-title">
                <h2 id="trust-title" class="font-display text-xl font-semibold text-brown">{{ __('admin.providers.trust') }}</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-select name="status" :label="__('admin.providers.status')" :options="__('admin.provider_statuses')" :value="$provider->status?->value ?? 'active'" />
                    <x-select name="verification_level" :label="__('admin.providers.verification_level')" :options="__('admin.verification_levels')" :value="$provider->verification_level?->value ?? 'none'" />
                    <x-input name="bce_number" :label="__('pro.fields.bce_number')" :value="$provider->bce_number" placeholder="0123.456.789" maxlength="20" />
                    <x-input name="regulatory_number" :label="__('pro.fields.regulatory_number')" :value="$provider->regulatory_number" maxlength="64" />
                </div>
                @if ($provider->claimedBy)
                    <p class="text-sm text-muted">{{ __('admin.providers.managed_by', ['name' => $provider->claimedBy->name.' ('.$provider->claimedBy->email.')']) }}</p>
                @endif
            </section>

            <section class="space-y-5 border-t border-line pt-8" aria-labelledby="contact-title">
                <h2 id="contact-title" class="font-display text-xl font-semibold text-brown">{{ __('pro.listing.contact') }}</h2>
                @include('pro._listing-fields')
            </section>

            <x-button icon="check">{{ __('ui.save') }}</x-button>
        </form>
    </x-card>

    @if ($provider->exists && $provider->status !== \App\Enums\ProviderStatus::Removed)
        <section class="mt-6 rounded-3xl bg-rose-soft p-5 ring-1 ring-rose/30 sm:p-6" aria-labelledby="danger-title">
            <h2 id="danger-title" class="font-display text-xl font-semibold text-rose-dark">{{ __('admin.providers.remove_title') }}</h2>
            <p class="mt-2 text-sm text-brown-dark">{{ __('admin.providers.remove_text') }}</p>
            <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" class="mt-4 flex flex-wrap items-center gap-3"
                x-data="confirmSubmit" data-confirm="{{ __('admin.providers.remove_confirm', ['name' => $provider->name]) }}" x-on:submit="submit">
                @csrf
                @method('DELETE')
                <x-button size="sm" variant="danger" icon="trash">{{ __('admin.providers.remove') }}</x-button>
                @include('admin._confirm-hint')
            </form>
        </section>
    @endif
</x-admin-layout>
