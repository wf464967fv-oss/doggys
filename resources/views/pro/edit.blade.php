<x-layouts.app :title="__('pro.edit.title')" :description="__('pro.space.meta')" noindex>
    <x-account-header :title="$provider->name" :subtitle="__('pro.edit.subtitle')" />

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-8">
        <x-card padding="lg">
            <form method="POST" action="{{ localized_route('pro.update', $provider) }}" class="space-y-5" novalidate>
                @csrf
                @method('PUT')
                @if ($errors->any())
                    <x-alert variant="error" :title="__('ui.form_has_errors')" />
                @endif
                @include('pro._listing-fields')
                <div class="flex flex-wrap gap-3 pt-2">
                    <x-button icon="check">{{ __('ui.save') }}</x-button>
                    <x-button variant="ghost" :href="localized_route('pro.index')">{{ __('ui.cancel') }}</x-button>
                </div>
            </form>
        </x-card>

        <aside class="space-y-6">
            <x-card>
                <h2 class="font-display text-xl font-semibold text-brown">{{ __('pro.edit.locked_title') }}</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('pro.fields.name') }}</dt>
                        <dd class="font-bold break-words text-brown-dark">{{ $provider->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold tracking-wide text-muted uppercase">{{ __('pro.edit.address') }}</dt>
                        <dd class="font-bold text-brown-dark">{{ collect([trim($provider->street.' '.$provider->house_number), trim($provider->postal_code.' '.$provider->city)])->filter()->join(', ') }}</dd>
                    </div>
                </dl>
                <details class="group mt-4">
                    <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-1.5 font-bold text-orange-dark underline decoration-orange/60 decoration-2 underline-offset-4 [&::-webkit-details-marker]:hidden">
                        {{ __('pro.edit.request_change') }}
                    </summary>
                    <form method="POST" action="{{ localized_route('providers.report', $provider) }}" class="mt-3 space-y-3" novalidate>
                        @csrf
                        <input type="hidden" name="type" value="{{ \App\Enums\ProviderReportType::Error->value }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <x-textarea name="message" id="change-request" :label="__('pro.edit.change_message')" :hint="__('pro.edit.change_hint')" rows="4" maxlength="2000" required />
                        <x-antibot />
                        <x-button size="sm" variant="secondary" icon="mail">{{ __('pro.edit.change_submit') }}</x-button>
                    </form>
                </details>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
