{{--
    Approve / reject forms of a pending request. Expects $approveUrl, $rejectUrl, $approveLabel and optionally
    $bceCheckbox (true: "BCE number checked" checkbox for claims of the "manage" kind).
--}}
<div class="grid gap-4 md:grid-cols-2">
    <form method="POST" action="{{ $approveUrl }}" class="space-y-4 rounded-3xl bg-verified-soft p-5 ring-1 ring-verified/25" novalidate>
        @csrf
        <h3 class="flex items-center gap-2 font-display text-xl font-semibold text-verified"><x-icon name="check-circle" class="size-5" />{{ __('admin.approve') }}</h3>
        @if ($bceCheckbox ?? false)
            <x-checkbox name="bce_verified" id="bce-verified" :hint="__('admin.claims.bce_verified_hint')">{{ __('admin.claims.bce_verified') }}</x-checkbox>
        @endif
        <x-textarea name="note" id="approve-note" :label="__('admin.note')" :hint="__('admin.note_hint')" rows="3" maxlength="1000" optional />
        <x-button icon="check">{{ $approveLabel }}</x-button>
    </form>
    <form method="POST" action="{{ $rejectUrl }}" class="space-y-4 rounded-3xl bg-rose-soft p-5 ring-1 ring-rose/30" novalidate>
        @csrf
        <h3 class="flex items-center gap-2 font-display text-xl font-semibold text-rose-dark"><x-icon name="x" class="size-5" />{{ __('admin.reject') }}</h3>
        <x-textarea name="note" id="reject-note" :label="__('admin.note')" :hint="__('admin.reject_hint')" rows="3" maxlength="1000" optional />
        <x-button variant="danger" icon="x">{{ __('admin.reject') }}</x-button>
    </form>
</div>
