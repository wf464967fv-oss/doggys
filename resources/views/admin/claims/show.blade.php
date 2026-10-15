@php
    $provider = $claim->provider;
    $locale = app()->getLocale();
@endphp

<x-admin-layout :title="__('admin.claims.show_title', ['name' => $provider?->name])" :subtitle="__('admin.claim_statuses.'.$claim->status->value)">
    <div class="grid gap-6 xl:grid-cols-2">
        <x-card as="section" aria-labelledby="request-title">
            <h2 id="request-title" class="font-display text-xl font-semibold text-brown">{{ __('admin.claims.request') }}</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="font-bold text-muted">{{ __('admin.claims.applicant') }}</dt><dd class="break-words text-brown-dark">{{ $claim->user?->name }}<br>{{ $claim->user?->email }}</dd></div>
                <div><dt class="font-bold text-muted">{{ __('pro.fields.position') }}</dt><dd class="text-brown-dark">{{ $claim->position ?? '—' }}</dd></div>
                <div><dt class="font-bold text-muted">{{ __('pro.fields.bce_number') }}</dt><dd class="font-mono text-brown-dark">{{ $claim->bce_number }}</dd></div>
                <div><dt class="font-bold text-muted">{{ __('pro.fields.action') }}</dt><dd class="text-brown-dark">{{ __('admin.claims.action_'.$claim->action->value) }}@if ($claim->creates_listing) · {{ __('admin.claims.new_listing') }}@endif</dd></div>
                <div><dt class="font-bold text-muted">{{ __('admin.date') }}</dt><dd class="text-brown-dark">{{ $claim->created_at->locale($locale)->isoFormat('LLL') }}</dd></div>
                <div><dt class="font-bold text-muted">{{ __('admin.language') }}</dt><dd class="text-brown-dark uppercase">{{ $claim->locale }}</dd></div>
            </dl>
            @if ($claim->message)
                <p class="mt-4 rounded-2xl bg-cream-light p-4 text-sm break-words whitespace-pre-line text-brown-dark">{{ $claim->message }}</p>
            @endif
            <div class="mt-5 flex flex-wrap gap-2">
                @if ($proofUrl)
                    <x-button size="sm" variant="secondary" icon="download" :href="$proofUrl" target="_blank" rel="noopener">{{ __('admin.claims.proof') }}</x-button>
                    <p class="self-center text-xs text-muted">{{ __('admin.claims.proof_hint') }}</p>
                @else
                    <p class="text-sm text-muted">{{ __('admin.claims.no_proof') }}</p>
                @endif
                <a href="https://kbopub.economie.fgov.be/kbopub/toonondernemingps.html?ondernemingsnummer={{ preg_replace('/\D/', '', $claim->bce_number) }}" target="_blank" rel="noopener nofollow"
                    class="link inline-flex min-h-11 items-center gap-1 text-sm">{{ __('admin.claims.check_bce') }}<x-icon name="arrow-right" class="size-4" /></a>
            </div>
        </x-card>

        <x-card as="section" aria-labelledby="listing-title">
            <h2 id="listing-title" class="font-display text-xl font-semibold text-brown">{{ __('admin.claims.listing') }}</h2>
            @if ($provider)
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-muted">{{ __('pro.fields.name') }}</dt><dd class="break-words text-brown-dark">{{ $provider->name }}</dd></div>
                    <div><dt class="font-bold text-muted">{{ __('pro.edit.address') }}</dt><dd class="text-brown-dark">{{ trim($provider->street.' '.$provider->house_number) }}, {{ $provider->postal_code }} {{ $provider->city }}</dd></div>
                    <div><dt class="font-bold text-muted">{{ __('admin.providers.bce') }}</dt><dd class="font-mono text-brown-dark">{{ $provider->bce_number ?? '—' }}</dd></div>
                    <div><dt class="font-bold text-muted">{{ __('admin.providers.status') }}</dt><dd class="text-brown-dark">{{ __('admin.provider_statuses.'.$provider->status->value) }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-muted">{{ __('admin.providers.owner') }}</dt><dd class="break-words text-brown-dark">{{ $provider->claimedBy ? $provider->claimedBy->name.' ('.$provider->claimedBy->email.')' : '—' }}</dd></div>
                </dl>
                @if ($provider->bce_number && $provider->bce_number !== $claim->bce_number)
                    <x-alert variant="warning" class="mt-4">{{ __('admin.claims.bce_mismatch') }}</x-alert>
                @endif
                <div class="mt-5 flex flex-wrap gap-2">
                    <x-button size="sm" variant="soft" icon="pencil" :href="route('admin.providers.edit', $provider)">{{ __('admin.providers.edit') }}</x-button>
                    @if ($provider->status === \App\Enums\ProviderStatus::Active)
                        <x-button size="sm" variant="ghost" icon="arrow-right" :href="localized_route('providers.show', $provider)" target="_blank">{{ __('admin.view_public') }}</x-button>
                    @endif
                </div>
            @endif
        </x-card>
    </div>

    @if ($otherClaims->isNotEmpty())
        <x-alert variant="info" class="mt-6" :title="__('admin.claims.others')">
            @foreach ($otherClaims as $other)
                <a href="{{ route('admin.claims.show', $other) }}" class="link">{{ $other->user?->name }} — {{ __('admin.claim_statuses.'.$other->status->value) }}</a>@if (! $loop->last), @endif
            @endforeach
        </x-alert>
    @endif

    <section class="mt-8" aria-labelledby="decision-title">
        <h2 id="decision-title" class="font-display text-2xl font-semibold text-brown">{{ __('admin.decision') }}</h2>
        @if ($claim->status === \App\Enums\ClaimStatus::Pending)
            <div class="mt-4">
                @include('admin._decision', [
                    'approveUrl' => route('admin.claims.approve', $claim),
                    'rejectUrl' => route('admin.claims.reject', $claim),
                    'approveLabel' => __('admin.claims.approve_'.$claim->action->value),
                    'bceCheckbox' => $claim->action === \App\Enums\ClaimAction::Manage,
                ])
            </div>
        @else
            <p class="mt-3 text-brown-dark">
                {{ __('admin.handled_on', ['date' => $claim->handled_at?->locale($locale)->isoFormat('LLL')]) }}
                @if ($claim->admin_note) — {{ $claim->admin_note }} @endif
            </p>
        @endif
    </section>
</x-admin-layout>
