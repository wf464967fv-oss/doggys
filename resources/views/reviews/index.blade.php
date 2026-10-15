<x-layouts.app :title="__('reviews.mine.title')" :description="__('reviews.mine.meta')" noindex>
    <x-account-header :title="__('reviews.mine.title')" :subtitle="__('reviews.mine.subtitle')" />

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-10">
        @if ($reviews->isEmpty())
            <x-empty-state icon="star" :title="__('reviews.mine.empty_title')">
                {{ __('reviews.mine.empty_text') }}
                <x-slot:action>
                    <x-button icon="search" :href="localized_route('search')">{{ __('reviews.mine.empty_cta') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <ul class="space-y-4" role="list">
                @foreach ($reviews as $review)
                    <li>
                        <x-card as="article" aria-labelledby="my-review-{{ $review->id }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 id="my-review-{{ $review->id }}" class="font-display text-xl font-semibold break-words text-brown">
                                        @if ($review->provider?->status === \App\Enums\ProviderStatus::Active)
                                            <a href="{{ localized_route('providers.show', $review->provider) }}#review-{{ $review->id }}" class="hover:underline">{{ $review->provider->name }}</a>
                                        @else
                                            {{ $review->provider?->name ?? __('reviews.mine.provider_gone') }}
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-sm text-muted">
                                        <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time>
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($review->status === \App\Enums\ReviewStatus::Hidden)
                                        <x-badge variant="rose" icon="lock">{{ __('reviews.mine.hidden') }}</x-badge>
                                    @endif
                                    <x-stars :rating="$review->rating" />
                                </div>
                            </div>
                            <p class="mt-3 leading-relaxed break-words whitespace-pre-line text-brown-dark">{{ $review->body }}</p>
                            @if ($review->provider_reply)
                                <div class="mt-4 rounded-2xl border-l-4 border-orange bg-cream-light p-4">
                                    <p class="text-sm font-bold text-brown">{{ __('reviews.reply_from', ['name' => $review->provider?->name]) }}</p>
                                    <p class="mt-1.5 text-sm leading-relaxed break-words whitespace-pre-line text-brown-dark">{{ $review->provider_reply }}</p>
                                </div>
                            @endif
                            <div class="mt-5 flex flex-wrap gap-2">
                                <x-button size="sm" variant="soft" icon="pencil" :href="localized_route('reviews.edit', $review)">{{ __('reviews.mine.edit') }}</x-button>
                                <form method="POST" action="{{ localized_route('reviews.destroy', $review) }}"
                                    x-data="confirmSubmit" data-confirm="{{ __('reviews.mine.delete_confirm') }}" x-on:submit="submit">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="ghost" icon="trash">{{ __('reviews.mine.delete') }}</x-button>
                                </form>
                            </div>
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
