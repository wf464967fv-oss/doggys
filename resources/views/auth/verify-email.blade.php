<x-layouts.app :title="__('auth.verify_title')" :description="__('auth.verify_meta')" noindex>
    <x-auth-shell :title="__('auth.verify_title')" :subtitle="__('auth.verify_subtitle', ['email' => auth()->user()->email])" icon="mail">
        <div class="space-y-5">
            <p class="leading-relaxed text-brown-dark">{{ __('auth.verify_text') }}</p>
            <form method="POST" action="{{ localized_route('verification.send') }}">
                @csrf
                <x-button size="lg" full icon="mail">{{ __('auth.verify_resend') }}</x-button>
            </form>
            <p class="text-center text-sm text-muted">
                {{ __('auth.verify_wrong_address') }}
                <a href="{{ localized_route('account.profile') }}" class="link">{{ __('auth.verify_manage') }}</a>
            </p>
            <form method="POST" action="{{ localized_route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="link min-h-11">{{ __('ui.logout') }}</button>
            </form>
        </div>
    </x-auth-shell>
</x-layouts.app>
