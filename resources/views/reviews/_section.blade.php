{{--
    Reviews block of a provider profile: rating summary, publication form (or why it is unavailable),
    paginated list sorted by date with the professional's replies and a "report" form per review.
    Expects $provider, $reviews (paginator), $reviewBlock (null or reviews.blocked.* key), $reportedReviewIds.
--}}
@php($locale = app()->getLocale())

<x-card as="section" id="reviews" aria-labelledby="reviews-title">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="reviews-title" class="font-display text-2xl font-semibold text-brown">{{ __('directory.provider.reviews') }}</h2>
        @if ($provider->reviews_count > 0)
            <x-stars :rating="$provider->rating_avg" :count="$provider->reviews_count" size="size-5" />
        @endif
    </div>

    <div class="mt-5 rounded-3xl bg-cream-light p-4 ring-1 ring-line sm:p-5">
        @if ($reviewBlock === null)
            <h3 class="font-display text-xl font-semibold text-brown">{{ __('reviews.form.title') }}</h3>
            <form method="POST" action="{{ localized_route('reviews.store', $provider) }}" class="mt-4 scroll-mb-24 space-y-5" novalidate>
                @csrf
                @if ($errors->review->any())
                    <x-alert variant="error" :title="__('ui.form_has_errors')" />
                @endif
                <x-star-input bag="review" :hint="__('reviews.form.rating_hint')" />
                <x-textarea name="body" bag="review" :label="__('reviews.form.body')" :hint="__('reviews.form.body_hint')"
                    rows="5" minlength="20" maxlength="2000" required />
                <x-antibot />
                <p class="text-sm text-muted">{{ __('reviews.form.published_as', ['name' => auth()->user()->publicName()]) }}</p>
                <x-button icon="star">{{ __('reviews.form.submit') }}</x-button>
            </form>
        @elseif ($reviewBlock === 'guest')
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-display text-xl font-semibold text-brown">{{ __('reviews.form.title') }}</p>
                    <p class="mt-1 text-sm text-muted">{{ __('reviews.blocked.guest') }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <x-button size="sm" :href="localized_route('login')" icon="user">{{ __('ui.login') }}</x-button>
                    <x-button size="sm" variant="soft" :href="localized_route('register')">{{ __('ui.register') }}</x-button>
                </div>
            </div>
        @else
            <p class="font-display text-xl font-semibold text-brown">{{ __('reviews.form.title') }}</p>
            <x-alert variant="info" class="mt-3">
                {{ __('reviews.blocked.'.$reviewBlock, ['days' => \App\Models\Review::COOLDOWN_DAYS, 'limit' => \App\Models\Review::DAILY_LIMIT]) }}
                @if ($reviewBlock === 'unverified')
                    <a href="{{ localized_route('verification.notice') }}" class="link">{{ __('reviews.blocked.unverified_link') }}</a>
                @elseif ($reviewBlock === 'own_listing')
                    <a href="{{ localized_route('pro.reviews', $provider) }}" class="link">{{ __('reviews.blocked.own_listing_link') }}</a>
                @endif
            </x-alert>
        @endif
    </div>

    @if ($reviews->isNotEmpty())
        <ol class="mt-6 space-y-4" role="list">
            @foreach ($reviews as $review)
                @php($reportBag = 'report'.$review->id)
                <li>
                    <article id="review-{{ $review->id }}" class="scroll-mt-28 rounded-3xl bg-white p-4 ring-1 ring-line sm:p-5" aria-labelledby="review-{{ $review->id }}-author">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-cream font-display text-lg font-semibold text-brown" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($review->user?->name ?? '?', 0, 1)) }}
                                </span>
                                <div>
                                    <p id="review-{{ $review->id }}-author" class="font-bold text-brown">{{ $review->user?->publicName() ?? __('directory.provider.former_member') }}</p>
                                    <p class="text-sm text-muted"><time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->locale($locale)->isoFormat('LL') }}</time></p>
                                </div>
                            </div>
                            <x-stars :rating="$review->rating" />
                        </div>
                        <p class="mt-3 leading-relaxed break-words whitespace-pre-line text-brown-dark">{{ $review->body }}</p>

                        @if ($review->provider_reply)
                            <div class="mt-4 rounded-2xl border-l-4 border-orange bg-cream-light p-4">
                                <p class="flex items-center gap-2 text-sm font-bold text-brown">
                                    <x-icon name="message-circle" class="size-4 text-orange-dark" />{{ __('reviews.reply_from', ['name' => $provider->name]) }}
                                </p>
                                <p class="mt-1.5 text-sm leading-relaxed break-words whitespace-pre-line text-brown-dark">{{ $review->provider_reply }}</p>
                            </div>
                        @endif

                        @auth
                            @if ($review->user_id !== auth()->id())
                                @if (in_array($review->id, $reportedReviewIds, true))
                                    <p class="mt-3 flex min-h-11 items-center gap-1.5 text-sm text-muted"><x-icon name="check" class="size-4" />{{ __('reviews.report.done') }}</p>
                                @else
                                    <details class="group mt-3" @if ($errors->getBag($reportBag)->any()) open @endif>
                                        <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-1.5 rounded-full text-sm font-bold text-muted hover:text-brown [&::-webkit-details-marker]:hidden">
                                            <x-icon name="alert" class="size-4" />{{ __('reviews.report.button') }}
                                        </summary>
                                        <form method="POST" action="{{ localized_route('reviews.report', $review) }}" class="mt-2 space-y-3 rounded-2xl bg-cream-light p-4" novalidate>
                                            @csrf
                                            <x-textarea name="reason" :bag="$reportBag" id="report-reason-{{ $review->id }}" :label="__('reviews.report.reason')"
                                                :hint="__('reviews.report.reason_hint')" rows="3" minlength="10" maxlength="1000" required />
                                            <x-button size="sm" variant="secondary" icon="check">{{ __('reviews.report.submit') }}</x-button>
                                        </form>
                                    </details>
                                @endif
                            @endif
                        @endauth
                    </article>
                </li>
            @endforeach
        </ol>
        @if ($reviews->hasPages())
            <div class="mt-6">{{ $reviews->onEachSide(1)->links() }}</div>
        @endif
    @else
        <x-empty-state icon="star" :title="__('directory.provider.no_reviews_title')" class="mt-5">
            {{ __('directory.provider.no_reviews_text') }}
        </x-empty-state>
    @endif

    <p class="mt-4 text-sm">
        <a href="{{ localized_route('reviews.policy') }}" class="link inline-flex min-h-11 items-center gap-1">{{ __('directory.provider.reviews_policy') }}<x-icon name="arrow-right" class="size-4" /></a>
    </p>
</x-card>
