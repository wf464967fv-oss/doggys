{{-- "What happens next" card of the claim and "add your business" forms. --}}
<x-card>
    <h2 class="font-display text-xl font-semibold text-brown">{{ __('pro.steps.title') }}</h2>
    <ol class="mt-4 space-y-4" role="list">
        @foreach (__('pro.steps.items') as $index => $step)
            <li class="flex gap-3">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-orange font-display font-semibold text-brown-dark" aria-hidden="true">{{ $index + 1 }}</span>
                <span class="pt-1 text-sm leading-relaxed text-brown-dark">{{ $step }}</span>
            </li>
        @endforeach
    </ol>
    <p class="mt-5 flex gap-2 border-t border-line pt-4 text-xs leading-relaxed text-muted">
        <x-icon name="lock" class="size-4 shrink-0" />{{ __('pro.steps.privacy') }}
    </p>
</x-card>
