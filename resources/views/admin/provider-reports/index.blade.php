<x-admin-layout :title="__('admin.reports.title')" :subtitle="__('admin.reports.subtitle')">
    @include('admin._status-tabs', ['route' => 'admin.provider-reports.index', 'current' => $status, 'options' => __('admin.report_statuses')])

    @if ($reports->isEmpty())
        <x-empty-state class="mt-6" icon="check-circle" :title="__('admin.reports.empty')" />
    @else
        <ul class="mt-6 space-y-3" role="list">
            @foreach ($reports as $report)
                @php($isRemoval = $report->type === \App\Enums\ProviderReportType::RemovalRequest)
                <li>
                    <x-card as="article" padding="sm" class="sm:px-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold break-words text-brown">
                                    @if ($report->provider)
                                        <a href="{{ route('admin.providers.edit', $report->provider) }}" class="hover:underline">{{ $report->provider->name }}</a>
                                        <span class="font-semibold text-muted">· {{ $report->provider->city }}</span>
                                    @endif
                                </p>
                                <p class="text-sm text-muted">{{ $report->email ?? __('admin.reports.anonymous') }} ·
                                    <time datetime="{{ $report->created_at->toIso8601String() }}">{{ $report->created_at->locale(app()->getLocale())->isoFormat('LLL') }}</time></p>
                            </div>
                            <x-badge :variant="$isRemoval ? 'rose' : 'orange'">{{ __('admin.reports.types.'.$report->type->value) }}</x-badge>
                        </div>
                        <p class="mt-3 rounded-2xl bg-cream-light p-3 text-sm break-words whitespace-pre-line text-brown-dark">{{ $report->message }}</p>

                        @if ($report->status === \App\Enums\ReportStatus::Pending)
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                @if ($isRemoval && $report->provider?->status !== \App\Enums\ProviderStatus::Removed)
                                    <form method="POST" action="{{ route('admin.provider-reports.apply-removal', $report) }}"
                                        x-data="confirmSubmit" data-confirm="{{ __('admin.reports.apply_confirm', ['name' => $report->provider?->name]) }}" x-on:submit="submit">
                                        @csrf
                                        <x-button size="sm" variant="danger" icon="trash">{{ __('admin.reports.apply_removal') }}</x-button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.provider-reports.close', $report) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="handled">
                                    <x-button size="sm" variant="soft" icon="check">{{ __('admin.reports.mark_handled') }}</x-button>
                                </form>
                                <form method="POST" action="{{ route('admin.provider-reports.close', $report) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="dismissed">
                                    <x-button size="sm" variant="ghost" icon="x">{{ __('admin.reports.dismiss') }}</x-button>
                                </form>
                            </div>
                            @if ($isRemoval)
                                <div class="mt-2">@include('admin._confirm-hint')</div>
                            @endif
                        @endif
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($reports->hasPages())
            <div class="mt-6">{{ $reports->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>
