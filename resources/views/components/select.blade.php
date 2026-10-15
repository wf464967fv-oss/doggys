{{--
    Labelled native <select> with validation error.
    <x-select name="sex" :label="__('dogs.sex')" :options="['male' => 'Mâle', 'female' => 'Femelle']" :value="$dog->sex?->value" :placeholder="__('ui.choose')" />
    Options: [value => label] or [group label => [value => label]] for <optgroup>.
    Props: name, label, options, value, placeholder, hint, optional, bag.
--}}
@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'optional' => false,
    'bag' => 'default',
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id') ?? 'field-'.str_replace('.', '-', $key);
    $error = $errors->getBag($bag)->first($key);
    $current = (string) old($key, $value);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-bold text-brown">
        {{ $label }}
        @if ($optional)<span class="font-semibold text-muted">({{ __('ui.optional') }})</span>@endif
    </label>
    <div class="relative">
        <select id="{{ $id }}" name="{{ $name }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->except(['class', 'id'])->class([
                'block min-h-12 w-full appearance-none rounded-2xl border-2 bg-white py-2.5 pr-11 pl-4 text-base text-brown-dark transition focus:outline-none focus:ring-4',
                'border-line focus:border-orange focus:ring-orange/25' => ! $error,
                'border-rose-dark focus:border-rose-dark focus:ring-rose/20' => $error,
            ]) }}>
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                @if (is_array($optionLabel))
                    <optgroup label="{{ $optionValue }}">
                        @foreach ($optionLabel as $groupValue => $groupLabel)
                            <option value="{{ $groupValue }}" @selected($current === (string) $groupValue)>{{ $groupLabel }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
                @endif
            @endforeach
        </select>
        <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-brown" />
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
