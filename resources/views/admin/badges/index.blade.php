<x-admin-layout :title="__('admin.badges.title')" :subtitle="__('admin.badges.subtitle')">
    @include('admin._status-tabs', ['route' => 'admin.badges.index', 'current' => $status, 'options' => __('admin.claim_statuses')])

    @if ($badgeRequests->isEmpty())
        <x-empty-state class="mt-6" icon="badge-check" :title="__('admin.badges.empty')" />
    @else
        <ul class="mt-6 space-y-3" role="list">
            @foreach ($badgeRequests as $badgeRequest)
                <li>
                    <x-card padding="sm" class="flex flex-wrap items-center justify-between gap-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-bold break-words text-brown">{{ $badgeRequest->provider?->name }} <span class="font-semibold text-muted">· {{ $badgeRequest->provider?->city }}</span></p>
                            <p class="text-sm text-muted">{{ $badgeRequest->user?->name }} · <span class="font-mono">{{ $badgeRequest->regulatory_number }}</span> ·
                                <time datetime="{{ $badgeRequest->created_at->toIso8601String() }}">{{ $badgeRequest->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time></p>
                        </div>
                        <x-button size="sm" variant="soft" icon-right="arrow-right" :href="route('admin.badges.show', $badgeRequest)">{{ __('admin.examine') }}</x-button>
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($badgeRequests->hasPages())
            <div class="mt-6">{{ $badgeRequests->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>
