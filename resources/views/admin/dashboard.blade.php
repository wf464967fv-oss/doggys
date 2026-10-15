<x-admin-layout :title="__('admin.dashboard.title')" :subtitle="__('admin.dashboard.subtitle')">
    <section aria-labelledby="counters-title">
        <h2 id="counters-title" class="sr-only">{{ __('admin.dashboard.counters') }}</h2>
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4" role="list">
            @foreach ($counters as $counter)
                <li>
                    <a href="{{ route($counter['route'], $counter['query'] ?? []) }}"
                        @class([
                            'flex h-full flex-col gap-2 rounded-3xl p-4 ring-1 transition hover:-translate-y-0.5 hover:shadow-lift sm:p-5',
                            'bg-orange-soft ring-orange/40' => $counter['count'] > 0 && $counter['label'] !== 'new_users',
                            'bg-white ring-line' => $counter['count'] === 0 || $counter['label'] === 'new_users',
                        ])>
                        <span class="flex size-10 items-center justify-center rounded-full bg-white text-orange-dark ring-1 ring-line"><x-icon :name="$counter['icon']" class="size-5" /></span>
                        <span class="font-display text-3xl font-semibold text-brown">{{ $counter['count'] }}</span>
                        <span class="text-sm leading-snug font-bold text-brown-dark">{{ __('admin.dashboard.'.$counter['label']) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mt-10" aria-labelledby="latest-title">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="latest-title" class="font-display text-2xl font-semibold text-brown">{{ __('admin.dashboard.latest') }}</h2>
            <a href="{{ route('admin.log') }}" class="link inline-flex min-h-11 items-center text-sm">{{ __('admin.dashboard.full_log') }}</a>
        </div>
        @if ($actions->isEmpty())
            <x-empty-state class="mt-4" icon="clock" :title="__('admin.log.empty')" />
        @else
            <div class="mt-4">@include('admin._action-log')</div>
        @endif
    </section>
</x-admin-layout>
