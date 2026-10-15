<x-admin-layout :title="__('admin.users.title')" :subtitle="trans_choice('admin.users.count', $users->total(), ['count' => $users->total()])">
    <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-line sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end" role="search">
        <x-input name="q" type="search" :label="__('admin.search')" :value="$filters['q'] ?? null" :placeholder="__('admin.users.search_hint')" icon="search" />
        <x-select name="role" :label="__('admin.users.role')" :options="__('admin.roles')" :value="$filters['role'] ?? null" :placeholder="__('admin.all')" />
        <x-button variant="secondary" icon="search">{{ __('admin.search') }}</x-button>
    </form>
    <div class="mt-3">@include('admin._confirm-hint')</div>

    @if ($users->isEmpty())
        <x-empty-state class="mt-6" icon="users" :title="__('admin.users.empty')" />
    @else
        <ul class="mt-4 space-y-3" role="list">
            @foreach ($users as $user)
                <li>
                    <x-card padding="sm" class="flex flex-wrap items-center justify-between gap-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-bold break-words text-brown">{{ $user->name }}
                                @unless ($user->hasVerifiedEmail())<x-badge variant="neutral" class="ml-1">{{ __('admin.users.unverified') }}</x-badge>@endunless
                            </p>
                            <p class="text-sm break-all text-muted">{{ $user->email }}</p>
                            <p class="text-xs text-muted">{{ __('admin.users.meta', ['date' => $user->created_at->locale(app()->getLocale())->isoFormat('LL'), 'reviews' => $user->reviews_count, 'listings' => $user->claimed_providers_count]) }}</p>
                        </div>
                        @if ($user->is(auth()->user()))
                            <x-badge variant="brown">{{ __('admin.users.you') }}</x-badge>
                        @else
                            <div class="flex flex-wrap items-end gap-2">
                                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-end gap-2">
                                    @csrf
                                    @method('PUT')
                                    <x-select name="role" id="role-{{ $user->id }}" :label="__('admin.users.role')" :options="__('admin.roles')" :value="$user->role->value" />
                                    <x-button size="sm" variant="soft" icon="check">{{ __('ui.save') }}</x-button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    x-data="confirmSubmit" data-confirm="{{ __('admin.users.delete_confirm', ['name' => $user->name]) }}" x-on:submit="submit">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="ghost" icon="trash">{{ __('admin.delete') }}</x-button>
                                </form>
                            </div>
                        @endif
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($users->hasPages())
            <div class="mt-6">{{ $users->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>
