{{--
    Labelled <textarea> with hint and validation error.
    <x-textarea name="health_notes" :label="__('dogs.health_notes')" :value="$dog->health_notes" rows="4" maxlength="2000" optional />
    Props: name, label, value, hint, optional, bag.
--}}
@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'bag' => 'default',
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id') ?? 'field-'.str_replace('.', '-', $key);
    $error = $errors->getBag($bag)->first($key);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-bold text-brown">
        {{ $label }}
        @if ($optional)<span class="font-semibold text-muted">({{ __('ui.optional') }})</span>@endif
    </label>
    <textarea id="{{ $id }}" name="{{ $name }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($error) aria-invalid="true" @endif
        {{ $attributes->except(['class', 'id'])->merge(['rows' => 4])->class([
            'block w-full rounded-2xl border-2 bg-white px-4 py-3 text-base text-brown-dark placeholder:text-muted/70 transition focus:outline-none focus:ring-4',
            'border-line focus:border-orange focus:ring-orange/25' => ! $error,
            'border-rose-dark focus:border-rose-dark focus:ring-rose/20' => $error,
        ]) }}>{{ old($key, $value) }}</textarea>
    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-semibold text-rose-dark">
            <x-icon name="alert" class="mt-0.5 size-4" />{{ $error }}
        </p>
    @endif
</div>
