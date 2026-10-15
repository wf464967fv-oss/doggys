{{-- Status filter of an admin list. Expects $route, $current and $options [value => label]. --}}
<nav aria-label="{{ __('admin.filter') }}" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
    @foreach ($options as $value => $label)
        <a href="{{ route($route, ['status' => $value]) }}" @if ($current === $value) aria-current="page" @endif
            @class([
                'inline-flex min-h-11 shrink-0 items-center rounded-full px-4 text-sm font-bold transition',
                'bg-brown text-cream-light' => $current === $value,
                'bg-white text-brown ring-2 ring-line hover:ring-brown-mid' => $current !== $value,
            ])>{{ $label }}</a>
    @endforeach
</nav>
