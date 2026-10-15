<x-admin-layout :title="__('admin.providers.title')" :subtitle="trans_choice('admin.providers.count', $providers->total(), ['count' => $providers->total()])">
    <x-slot:actions>
        <x-button size="sm" icon="plus" :href="route('admin.providers.create')">{{ __('admin.providers.create') }}</x-button>
    </x-slot:actions>

    <form method="GET" action="{{ route('admin.providers.index') }}" class="grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-line sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end" role="search">
        <x-input name="q" type="search" :label="__('admin.search')" :value="$filters['q'] ?? null" :placeholder="__('admin.providers.search_hint')" icon="search" />
        <x-select name="status" :label="__('admin.providers.status')" :options="__('admin.provider_statuses')" :value="$filters['status'] ?? null" :placeholder="__('admin.all')" />
        <x-button variant="secondary" icon="search">{{ __('admin.search') }}</x-button>
    </form>

    @if ($providers->isEmpty())
        <x-empty-state class="mt-6" icon="search" :title="__('admin.providers.empty')" />
    @else
        <ul class="mt-6 space-y-3" role="list">
            @foreach ($providers as $provider)
                <li>
                    <x-card padding="sm" class="flex flex-wrap items-center justify-between gap-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-bold break-words text-brown">{{ $provider->name }}</p>
                            <p class="text-sm text-muted">{{ $provider->postal_code }} {{ $provider->city }} · {{ __('admin.sources.'.$provider->source->value) }}
                                @if ($provider->claimedBy) · {{ __('admin.providers.managed_by', ['name' => $provider->claimedBy->name]) }} @endif</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <x-badge :variant="['active' => 'verified', 'hidden' => 'orange', 'removed' => 'rose'][$provider->status->value]">{{ __('admin.provider_statuses.'.$provider->status->value) }}</x-badge>
                                @if ($provider->isRegulated())
                                    <x-badge variant="regulated" />
                                @elseif ($provider->isVerified())
                                    <x-badge variant="verified" />
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-button size="sm" variant="soft" icon="pencil" :href="route('admin.providers.edit', $provider)">{{ __('admin.providers.edit') }}</x-button>
                            @unless ($provider->status === \App\Enums\ProviderStatus::Removed)
                                <form method="POST" action="{{ route('admin.providers.toggle', $provider) }}">
                                    @csrf
                                    <x-button size="sm" variant="ghost" :icon="$provider->status === \App\Enums\ProviderStatus::Active ? 'lock' : 'check'">
                                        {{ $provider->status === \App\Enums\ProviderStatus::Active ? __('admin.providers.hide') : __('admin.providers.publish') }}
                                    </x-button>
                                </form>
                            @endunless
                        </div>
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($providers->hasPages())
            <div class="mt-6">{{ $providers->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>
