<x-admin-layout :title="__('admin.reviews.title')" :subtitle="__('admin.reviews.subtitle')">
    <section aria-labelledby="reports-title">
        <h2 id="reports-title" class="font-display text-2xl font-semibold text-brown">{{ __('admin.reviews.reports', ['count' => $reports->count()]) }}</h2>
        @if ($reports->isEmpty())
            <p class="mt-3 flex items-center gap-2 text-muted"><x-icon name="check-circle" class="size-5 text-verified" />{{ __('admin.reviews.no_reports') }}</p>
        @else
            <ul class="mt-4 space-y-3" role="list">
                @foreach ($reports as $report)
                    @php($review = $report->review)
                    <li>
                        <x-card as="article" padding="sm" class="bg-orange-soft sm:px-5">
                            <p class="text-sm font-bold text-brown">{{ __('admin.reviews.reported_by', ['name' => $report->user?->name ?? __('admin.reports.anonymous')]) }}</p>
                            <p class="mt-1 text-sm break-words whitespace-pre-line text-brown-dark">« {{ $report->reason }} »</p>
                            <div class="mt-3 rounded-2xl bg-white p-3 ring-1 ring-line">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-bold text-brown">{{ $review->user?->name ?? '—' }} → {{ $review->provider?->name }}</p>
                                    <x-stars :rating="$review->rating" />
                                </div>
                                <p class="mt-2 text-sm break-words whitespace-pre-line text-brown-dark">{{ $review->body }}</p>
                            </div>
                            <div class="mt-3 flex flex-wrap items-start gap-2">
                                <form method="POST" action="{{ route('admin.reviews.hide', $review) }}" class="flex flex-wrap items-end gap-2">
                                    @csrf
                                    <x-input name="note" id="hide-note-{{ $report->id }}" :label="__('admin.reviews.hide_reason')" class="min-w-56" maxlength="1000" />
                                    <x-button size="sm" variant="danger" icon="lock">{{ __('admin.reviews.hide') }}</x-button>
                                </form>
                                <form method="POST" action="{{ route('admin.review-reports.dismiss', $report) }}" class="self-end">
                                    @csrf
                                    <x-button size="sm" variant="soft" icon="x">{{ __('admin.reviews.dismiss_report') }}</x-button>
                                </form>
                            </div>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mt-10" aria-labelledby="all-title">
        <h2 id="all-title" class="font-display text-2xl font-semibold text-brown">{{ __('admin.reviews.all') }}</h2>
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mt-4 grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-line sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end" role="search">
            <x-input name="q" type="search" :label="__('admin.search')" :value="$filters['q'] ?? null" icon="search" />
            <x-select name="status" :label="__('admin.providers.status')" :options="__('admin.review_statuses')" :value="$filters['status'] ?? null" :placeholder="__('admin.all')" />
            <x-button variant="secondary" icon="search">{{ __('admin.search') }}</x-button>
        </form>

        @if ($reviews->isEmpty())
            <x-empty-state class="mt-6" icon="star" :title="__('admin.reviews.empty')" />
        @else
            <ul class="mt-4 space-y-3" role="list">
                @foreach ($reviews as $review)
                    <li>
                        <x-card as="article" padding="sm" class="sm:px-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="min-w-0 text-sm font-bold break-words text-brown">{{ $review->user?->name ?? '—' }} → {{ $review->provider?->name }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($review->status === \App\Enums\ReviewStatus::Hidden)
                                        <x-badge variant="rose" icon="lock">{{ __('admin.review_statuses.hidden') }}</x-badge>
                                    @endif
                                    @if ($review->reports_count > 0)
                                        <x-badge variant="orange" icon="alert">{{ trans_choice('admin.reviews.reports_count', $review->reports_count, ['count' => $review->reports_count]) }}</x-badge>
                                    @endif
                                    <x-stars :rating="$review->rating" />
                                </div>
                            </div>
                            <p class="mt-2 text-sm break-words whitespace-pre-line text-brown-dark">{{ \Illuminate\Support\Str::limit($review->body, 400) }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if ($review->status === \App\Enums\ReviewStatus::Published)
                                    <form method="POST" action="{{ route('admin.reviews.hide', $review) }}">
                                        @csrf
                                        <x-button size="sm" variant="soft" icon="lock">{{ __('admin.reviews.hide') }}</x-button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.reviews.publish', $review) }}">
                                        @csrf
                                        <x-button size="sm" variant="soft" icon="check">{{ __('admin.reviews.publish') }}</x-button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                    x-data="confirmSubmit" data-confirm="{{ __('admin.reviews.delete_confirm') }}" x-on:submit="submit">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="ghost" icon="trash">{{ __('admin.delete') }}</x-button>
                                </form>
                            </div>
                        </x-card>
                    </li>
                @endforeach
            </ul>
            @if ($reviews->hasPages())
                <div class="mt-6">{{ $reviews->onEachSide(1)->links() }}</div>
            @endif
        @endif
    </section>
</x-admin-layout>
