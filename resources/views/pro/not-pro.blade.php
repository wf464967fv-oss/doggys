<x-layouts.app :title="__('pro.not_pro.title')" :description="__('pro.not_pro.text')" noindex>
    <x-message-page icon="building" :eyebrow="__('pro.not_pro.eyebrow')" :title="__('pro.not_pro.title')" :text="__('pro.not_pro.text')">
        <x-button :href="localized_route('contact')" icon="mail">{{ __('pro.not_pro.contact') }}</x-button>
        <x-button variant="soft" :href="localized_route('account')">{{ __('pro.not_pro.back') }}</x-button>
    </x-message-page>
</x-layouts.app>
