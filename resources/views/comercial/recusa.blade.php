@extends('layouts.app')

@section('content')

<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Nova Recusa de Mercadoria</h2>
                <div class="text-muted">
                    Registro de recusa no ato da entrega
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('return.process.index') }}"
                    class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <form id="form_create_recusa" enctype="multipart/form-data">

            @csrf

            <div class="card">

                <div class="card-body">

                    {{-- XML --}}
                    <div class="mb-3">
                        <label class="form-label required">XML da NF-e</label>
                        <input type="file"
                            id="xmlFileInput_recusa"
                            name="xml"
                            class="form-control"
                            accept=".xml"
                            required>

                    </div>
                    <input type="hidden" name="nfo" id="nfo_recusa">

                    {{-- RESPONSÁVEL --}}
                    <div class="mb-3">
                        <label class="form-label required">Responsável</label>
                        <select id="gestorSolicitante_recusa" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($solicitantes as $u)
                            <option value="{{ $u->name }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- MOTIVO --}}
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label required">Motivo</label>
                            <select id="motivo_recusa" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($motivos as $m)
                                <option value="{{ $m->name }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Código de erro</label>
                            <select id="codigo_erro_recusa" class="form-select" required>
                                <option value="">Selecione...</option>
                                <option value="Preço errado">Preço errado</option>
                                <option value="Produto errado">Produto errado</option>
                                <option value="Faturamento sem autorização">Faturamento s/ aut.</option>
                                <option value="Duplicidade">Duplicidade</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input
                                    id="movimentacao_recusa"
                                    name="movimentacao_mercadoria"
                                    class="form-check-input"
                                    type="checkbox"
                                    value="1">
                                <label class="form-check-label">Movimentação física</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">Observações</label>
                            <textarea id="observacoes_recusa"
                                class="form-control"
                                rows="3"
                                required></textarea>
                        </div>
                    </div>

                    {{-- DETALHES XML --}}
                    @include('comercial.partials.xml-details', ['context' => 'recusa'])

                </div>

                <div class="card-footer text-end">
                    <button type="button"
                        id="btnSalvarRecusa"
                        class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Salvar Recusa
                    </button>
                </div>

            </div>
        </form>


    </div>
</div>
@vite('resources/js/comercial/recusa-create.js')

@endsection