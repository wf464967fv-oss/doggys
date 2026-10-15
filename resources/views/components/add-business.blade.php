{{--
    "Your business is missing? Add it" banner shown under directory listings.
    <x-add-business class="mt-8" />
--}}
<div {{ $attributes->class(['flex flex-col items-start gap-4 rounded-3xl bg-orange-soft p-5 ring-1 ring-orange/30 sm:flex-row sm:items-center sm:p-6']) }}>
    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-orange text-brown-dark"><x-icon name="building" class="size-6" /></span>
    <p class="flex-1 text-brown-dark">{{ __('directory.missing_business') }}</p>
    <x-button variant="secondary" size="sm" :href="localized_route('listing.create')" icon="plus">{{ __('directory.add_business') }}</x-button>
</div>
