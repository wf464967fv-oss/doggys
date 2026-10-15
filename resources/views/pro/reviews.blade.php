<x-layouts.app :title="__('pro.reviews.title')" :description="__('pro.space.meta')" noindex>
    <x-account-header :title="__('pro.reviews.title')" :subtitle="$provider->name" />

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-10">
        <p class="text-muted">{{ __('pro.reviews.intro') }}</p>

        @if ($reviews->isEmpty())
            <x-empty-state class="mt-6" icon="star" :title="__('pro.reviews.empty_title')">{{ __('pro.reviews.empty_text') }}</x-empty-state>
        @else
            <ul class="mt-6 space-y-4" role="list">
                @foreach ($reviews as $review)
                    @php($bag = 'reply'.$review->id)
                    <li>
                        <x-card as="article" id="review-{{ $review->id }}" class="scroll-mt-28" aria-labelledby="review-{{ $review->id }}-author">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p id="review-{{ $review->id }}-author" class="font-bold text-brown">{{ $review->user?->publicName() ?? __('directory.provider.former_member') }}</p>
                                    <p class="text-sm text-muted"><time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time></p>
                                </div>
                                <x-stars :rating="$review->rating" />
                            </div>
                            <p class="mt-3 leading-relaxed break-words whitespace-pre-line text-brown-dark">{{ $review->body }}</p>

                            <form method="POST" action="{{ localized_route('pro.reply', $review) }}" class="mt-4 space-y-3 rounded-2xl bg-cream-light p-4" novalidate>
                                @csrf
                                @method('PUT')
                                <x-textarea name="reply" :bag="$bag" id="reply-{{ $review->id }}" :value="$review->provider_reply"
                                    :label="$review->provider_reply ? __('pro.reviews.your_reply') : __('pro.reviews.reply')" :hint="__('pro.reviews.reply_hint')"
                                    rows="3" maxlength="2000" required />
                                <x-button size="sm" variant="secondary" icon="message-circle">{{ $review->provider_reply ? __('pro.reviews.update') : __('pro.reviews.submit') }}</x-button>
                            </form>
                        </x-card>
                    </li>
                @endforeach
            </ul>
            @if ($reviews->hasPages())
                <div class="mt-8">{{ $reviews->onEachSide(1)->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
