@extends('layouts.app')

@section('content')

@vite('resources/js/comercial/process-table.js')
{{-- ===========================
🧭 HEADER
=========================== --}}


<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Processos de Recusa/Devolução</h2>
                <div class="text-muted">
                    Controle de processos
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

{{-- ===========================
📋 TABELA DE PROCESSOS
=========================== --}}
<div class="card fiori-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="processTable"
                class="table table-striped table-hover align-middle w-100 mb-0">

                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>CNPJ</th>
                        <th>Motivo</th>
                        <th>Código do Erro</th>
                        <th>Etapa</th>
                        <th>Responsável</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody>

                </tbody>
            </table>
            <div id="pagination" class="d-flex justify-content-end mt-3"></div>

        </div>
    </div>
</div>

{{-- ===========================
🔍 MODAL — DETALHES DO PROCESSO
=========================== --}}
<div class="modal fade" id="modal-process-view" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">
                    Detalhes do Processo / Nota Fiscal
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- CLIENTE --}}
                <h6 class="fw-bold text-primary mb-2">Cliente</h6>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Nome:</strong>
                            <div id="det-cliente-nome"></div>
                        </div>

                        <div class="col-md-6">
                            <strong>CNPJ:</strong>
                            <div id="det-cliente-cnpj"></div>
                        </div>
                    </div>
                </div>

                {{-- FISCAL --}}
                <h6 class="fw-bold text-primary mb-2">Fiscal</h6>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div><strong>NFD:</strong> <span id="det-nfd"></span></div>
                        </div>

                        <div class="col-md-6">
                            <div><strong>NF Saída:</strong> <span id="det-nf-saida"></span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>NF Devolução:</strong> <span id="det-nf-devolucao"></span></div>
                        </div>

                        <div class="col-md-6">
                            <div><strong>NFO:</strong> <span id="det-nfo"></span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Protocolo:</strong> <span id="det-nprot"></span></div>
                        </div>
                    </div>
                </div>


                {{-- WORKFLOW --}}
                <h6 class="fw-bold text-primary mb-2">Fiscal</h6>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div><strong>Motivo:</strong> <span id="det-motivo"></span></div>
                        </div>

                        <div class="col-md-6">
                            <div><strong>Código do Erro:</strong> <span id="det-codigo-erro"></span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Status:</strong> <span id="det-status"></span></div>
                        </div>

                        <div class="col-md-6">
                            <div><strong>Etapa:</strong> <span id="det-etapa"></span></div>
                        </div>

                    </div>
                </div>


                {{-- ITENS --}}
                <h6 class="fw-bold text-primary mb-2">Itens da Nota</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th>Artigo</th>
                                <th>Descrição</th>
                                <th class="d-none d-md-table-cell">NCM</th>
                                <th class="text-end">Qtd.</th>
                                <th class="text-end">Preço</th>
                            </tr>
                        </thead>

                        <tbody id="det-itens-body">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="ti ti-package-off fs-4 d-block mb-1"></i>
                                    Carregando itens...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- ===========================
✅ MODAL — APROVAÇÃO
=========================== --}}
<div class="modal fade" id="modal-approve" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Confirmar Aprovação</h5>
                <button class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="approveFields"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success"
                    id="btnSubmitApprove">
                    Salvar & Avançar
                </button>
            </div>

        </div>
    </div>
</div>
{{-- ===========================
✅ MODAL — EXCLUSÃO
=========================== --}}
<div class="modal fade" id="modal-delete" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Tem certeza que deseja <strong>excluir este processo</strong>?
                    <br>
                    <small class="text-muted">
                        Essa ação não poderá ser desfeita.
                    </small>
                </p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-danger"
                    id="btnConfirmDelete">
                    Excluir
                </button>
            </div>

        </div>
    </div>
</div>

@endsection