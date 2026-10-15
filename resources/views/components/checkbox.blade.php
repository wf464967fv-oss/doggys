{{--
    Checkbox with label (the whole row is clickable, 44px min height). The label slot may contain links.
    <x-checkbox name="is_neutered" :checked="$dog->is_neutered">{{ __('dogs.is_neutered') }}</x-checkbox>
    Props: name, checked, value (default "1"), hint, bag.
--}}
@props(['name', 'checked' => false, 'value' => '1', 'hint' => null, 'bag' => 'default'])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id') ?? 'field-'.str_replace('.', '-', $key);
    $error = $errors->getBag($bag)->first($key);
    $isChecked = old() ? (bool) old($key) : (bool) $checked;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    <div class="flex min-h-11 items-start gap-3">
        <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="{{ $value }}" @checked($isChecked)
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->except(['class', 'id']) }}
            class="mt-0.5 size-6 shrink-0 cursor-pointer rounded-lg border-2 border-line-strong accent-orange-dark focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-brown">
        <label for="{{ $id }}" class="cursor-pointer pt-0.5 text-base text-brown-dark">{{ $slot }}</label>
    </div>
    @if ($hint)
        <p id="{{ $id }}-hint" class="pl-9 text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 pl-9 text-sm font-semibold text-rose-dark">
            <x-icon name="alert" class="mt-0.5 size-4" />{{ $error }}
        </p>
    @endif
</div>
