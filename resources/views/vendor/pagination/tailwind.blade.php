@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600">
            Menampilkan
            @if ($paginator->firstItem())
                <span class="font-medium text-gray-900">{{ $paginator->firstItem() }}</span>
                &ndash;
                <span class="font-medium text-gray-900">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            dari
            <span class="font-medium text-gray-900">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                      class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-400">
                    &laquo;
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    &laquo;
                </a>
            @endif

            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true"
                              class="inline-flex h-9 min-w-9 shrink-0 items-center justify-center rounded-md px-2 text-sm text-gray-400">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex h-9 min-w-9 shrink-0 items-center justify-center rounded-md bg-dark px-2 text-sm font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="inline-flex h-9 min-w-9 shrink-0 items-center justify-center rounded-md border border-gray-300 px-2 text-sm text-gray-700 hover:bg-gray-50">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    &raquo;
                </a>
            @else
                <span aria-disabled="true"
                      class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-400">
                    &raquo;
                </span>
            @endif
        </div>
    </nav>
@endif
