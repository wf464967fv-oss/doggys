{{-- Server errors: "bare" layout, no session or database access. --}}
@php
    $code = $exception->getStatusCode();
    $key = $code === 503 ? 503 : 500;
@endphp

<x-layouts.app :title="__('errors.'.$key.'_title')" :description="__('errors.'.$key.'_text')" noindex bare>
    <x-message-page :code="$code" icon="heart" :title="__('errors.'.$key.'_title')" :text="__('errors.'.$key.'_text')">
        <x-button :href="localized_route('home')" icon="home">{{ __('ui.back_home') }}</x-button>
    </x-message-page>
</x-layouts.app>
