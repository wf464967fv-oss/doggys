{{--
    Accessible 1–5 star rating input: native radio group (arrow keys move the choice), styled stars,
    no JavaScript (stars up to the checked one are filled with :has()).
    <x-star-input name="rating" :value="$review->rating" bag="review" />
    Props: name, value, label, hint, bag (error bag).
--}}
@props(['name' => 'rating', 'value' => null, 'label' => null, 'hint' => null, 'bag' => 'default'])

@php
    $error = $errors->getBag($bag)->first($name);
    $current = (int) old($name, $value);
    $id = 'field-'.$name;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $filled = '[&:has(input:checked)_svg]:fill-orange [&:has(input:checked)_svg]:stroke-orange-dark [&:has(~label_input:checked)_svg]:fill-orange [&:has(~label_input:checked)_svg]:stroke-orange-dark';
@endphp

<fieldset {{ $attributes->class(['space-y-1.5']) }} @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
    <legend class="text-sm font-bold text-brown">{{ $label ?? __('reviews.form.rating') }}</legend>
    <div class="flex flex-wrap items-center gap-1">
        @for ($star = 1; $star <= 5; $star++)
            <label class="flex size-12 cursor-pointer items-center justify-center rounded-2xl transition hover:bg-orange-soft has-focus-visible:outline-3 has-focus-visible:outline-offset-1 has-focus-visible:outline-brown {{ $filled }}">
                <input type="radio" name="{{ $name }}" value="{{ $star }}" class="sr-only" @checked($current === $star) required
                    @if ($error) aria-invalid="true" @endif>
                <x-icon name="star" class="size-9 fill-none stroke-line-strong transition" />
                <span class="sr-only">{{ trans_choice('reviews.form.stars', $star, ['count' => $star]) }}</span>
            </label>
        @endfor
    </div>
    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-semibold text-rose-dark">
            <x-icon name="alert" class="mt-0.5 size-4" />{{ $error }}
        </p>
    @endif
</fieldset>
