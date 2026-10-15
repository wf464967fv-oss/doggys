<x-layouts.app :title="__('auth.forgot_title')" :description="__('auth.forgot_subtitle')" noindex>
    <x-auth-shell :title="__('auth.forgot_title')" :subtitle="__('auth.forgot_subtitle')" icon="key">
        <form method="POST" action="{{ localized_route('password.email') }}" class="space-y-5" novalidate>
            @csrf
            <x-input name="email" type="email" :label="__('auth.email')" autocomplete="email" required autofocus />
            <x-button size="lg" full icon="mail">{{ __('auth.forgot_submit') }}</x-button>
        </form>
        <x-slot:footer>
            <a href="{{ localized_route('login') }}" class="link">{{ __('auth.back_to_login') }}</a>
        </x-slot:footer>
    </x-auth-shell>
</x-layouts.app>
