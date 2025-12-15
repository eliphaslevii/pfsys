@extends('layouts.app')

@section('content')

<div class="page-header d-print-none mb-2">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Monitoramento</h2>
            </div>
            
            {{-- BUSCA --}}
            <div class="col-auto ms-auto">
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <div class="spinner-border spinner-border-sm text-secondary d-none" id="loading-spinner"></div>
                        <i class="ti ti-search" id="search-icon"></i>
                    </span>
                    <input 
                        type="text" 
                        id="search-input"
                        value="{{ request('search') }}" 
                        class="form-control form-control-sm" 
                        placeholder="Buscar..." 
                        style="width: 200px;"
                        autocomplete="off"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body mt-0">
    <div class="container-xl">
        <div class="card">

            <div id="render-target">
                <div class="table-responsive">
                    
                    <table class="table table-vcenter table-sm table-nowrap card-table table-hover">
                        <thead>
                            <tr>
                                <th class="w-1">NF / Série</th>
                                <th>Destinatário</th>
                                <th>Transportadora</th>
                                <th>Status</th>
                                <th>Ocorrência</th>
                                <th class="w-1 text-end">Data</th>
                            </tr>
                        </thead>
                        <tbody>

                            @forelse($nfes as $nf)
                                <tr>
                                    {{-- NF --}}
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-primary me-2">{{ $nf->numero }}</span>
                                            <span class="text-muted small">S:{{ $nf->serie }}</span>
                                        </div>
                                    </td>

                                    {{-- Destinatário --}}
                                    <td class="py-2">
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $nf->dest_nome }}">
                                            {{ $nf->dest_nome }}
                                        </div>
                                    </td>

                                    {{-- Transportadora --}}
                                    <td class="py-2 text-secondary small">
                                        <div class="text-truncate" style="max-width: 200px;">
                                            {{ $nf->transportadora_nome ?: '---' }}
                                        </div>
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="py-2">
                                        <span class="{{ $nf->ui_class }}">
                                            <i class="{{ $nf->ui_icon }} icon me-1"></i>
                                            {{ $nf->ui_text }}
                                        </span>
                                    </td>

                                    {{-- Ocorrência --}}
                                    <td class="py-2 text-secondary small">
                                        <div class="text-truncate" style="max-width: 220px;" title="{{ $nf->ui_message }}">
                                            {{ Str::limit($nf->ui_message, 45) }}
                                        </div>
                                    </td>

                                    {{-- Data --}}
                                    <td class="py-2 text-end small text-muted">
                                        @if($nf->ui_date)
                                            {{ \Carbon\Carbon::parse($nf->ui_date)->format('d/m H:i') }}
                                        @else
                                            ---
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        Nenhum registro encontrado.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="card-footer d-flex align-items-center py-2">
                    <p class="m-0 text-secondary small">
                        Total: <b>{{ $nfes->total() }}</b>
                    </p>
                    <div class="ms-auto pagination-sm">
                        {{ $nfes->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const targetDiv = document.getElementById('render-target');
    const spinner = document.getElementById('loading-spinner');
    const searchIcon = document.getElementById('search-icon');
    let debounceTimer;

    function updateTable(url) {
        spinner.classList.remove('d-none');
        searchIcon.classList.add('d-none');
        targetDiv.style.opacity = '0.6';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            targetDiv.innerHTML = doc.getElementById('render-target').innerHTML;
            window.history.pushState({}, '', url);
        })
        .catch(err => console.error(err))
        .finally(() => {
            spinner.classList.add('d-none');
            searchIcon.classList.remove('d-none');
            targetDiv.style.opacity = '1';
        });
    }

    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const query = e.target.value;
        debounceTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', query);
            url.searchParams.set('page', 1);
            updateTable(url.toString());
        }, 500);
    });

    targetDiv.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateTable(link.href);
        }
    });
});
</script>
@endpush

@endsection
