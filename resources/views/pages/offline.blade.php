<x-layouts.app :title="__('ui.offline_title')" :description="__('ui.offline_text')" noindex bare>
    <x-message-page icon="info" :eyebrow="__('ui.offline_eyebrow')" :title="__('ui.offline_title')" :text="__('ui.offline_text')">
        <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
    </x-message-page>
</x-layouts.app>
