<x-layouts.app :title="__('auth.confirm_title')" :description="__('auth.confirm_subtitle')" noindex>
    <x-auth-shell :title="__('auth.confirm_title')" :subtitle="__('auth.confirm_subtitle')" icon="lock">
        <form method="POST" action="{{ localized_route('password.confirm.store') }}" class="space-y-5" novalidate>
            @csrf
            <x-input name="password" type="password" :label="__('auth.password_label')" autocomplete="current-password" required autofocus />
            <x-button size="lg" full icon="check">{{ __('auth.confirm_submit') }}</x-button>
        </form>
    </x-auth-shell>
</x-layouts.app>
