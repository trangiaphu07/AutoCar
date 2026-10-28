@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Phân trang">
        @if ($paginator->onFirstPage())
            <span class="disabled" aria-disabled="true">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="disabled">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">›</a>
        @else
            <span class="disabled" aria-disabled="true">›</span>
        @endif
    </nav>
@endif
