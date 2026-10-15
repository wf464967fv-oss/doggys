<x-layouts.app :title="__('auth.reset_title')" :description="__('auth.reset_subtitle')" noindex>
    <x-auth-shell :title="__('auth.reset_title')" :subtitle="__('auth.reset_subtitle')" icon="lock">
        <form method="POST" action="{{ localized_route('password.update') }}" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-input name="email" type="email" :label="__('auth.email')" :value="$request->query('email')" autocomplete="email" required />
            <x-input name="password" type="password" :label="__('auth.password_new')" :hint="__('auth.password_rules')" autocomplete="new-password" required autofocus />
            <x-input name="password_confirmation" type="password" :label="__('auth.password_confirm')" autocomplete="new-password" required />
            <x-button size="lg" full icon="check">{{ __('auth.reset_submit') }}</x-button>
        </form>
    </x-auth-shell>
</x-layouts.app>
