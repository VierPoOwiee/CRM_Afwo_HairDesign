@php
    $paginator = $paginator ?? null;
@endphp

@if ($paginator && $paginator->hasPages())
    <nav class="pagination-wrap" aria-label="Navigasi Halaman">
        @if ($paginator->onFirstPage())
            <span class="pagination-btn is-disabled">&laquo;</span>
        @else
            <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" aria-label="Halaman Sebelumnya">&laquo;</a>
        @endif

        @php
            $window = 2;
            $totalPages = $paginator->lastPage();
            $current = $paginator->currentPage();
            $start = max(1, $current - $window);
            $end = min($totalPages, $current + $window);
        @endphp

        @if ($start > 1)
            <a class="pagination-btn" href="{{ $paginator->url(1) }}">1</a>
            @if ($start > 2)
                <span class="pagination-btn is-disabled">...</span>
            @endif
        @endif

        @for ($i = $start; $i <= $end; $i++)
            @if ($i === $current)
                <span class="pagination-btn is-active" aria-current="page">{{ $i }}</span>
            @else
                <a class="pagination-btn" href="{{ $paginator->url($i) }}">{{ $i }}</a>
            @endif
        @endfor

        @if ($end < $totalPages)
            @if ($end < $totalPages - 1)
                <span class="pagination-btn is-disabled">...</span>
            @endif
            <a class="pagination-btn" href="{{ $paginator->url($totalPages) }}">{{ $totalPages }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" aria-label="Halaman Berikutnya">&raquo;</a>
        @else
            <span class="pagination-btn is-disabled">&raquo;</span>
        @endif

        <span class="pagination-info">Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</span>
    </nav>
@endif