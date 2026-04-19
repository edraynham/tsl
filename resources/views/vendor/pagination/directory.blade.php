@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex size-10 items-center justify-center rounded-lg text-tsl-outline opacity-50" aria-disabled="true">
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="inline-flex size-10 items-center justify-center rounded-lg text-tsl-secondary transition-colors hover:bg-tsl-surface-container"
                aria-label="{{ __('pagination.previous') }}"
            >
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-tsl-outline-variant">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                            aria-current="page"
                            class="inline-flex size-10 items-center justify-center rounded-lg bg-tsl-primary text-sm font-bold text-tsl-on-primary"
                        >{{ $page }}</span>
                    @else
                        <a
                            href="{{ $url }}"
                            class="inline-flex size-10 items-center justify-center rounded-lg text-sm font-medium text-tsl-secondary transition-colors hover:bg-tsl-surface-container"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        >{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="inline-flex size-10 items-center justify-center rounded-lg text-tsl-secondary transition-colors hover:bg-tsl-surface-container"
                aria-label="{{ __('pagination.next') }}"
            >
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <span class="inline-flex size-10 items-center justify-center rounded-lg text-tsl-outline opacity-50" aria-disabled="true">
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif
    </nav>
@endif
