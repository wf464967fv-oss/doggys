@php($provider = $badgeRequest->provider)

<x-admin-layout :title="__('admin.badges.show_title', ['name' => $provider?->name])" :subtitle="__('admin.claim_statuses.'.$badgeRequest->status->value)">
    <x-card as="section" aria-labelledby="request-title">
        <h2 id="request-title" class="font-display text-xl font-semibold text-brown">{{ __('admin.claims.request') }}</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="font-bold text-muted">{{ __('admin.claims.applicant') }}</dt><dd class="break-words text-brown-dark">{{ $badgeRequest->user?->name }}<br>{{ $badgeRequest->user?->email }}</dd></div>
            <div><dt class="font-bold text-muted">{{ __('pro.fields.regulatory_number') }}</dt><dd class="font-mono text-brown-dark">{{ $badgeRequest->regulatory_number }}</dd></div>
            <div><dt class="font-bold text-muted">{{ __('admin.claims.listing') }}</dt><dd class="text-brown-dark">{{ $provider?->name }} · {{ $provider?->categories->map->label()->join(', ') }}</dd></div>
        </dl>
        @if ($badgeRequest->message)
            <p class="mt-4 rounded-2xl bg-cream-light p-4 text-sm break-words whitespace-pre-line text-brown-dark">{{ $badgeRequest->message }}</p>
        @endif
        @if ($proofUrl)
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <x-button size="sm" variant="secondary" icon="download" :href="$proofUrl" target="_blank" rel="noopener">{{ __('admin.claims.proof') }}</x-button>
                <p class="text-xs text-muted">{{ __('admin.claims.proof_hint') }}</p>
            </div>
        @endif
    </x-card>

    <section class="mt-8" aria-labelledby="decision-title">
        <h2 id="decision-title" class="font-display text-2xl font-semibold text-brown">{{ __('admin.decision') }}</h2>
        @if ($badgeRequest->status === \App\Enums\ClaimStatus::Pending)
            <div class="mt-4">
                @include('admin._decision', [
                    'approveUrl' => route('admin.badges.approve', $badgeRequest),
                    'rejectUrl' => route('admin.badges.reject', $badgeRequest),
                    'approveLabel' => __('admin.badges.approve'),
                ])
            </div>
        @else
            <p class="mt-3 text-brown-dark">{{ __('admin.handled_on', ['date' => $badgeRequest->handled_at?->locale(app()->getLocale())->isoFormat('LLL')]) }}
                @if ($badgeRequest->admin_note) — {{ $badgeRequest->admin_note }} @endif</p>
        @endif
    </section>
</x-admin-layout>
