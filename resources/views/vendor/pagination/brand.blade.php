@if ($paginator->hasPages())
    @php
        $btn = 'inline-flex h-10 min-w-10 items-center justify-center gap-1.5 rounded px-3 text-sm font-semibold ring-1 transition';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="text-sm text-brand-graphite">
            Showing <span class="font-semibold text-brand-charcoal">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold text-brand-charcoal">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-brand-charcoal">{{ $paginator->total() }}</span> results
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="{{ $btn }} cursor-not-allowed bg-brand-offwhite text-gray-400 ring-gray-200" aria-disabled="true"><x-icon name="chevron-left" class="h-4 w-4" /><span class="hidden sm:inline">Previous</span></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $btn }} bg-brand-white text-brand-charcoal ring-gray-300 hover:text-brand-orange hover:ring-brand-orange" aria-label="Previous page"><x-icon name="chevron-left" class="h-4 w-4" /><span class="hidden sm:inline">Previous</span></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden px-1 text-brand-graphite sm:inline" aria-hidden="true">&hellip;</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $btn }} bg-brand-orange text-brand-white ring-brand-orange" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $btn }} hidden bg-brand-white text-brand-charcoal ring-gray-300 hover:text-brand-orange hover:ring-brand-orange sm:inline-flex" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $btn }} bg-brand-white text-brand-charcoal ring-gray-300 hover:text-brand-orange hover:ring-brand-orange" aria-label="Next page"><span class="hidden sm:inline">Next</span><x-icon name="chevron-right" class="h-4 w-4" /></a>
            @else
                <span class="{{ $btn }} cursor-not-allowed bg-brand-offwhite text-gray-400 ring-gray-200" aria-disabled="true"><span class="hidden sm:inline">Next</span><x-icon name="chevron-right" class="h-4 w-4" /></span>
            @endif
        </div>
    </nav>
@endif
