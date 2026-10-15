<x-layouts.app :title="__('ui.coming_soon_title')" :description="__('ui.coming_soon_text')" noindex>
    <x-message-page icon="sparkles" :eyebrow="__('ui.coming_soon_eyebrow')" :title="__('ui.coming_soon_title')" :text="__('ui.coming_soon_text')">
        <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
        @guest
            <x-button variant="soft" :href="localized_route('register')">{{ __('ui.register') }}</x-button>
        @endguest
    </x-message-page>
</x-layouts.app>
