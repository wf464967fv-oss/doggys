@php
    $accountType = old('account_type', request('type') === 'pro' ? 'pro' : 'owner');
@endphp

<x-layouts.app :title="__('auth.register_title')" :description="__('auth.register_meta')" noindex>
    <x-auth-shell :title="__('auth.register_title')" :subtitle="__('auth.register_subtitle')" icon="paw">
        <form method="POST" action="{{ localized_route('register.store') }}" class="relative space-y-5" novalidate>
            @csrf
            <fieldset>
                <legend class="text-sm font-bold text-brown">{{ __('auth.account_type') }}</legend>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    @foreach (['owner' => 'heart', 'pro' => 'building'] as $type => $typeIcon)
                        <label class="relative flex cursor-pointer flex-col gap-1 rounded-2xl border-2 border-line bg-cream-light p-4 pr-10 transition hover:border-brown-mid has-checked:border-orange has-checked:bg-orange-soft has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-brown">
                            <input type="radio" name="account_type" value="{{ $type }}" class="peer sr-only" @checked($accountType === $type)>
                            <span class="flex items-center gap-2 font-display text-lg font-semibold text-brown">
                                <x-icon :name="$typeIcon" class="text-orange-dark" />{{ __('auth.account_type_'.$type) }}
                            </span>
                            <span class="text-sm text-muted">{{ __('auth.account_type_'.$type.'_help') }}</span>
                            <x-icon name="check-circle" class="absolute top-3 right-3 hidden size-5 text-orange-dark peer-checked:block" />
                        </label>
                    @endforeach
                </div>
                @error('account_type')
                    <p class="mt-1.5 text-sm font-semibold text-rose-dark">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-input name="name" :label="__('auth.name')" autocomplete="name" required maxlength="255" />
            <x-input name="email" type="email" :label="__('auth.email')" autocomplete="email" required />
            <x-input name="password" type="password" :label="__('auth.password_label')" :hint="__('auth.password_rules')" autocomplete="new-password" required />
            <x-input name="password_confirmation" type="password" :label="__('auth.password_confirm')" autocomplete="new-password" required />
            <x-checkbox name="terms" required>
                {!! __('auth.terms_accept', [
                    'terms' => '<a class="link" href="'.e(localized_route('legal.terms')).'">'.e(__('ui.footer_terms')).'</a>',
                    'privacy' => '<a class="link" href="'.e(localized_route('legal.privacy')).'">'.e(__('ui.footer_privacy')).'</a>',
                ]) !!}
            </x-checkbox>
            <x-antibot />
            <x-button size="lg" full icon-right="arrow-right">{{ __('auth.register_submit') }}</x-button>
        </form>
        <x-slot:footer>
            {{ __('auth.has_account') }}
            <a href="{{ localized_route('login') }}" class="link">{{ __('auth.login_link') }}</a>
        </x-slot:footer>
    </x-auth-shell>
</x-layouts.app>
