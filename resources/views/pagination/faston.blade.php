<nav class="pagination-nav" role="navigation" aria-label="Navigasi halaman">
    @php
        $lastPage = $paginator->lastPage();
        $currentPage = $paginator->currentPage();
        if ($lastPage <= 5) {
            $visiblePages = range(1, $lastPage);
        } elseif ($currentPage <= 3) {
            $visiblePages = [1, 2, 3, '…', $lastPage];
        } elseif ($currentPage >= $lastPage - 2) {
            $visiblePages = [1, '…', $lastPage - 2, $lastPage - 1, $lastPage];
        } else {
            $visiblePages = [1, '…', $currentPage - 1, $currentPage, $currentPage + 1, '…', $lastPage];
        }
    @endphp

    <p class="pagination-summary">
        Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
        dari <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong> data
    </p>

    @if ($paginator->hasPages())
        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7"/></svg>
                </span>
            @else
                <a class="pagination-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7"/></svg>
                </a>
            @endif

            @foreach ($visiblePages as $page)
                @if ($page === '…')
                    <span class="pagination-ellipsis" aria-hidden="true">…</span>
                @elseif ($page == $paginator->currentPage())
                    <span class="pagination-number is-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                @else
                    <a class="pagination-number" href="{{ $paginator->url($page) }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7"/></svg>
                </a>
            @else
                <span class="pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    @endif
</nav>
