@php
    $editing = $dog->exists;
    $title = $editing ? __('dogs.edit_title', ['name' => $dog->name]) : __('dogs.create_title');
    $vaccinations = old('vaccinations', $dog->vaccinations ?? []);
    $vaccinationErrors = collect($errors->get('vaccinations'))->merge($errors->get('vaccinations.*'))->flatten()->unique();
@endphp

<x-layouts.app :title="$title" :description="__('dogs.form_meta')" noindex>
    <x-account-header :title="$title" :subtitle="__('dogs.form_subtitle')" />

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-10">
        <a href="{{ localized_route('account') }}" class="link inline-flex min-h-11 items-center gap-1.5">
            <x-icon name="arrow-right" class="size-4 rotate-180" />{{ __('dogs.back') }}
        </a>

        @if ($errors->any())
            <x-alert variant="error" class="mt-4" :title="__('ui.form_has_errors')" />
        @endif

        <form method="POST" action="{{ $editing ? localized_route('dogs.update', $dog) : localized_route('dogs.store') }}" class="mt-4 space-y-6" novalidate>
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-card as="section" class="space-y-5" aria-labelledby="section-identity">
                <h2 id="section-identity" class="flex items-center gap-2 font-display text-xl font-semibold text-brown">
                    <x-icon name="paw" class="text-orange-dark" />{{ __('dogs.section_identity') }}
                </h2>
                <x-input name="name" :label="__('dogs.name')" :value="$dog->name" required maxlength="80" autocomplete="off" />
                <x-input name="breed" :label="__('dogs.breed')" :value="$dog->breed" :hint="__('dogs.breed_hint')" maxlength="120" optional />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="sex" :label="__('dogs.sex')" :value="$dog->sex?->value" :placeholder="__('ui.choose')" optional
                        :options="collect($sexes)->mapWithKeys(fn ($sex) => [$sex->value => __('dogs.sex_'.$sex->value)])->all()" />
                    <x-input name="birth_date" type="date" :label="__('dogs.birth_date')" :value="$dog->birth_date?->toDateString()" :max="now()->toDateString()" optional />
                </div>
                <x-input name="weight_kg" type="number" step="0.1" min="0.2" max="150" inputmode="decimal" :label="__('dogs.weight_kg')" :value="$dog->weight_kg" class="sm:max-w-xs" optional />
            </x-card>

            <x-card as="section" class="space-y-5" aria-labelledby="section-identification">
                <h2 id="section-identification" class="flex items-center gap-2 font-display text-xl font-semibold text-brown">
                    <x-icon name="chip" class="text-orange-dark" />{{ __('dogs.section_identification') }}
                </h2>
                <x-input name="microchip_number" :label="__('dogs.microchip_number')" :value="$dog->microchip_number" :hint="__('dogs.microchip_hint')" inputmode="numeric" maxlength="32" autocomplete="off" optional />
                <x-checkbox name="is_neutered" :checked="$dog->is_neutered">{{ __('dogs.is_neutered') }}</x-checkbox>
            </x-card>

            <x-card as="section" class="space-y-5" aria-labelledby="section-health">
                <h2 id="section-health" class="flex items-center gap-2 font-display text-xl font-semibold text-brown">
                    <x-icon name="heart" class="text-orange-dark" />{{ __('dogs.section_health') }}
                </h2>

                <div x-data="vaccinationList" data-items="{{ json_encode(array_values($vaccinations)) }}">
                    <p class="text-sm font-bold text-brown" id="vaccinations-label">{{ __('dogs.vaccinations') }}</p>
                    <p class="text-sm text-muted">{{ __('dogs.vaccinations_hint') }}</p>

                    <ul class="mt-3 space-y-3" aria-labelledby="vaccinations-label">
                        <template x-for="(item, index) in items" x-bind:key="index">
                            <li class="grid grid-cols-[1fr_auto] gap-3 rounded-2xl bg-cream-light p-3 ring-1 ring-line sm:grid-cols-[1fr_11rem_auto] sm:items-end">
                                <div class="space-y-1">
                                    <label class="block text-sm font-bold text-brown" x-bind:for="fieldId(index, 'name')">{{ __('dogs.vaccine_name') }}</label>
                                    <input type="text" maxlength="120" data-vaccine-name x-bind:id="fieldId(index, 'name')" x-bind:name="fieldName(index, 'name')" x-model="item.name"
                                        placeholder="{{ __('dogs.vaccine_placeholder') }}"
                                        class="block min-h-12 w-full rounded-2xl border-2 border-line bg-white px-4 py-2.5 text-base placeholder:text-muted/70 focus:border-orange focus:ring-4 focus:ring-orange/25 focus:outline-none">
                                </div>
                                <div class="order-3 col-span-2 space-y-1 sm:order-none sm:col-span-1">
                                    <label class="block text-sm font-bold text-brown" x-bind:for="fieldId(index, 'date')">{{ __('dogs.vaccine_date') }}</label>
                                    <input type="date" max="{{ now()->toDateString() }}" x-bind:id="fieldId(index, 'date')" x-bind:name="fieldName(index, 'date')" x-model="item.date"
                                        class="block min-h-12 w-full rounded-2xl border-2 border-line bg-white px-4 py-2.5 text-base focus:border-orange focus:ring-4 focus:ring-orange/25 focus:outline-none">
                                </div>
                                <button type="button" x-on:click="remove(index)"
                                    class="inline-flex size-12 items-center justify-center self-end rounded-full text-rose-dark ring-2 ring-line transition hover:bg-rose-soft">
                                    <x-icon name="trash" />
                                    <span class="sr-only">{{ __('dogs.vaccine_remove') }}</span>
                                </button>
                            </li>
                        </template>
                    </ul>

                    <p x-show="items.length === 0" class="mt-3 rounded-2xl border-2 border-dashed border-line px-4 py-3 text-sm text-muted">{{ __('dogs.vaccinations_empty') }}</p>

                    @if ($vaccinationErrors->isNotEmpty())
                        <ul class="mt-3 space-y-1">
                            @foreach ($vaccinationErrors as $message)
                                <li class="flex items-start gap-1.5 text-sm font-semibold text-rose-dark"><x-icon name="alert" class="mt-0.5 size-4" />{{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <x-button type="button" variant="soft" size="sm" icon="plus" class="mt-3" x-on:click="add">{{ __('dogs.vaccine_add') }}</x-button>
                    <noscript><p class="mt-2 text-sm text-muted">{{ __('dogs.vaccinations_noscript') }}</p></noscript>
                </div>

                <x-textarea name="health_notes" :label="__('dogs.health_notes')" :value="$dog->health_notes" :hint="__('dogs.health_notes_hint')" rows="4" maxlength="2000" optional />
            </x-card>

            <x-alert variant="info">{{ __('dogs.privacy_note') }}</x-alert>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="ghost" :href="localized_route('account')">{{ __('ui.cancel') }}</x-button>
                <x-button icon="check" size="lg">{{ $editing ? __('ui.save') : __('dogs.create_submit') }}</x-button>
            </div>
        </form>

        @if ($editing)
            <form method="POST" action="{{ localized_route('dogs.destroy', $dog) }}" class="mt-10 border-t border-line pt-6"
                x-data="confirmSubmit" data-confirm="{{ __('dogs.delete_confirm', ['name' => $dog->name]) }}" x-on:submit="submit">
                @csrf
                @method('DELETE')
                <x-button variant="ghost" size="sm" icon="trash" class="text-rose-dark hover:bg-rose-soft">{{ __('dogs.delete', ['name' => $dog->name]) }}</x-button>
            </form>
        @endif
    </div>
</x-layouts.app>
