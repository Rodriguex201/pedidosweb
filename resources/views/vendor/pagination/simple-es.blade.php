@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginacion" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex cursor-default items-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                Anterior
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-secondary">
                Anterior
            </a>
        @endif

        <span class="text-sm font-medium text-slate-500">
            Pagina {{ $paginator->currentPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-secondary">
                Siguiente
            </a>
        @else
            <span class="inline-flex cursor-default items-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                Siguiente
            </span>
        @endif
    </nav>
@endif
