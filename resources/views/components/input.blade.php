{{--
    Labelled text input with hint and validation error (old() input is restored automatically).
    <x-input name="email" type="email" :label="__('auth.email')" autocomplete="email" required />
    Props: name, label, type, value, hint, optional (adds "(optional)"), icon (leading), bag (error bag).
    Other attributes (required, autocomplete, min, max, placeholder…) go to the <input>.
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'icon' => null,
    'bag' => 'default',
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id') ?? 'field-'.str_replace('.', '-', $key);
    $error = $errors->getBag($bag)->first($key);
    $current = $type === 'password' ? null : old($key, $value);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-bold text-brown">
        {{ $label }}
        @if ($optional)<span class="font-semibold text-muted">({{ __('ui.optional') }})</span>@endif
    </label>
    <div class="relative">
        @if ($icon)
            <x-icon :name="$icon" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted" />
        @endif
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if ($current !== null) value="{{ $current }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->except(['class', 'id'])->class([
                'field min-h-12 px-4 py-2.5',
                'pl-11' => $icon,
                'field-error' => $error,
            ]) }}>
    </div>
    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-semibold text-rose-dark">
            <x-icon name="alert" class="mt-0.5 size-4" />{{ $error }}
        </p>
    @endif
</div>
