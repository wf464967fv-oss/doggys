<x-layouts.app :title="__('auth.login_title')" :description="__('auth.login_meta')" noindex>
    <x-auth-shell :title="__('auth.login_title')" :subtitle="__('auth.login_subtitle')" icon="user">
        <form method="POST" action="{{ localized_route('login.store') }}" class="space-y-5" novalidate>
            @csrf
            <x-input name="email" type="email" :label="__('auth.email')" autocomplete="email" required autofocus />
            <x-input name="password" type="password" :label="__('auth.password_label')" autocomplete="current-password" required />
            <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1">
                <x-checkbox name="remember">{{ __('auth.remember') }}</x-checkbox>
                <a href="{{ localized_route('password.request') }}" class="link inline-block py-2.5 text-sm">{{ __('auth.forgot_link') }}</a>
            </div>
            <x-button size="lg" full>{{ __('auth.login_submit') }}</x-button>
        </form>
        <x-slot:footer>
            {{ __('auth.no_account') }}
            <a href="{{ localized_route('register') }}" class="link">{{ __('auth.register_link') }}</a>
        </x-slot:footer>
    </x-auth-shell>
</x-layouts.app>
