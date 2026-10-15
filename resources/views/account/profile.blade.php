<x-layouts.app :title="__('account.profile_title')" :description="__('account.profile_meta')" noindex>
    <x-account-header :title="__('account.profile_title')" :subtitle="__('account.profile_subtitle')" />

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 sm:py-10">
        <x-card as="section" aria-labelledby="profile-info-title">
            <h2 id="profile-info-title" class="font-display text-2xl font-semibold text-brown">{{ __('account.info_title') }}</h2>
            <p class="mt-1 text-muted">{{ __('account.info_text') }}</p>
            <form method="POST" action="{{ localized_route('account.profile.update') }}" class="mt-5 space-y-5" novalidate>
                @csrf
                @method('PUT')
                <x-input name="name" :label="__('auth.name')" :value="$user->name" autocomplete="name" bag="updateProfileInformation" required maxlength="255" />
                <x-input name="email" type="email" :label="__('auth.email')" :value="$user->email" :hint="__('account.email_change_hint')" autocomplete="email" bag="updateProfileInformation" required />
                <div class="flex flex-wrap items-center gap-3">
                    <x-badge :variant="$user->isPro() ? 'brown' : 'orange'" :icon="$user->isPro() ? 'building' : 'heart'">{{ __('account.role_'.$user->role->value) }}</x-badge>
                </div>
                <x-button icon="check">{{ __('ui.save') }}</x-button>
            </form>
        </x-card>

        <x-card as="section" aria-labelledby="password-title">
            <h2 id="password-title" class="font-display text-2xl font-semibold text-brown">{{ __('account.password_title') }}</h2>
            <form method="POST" action="{{ localized_route('account.password.update') }}" class="mt-5 space-y-5" novalidate>
                @csrf
                @method('PUT')
                <x-input name="current_password" type="password" :label="__('auth.password_current')" autocomplete="current-password" bag="updatePassword" required />
                <x-input name="password" type="password" :label="__('auth.password_new')" :hint="__('auth.password_rules')" autocomplete="new-password" bag="updatePassword" required />
                <x-input name="password_confirmation" type="password" :label="__('auth.password_confirm')" autocomplete="new-password" bag="updatePassword" required />
                <x-button icon="lock">{{ __('account.password_submit') }}</x-button>
            </form>
        </x-card>

        <x-card as="section" aria-labelledby="data-title">
            <h2 id="data-title" class="font-display text-2xl font-semibold text-brown">{{ __('account.export_title') }}</h2>
            <p class="mt-1 text-muted">{{ __('account.export_text') }}</p>
            <x-button class="mt-5" variant="soft" icon="download" :href="localized_route('account.export')">{{ __('account.export_submit') }}</x-button>
        </x-card>

        <x-card as="section" aria-labelledby="delete-title" class="ring-rose/40">
            <h2 id="delete-title" class="font-display text-2xl font-semibold text-rose-dark">{{ __('account.delete_title') }}</h2>
            <p class="mt-1 text-muted">{{ __('account.delete_text') }}</p>
            <form method="POST" action="{{ localized_route('account.destroy') }}" class="mt-5 space-y-5" novalidate
                x-data="confirmSubmit" data-confirm="{{ __('account.delete_confirm') }}" x-on:submit="submit">
                @csrf
                @method('DELETE')
                <x-input name="password" type="password" :label="__('account.delete_password')" autocomplete="current-password" bag="deleteAccount" required />
                <x-button variant="danger" icon="trash">{{ __('account.delete_submit') }}</x-button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
