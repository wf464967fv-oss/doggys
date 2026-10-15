{{--
    Doggys pagination (overrides Laravel's default Tailwind view): 44 px targets, brand colours,
    and page 1 linked without "?page=1" (same URL as the canonical page).
--}}
@php
    $pageUrl = fn (int $page) => $page === 1
        ? rtrim(preg_replace('/([?&])page=1(&|$)/', '$1', $paginator->url(1)), '?&')
        : $paginator->url($page);
    $linkClass = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-full px-3 font-bold text-brown ring-1 ring-line bg-white transition hover:bg-cream hover:ring-brown-mid';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
        <p class="text-sm text-muted">
            {{ __('Showing') }} <span class="font-bold text-brown-dark">{{ $paginator->firstItem() }}</span>
            {{ __('to') }} <span class="font-bold text-brown-dark">{{ $paginator->lastItem() }}</span>
            {{ __('of') }} <span class="font-bold text-brown-dark">{{ $paginator->total() }}</span> {{ __('results') }}
        </p>

        <ul class="flex flex-wrap items-center justify-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $linkClass }} cursor-not-allowed opacity-40" aria-disabled="true"><x-icon name="chevron-right" class="size-5 rotate-180" /><span class="sr-only">{{ __('pagination.previous') }}</span></span>
                @else
                    <a href="{{ $pageUrl($paginator->currentPage() - 1) }}" rel="prev" class="{{ $linkClass }}"><x-icon name="chevron-right" class="size-5 rotate-180" /><span class="sr-only">{{ __('pagination.previous') }}</span></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="inline-flex min-h-11 items-center px-1 text-muted" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full bg-brown px-3 font-bold text-cream-light">{{ $page }}</span>
                            @else
                                <a href="{{ $pageUrl((int) $page) }}" class="{{ $linkClass }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $pageUrl($paginator->currentPage() + 1) }}" rel="next" class="{{ $linkClass }}"><x-icon name="chevron-right" class="size-5" /><span class="sr-only">{{ __('pagination.next') }}</span></a>
                @else
                    <span class="{{ $linkClass }} cursor-not-allowed opacity-40" aria-disabled="true"><x-icon name="chevron-right" class="size-5" /><span class="sr-only">{{ __('pagination.next') }}</span></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
