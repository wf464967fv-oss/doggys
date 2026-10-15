<x-layouts.app :title="__('contact.meta_title')" noindex :description="__('contact.meta_description')">
    <section class="relative overflow-hidden bg-gradient-to-b from-cream to-cream-light">
        <x-paw class="absolute top-8 right-[6%] size-12 rotate-12 text-orange/25" />
        <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 sm:px-6 sm:pt-8">
            <x-breadcrumbs :items="[__('directory.breadcrumb_home') => localized_route('home'), __('contact.breadcrumb') => null]" />
            <h1 class="mt-6 font-display text-3xl leading-tight font-semibold text-brown sm:text-5xl">{{ __('contact.title') }}</h1>
            <p class="mt-3 max-w-2xl text-lg leading-relaxed text-brown-dark">{{ __('contact.intro') }}</p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-8">
        <x-card padding="lg">
            <h2 class="font-display text-2xl font-semibold text-brown">{{ __('contact.form_title') }}</h2>
            <form method="POST" action="{{ localized_route('contact.store') }}" class="mt-5 space-y-5" novalidate>
                @csrf
                @if ($errors->any())
                    <x-alert variant="error" :title="__('ui.form_has_errors')" />
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="name" :label="__('contact.name')" :value="auth()->user()?->name" autocomplete="name" maxlength="100" required />
                    <x-input name="email" type="email" :label="__('contact.email')" :value="auth()->user()?->email" autocomplete="email" maxlength="255" required />
                </div>
                <x-input name="subject" :label="__('contact.subject')" maxlength="150" required />
                <x-textarea name="message" :label="__('contact.message')" :hint="__('contact.message_hint')" rows="7" minlength="10" maxlength="5000" required />
                <x-antibot />
                <p class="text-sm text-muted">{{ __('contact.privacy') }}</p>
                <x-button icon="mail">{{ __('contact.submit') }}</x-button>
            </form>
        </x-card>

        <aside class="space-y-6">
            <x-card>
                <span class="flex size-12 items-center justify-center rounded-full bg-cream text-brown"><x-icon name="mail" class="size-6" /></span>
                <h2 class="mt-3 font-display text-xl font-semibold text-brown">{{ __('contact.direct_title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('contact.direct_text') }}</p>
                <a href="mailto:{{ config('mail.from.address') }}" class="link mt-2 inline-flex min-h-11 items-center break-all">{{ config('mail.from.address') }}</a>
            </x-card>
            <x-card>
                <h2 class="font-display text-xl font-semibold text-brown">{{ __('contact.shortcuts_title') }}</h2>
                <ul class="mt-3 space-y-3 text-sm leading-relaxed text-muted">
                    <li class="flex gap-3"><x-icon name="building" class="mt-0.5 size-5 shrink-0 text-orange-dark" /><span>{{ __('contact.shortcut_listing') }}</span></li>
                    <li class="flex gap-3"><x-icon name="star" class="mt-0.5 size-5 shrink-0 text-orange-dark" />
                        <span>{{ __('contact.shortcut_reviews') }} <a href="{{ localized_route('reviews.policy') }}" class="link inline-flex min-h-11 items-center">{{ __('reviews.form.rules_link') }}</a></span></li>
                    <li class="flex gap-3"><x-icon name="lock" class="mt-0.5 size-5 shrink-0 text-orange-dark" /><span>{{ __('contact.shortcut_privacy') }}</span></li>
                </ul>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
