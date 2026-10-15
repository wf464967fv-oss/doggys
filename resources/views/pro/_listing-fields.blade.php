{{--
    Editable listing fields shared by the professional edit form, "add your business" and the admin form.
    Expects $provider (may be a new, unsaved model) and $categories (all categories, sorted).
--}}
@php
    $selectedCategories = old('categories', $provider->exists ? $provider->categories->pluck('slug')->all() : []);
    $categoryError = $errors->first('categories') ?: $errors->first('categories.*');
    $groups = $categories->groupBy(fn ($category) => $category->group->value);
@endphp

<x-textarea name="description" :label="__('pro.fields.description')" :hint="__('pro.hints.description')" :value="$provider->description" rows="4" maxlength="500" optional />

<div class="grid gap-5 sm:grid-cols-2">
    <x-input name="phone" type="tel" :label="__('pro.fields.phone')" :hint="__('pro.hints.phone')" :value="$provider->phoneForDisplay()" autocomplete="tel" maxlength="32" optional />
    <x-input name="email" type="email" :label="__('pro.fields.email')" :value="$provider->email" autocomplete="email" maxlength="255" optional />
</div>
<x-input name="website" type="url" :label="__('pro.fields.website')" :hint="__('pro.hints.website')" :value="$provider->website" autocomplete="url" maxlength="255" placeholder="https://" optional />
<x-input name="opening_hours" :label="__('pro.fields.opening_hours')" :hint="__('pro.hints.opening_hours')" :value="$provider->opening_hours" maxlength="255" optional />

<div class="space-y-3 rounded-2xl bg-cream-light p-4 ring-1 ring-line">
    <x-checkbox name="is_mobile" :checked="$provider->is_mobile" :hint="__('pro.hints.is_mobile')">{{ __('pro.fields.is_mobile') }}</x-checkbox>
    <x-input name="service_radius_km" type="number" :label="__('pro.fields.service_radius_km')" :hint="__('pro.hints.service_radius_km')"
        :value="$provider->service_radius_km" min="1" max="100" inputmode="numeric" class="max-w-xs" />
</div>

<fieldset @if ($categoryError) aria-describedby="field-categories-error" @endif>
    <legend class="text-sm font-bold text-brown">{{ __('pro.fields.categories') }}</legend>
    <p class="mt-1 text-sm text-muted">{{ __('pro.hints.categories') }}</p>
    <div class="mt-3 space-y-4">
        @foreach ($groups as $group => $groupCategories)
            <div>
                <p class="text-xs font-extrabold tracking-wider text-orange-dark uppercase">{{ __('categories.groups.'.$group) }}</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($groupCategories as $category)
                        <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl bg-white px-4 py-2 ring-2 ring-line transition has-checked:bg-orange-soft has-checked:ring-orange has-focus-visible:outline-3 has-focus-visible:outline-brown">
                            <input type="checkbox" name="categories[]" value="{{ $category->slug }}" @checked(in_array($category->slug, $selectedCategories, true)) class="size-5 shrink-0 accent-orange-dark">
                            <x-icon :name="$category->icon" class="size-5 shrink-0 text-orange-dark" />
                            <span class="font-semibold text-brown-dark">{{ $category->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @if ($categoryError)
        <p id="field-categories-error" class="mt-2 flex items-start gap-1.5 text-sm font-semibold text-rose-dark"><x-icon name="alert" class="mt-0.5 size-4" />{{ $categoryError }}</p>
    @endif
</fieldset>
