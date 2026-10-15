<x-admin-layout :title="__('admin.claims.title')" :subtitle="__('admin.claims.subtitle')">
    @include('admin._status-tabs', ['route' => 'admin.claims.index', 'current' => $status, 'options' => __('admin.claim_statuses')])

    @if ($claims->isEmpty())
        <x-empty-state class="mt-6" icon="building" :title="__('admin.claims.empty')" />
    @else
        <ul class="mt-6 space-y-3" role="list">
            @foreach ($claims as $claim)
                <li>
                    <x-card padding="sm" class="flex flex-wrap items-center justify-between gap-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-bold break-words text-brown">{{ $claim->provider?->name ?? '—' }} <span class="font-semibold text-muted">· {{ $claim->provider?->city }}</span></p>
                            <p class="text-sm text-muted">
                                {{ $claim->user?->name }} ({{ $claim->user?->email }}) ·
                                <time datetime="{{ $claim->created_at->toIso8601String() }}">{{ $claim->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time>
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <x-badge :variant="$claim->action === \App\Enums\ClaimAction::Delete ? 'rose' : 'orange'">{{ __('admin.claims.action_'.$claim->action->value) }}</x-badge>
                                @if ($claim->creates_listing)
                                    <x-badge variant="brown" icon="plus">{{ __('admin.claims.new_listing') }}</x-badge>
                                @endif
                            </div>
                        </div>
                        <x-button size="sm" variant="soft" icon-right="arrow-right" :href="route('admin.claims.show', $claim)">{{ __('admin.examine') }}</x-button>
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($claims->hasPages())
            <div class="mt-6">{{ $claims->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>
