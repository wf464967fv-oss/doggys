<x-layouts.app :title="__('reviews.edit.title')" :description="__('reviews.mine.meta')" noindex>
    <x-account-header :title="__('reviews.edit.title')" :subtitle="__('reviews.edit.subtitle', ['name' => $review->provider?->name])" />

    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10">
        <x-card padding="lg">
            <form method="POST" action="{{ localized_route('reviews.update', $review) }}" class="space-y-5" novalidate>
                @csrf
                @method('PUT')
                @if ($errors->review->any())
                    <x-alert variant="error" :title="__('ui.form_has_errors')" />
                @endif
                <x-star-input bag="review" :value="$review->rating" />
                <x-textarea name="body" bag="review" :label="__('reviews.form.body')" :hint="__('reviews.form.body_hint')"
                    :value="$review->body" rows="6" minlength="20" maxlength="2000" required />
                <div class="flex flex-wrap gap-3">
                    <x-button icon="check">{{ __('reviews.edit.submit') }}</x-button>
                    <x-button variant="ghost" :href="localized_route('account.reviews')">{{ __('ui.cancel') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
