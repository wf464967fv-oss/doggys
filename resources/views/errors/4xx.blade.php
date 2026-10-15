@php
    $code = $exception->getStatusCode();
    $key = in_array($code, [403, 404, 419, 429], true) ? $code : 'generic';
@endphp

<x-layouts.app :title="__('errors.'.$key.'_title')" :description="__('errors.'.$key.'_text')" noindex>
    <x-message-page :code="$code" :icon="$code === 404 ? 'search' : 'alert'" :title="__('errors.'.$key.'_title')" :text="__('errors.'.$key.'_text')">
        <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
        @if ($code === 404)
            <x-button variant="soft" :href="localized_route('search')" icon="search">{{ __('ui.nav_find') }}</x-button>
        @endif
    </x-message-page>
</x-layouts.app>
