@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary mb-0">
        <i class="ti ti-stairs-up me-2"></i>
        Steps do Fluxo — {{ $template->name }}
    </h3>

    <button class="btn btn-primary" id="btnCreateStep">
        <i class="ti ti-plus"></i> Nova Etapa
    </button>
</div>

{{-- TABLE WRAPPER --}}
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- Loader --}}
        <div id="stepsLoader" class="text-center py-5 d-none">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Carregando etapas...</p>
        </div>

        {{-- Table --}}
        <div id="stepsTableWrapper">
            <table class="table table-hover align-middle" id="stepsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Nome</th>
                        <th>Setor</th>
                        <th>Nível</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody id="stepsList">
                    {{-- preenchido via JS --}}
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- MODAL UNIVERSAL DE CRIAÇÃO / EDIÇÃO --}}

@endsection


@section('scripts')
<script src="{{ asset('js/workflows/steps.js') }}"></script>
@endsection
