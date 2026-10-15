<x-layouts.app :title="__('pro.badge.title')" :description="__('pro.space.meta')" noindex>
    <x-account-header :title="__('pro.badge.title')" :subtitle="$provider->name" />

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-8">
        <x-card padding="lg">
            @if ($provider->isRegulated() || $pending)
                <x-empty-state :icon="$pending ? 'clock' : 'badge-check'" :title="$pending ? __('pro.badge.pending_title') : __('pro.badge.done_title')">
                    {{ $pending ? __('pro.badge.pending_text') : __('pro.badge.done_text') }}
                    <x-slot:action>
                        <x-button :href="localized_route('pro.index')" icon="building">{{ __('pro.space.title') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <p class="leading-relaxed text-brown-dark">{{ __('pro.badge.intro') }}</p>
                <form method="POST" action="{{ localized_route('pro.badge.store', $provider) }}" enctype="multipart/form-data" class="mt-6 space-y-5" novalidate>
                    @csrf
                    @if ($errors->any())
                        <x-alert variant="error" :title="__('ui.form_has_errors')" />
                    @endif
                    <x-input name="regulatory_number" :label="__('pro.fields.regulatory_number')" :hint="__('pro.hints.regulatory_number')" maxlength="64" required />
                    <x-input name="proof" type="file" :label="__('pro.fields.proof')" :hint="__('pro.hints.proof_badge')"
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required
                        class="[&_input]:cursor-pointer [&_input]:file:mr-3 [&_input]:file:rounded-full [&_input]:file:border-0 [&_input]:file:bg-cream [&_input]:file:px-4 [&_input]:file:py-2 [&_input]:file:font-bold [&_input]:file:text-brown" />
                    <x-textarea name="message" :label="__('pro.fields.message')" rows="3" maxlength="2000" optional />
                    <x-button icon="badge-check">{{ __('pro.badge.submit') }}</x-button>
                </form>
            @endif
        </x-card>
        <aside>
            <x-card>
                <x-badge variant="regulated" />
                <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('ui.badge_regulated_help') }}</p>
                <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('pro.badge.examples') }}</p>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
