@extends('layouts.app')

@section('content')

@vite('resources/js/comercial/process-table.js')
@vite('resources/js/comercial/workflow-fields.js')
<script>
    window.userSector = "{{ auth()->user()->sector->name }}";
</script>

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
                {{-- =========================================================
                    | CLIENTE
                    ========================================================= --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2 ">
                        <i class="ti ti-user text-primary"></i>
                        <h6 class="fw-bold text-primary mb-0">Cliente</h6>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 ">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Nome</small>
                                <div id="det-cliente-nome" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">CNPJ</small>
                                <div id="det-cliente-cnpj" class="fw-semibold">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                    | FISCAL / XML
                    ========================================================= --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-file-description text-primary"></i>
                        <h6 class="fw-bold text-primary mb-0">Fiscal</h6>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">NFD</small>
                                <div id="det-nfd" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">NF Saída</small>
                                <div id="det-nf-saida" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">NF Devolução</small>
                                <div id="det-nf-devolucao" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">NF Original (NFO)</small>
                                <div id="det-nfo" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Protocolo</small>
                                <div id="det-nprot" class="fw-semibold">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                    | WORKFLOW / PROCESSO
                    ========================================================= --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-route text-warning"></i>
                        <h6 class="fw-bold text-primary mb-0">Processo</h6>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Motivo</small>
                                <div id="det-motivo" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Código do Erro</small>
                                <div id="det-codigo-erro" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Status</small>
                                <div id="det-status" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Etapa Atual</small>
                                <div id="det-etapa" class="fw-semibold">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- =========================================================
                | DADOS DO PROCESSO (PREENCHIDOS NO FLUXO)
                ========================================================= --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-forms text-success"></i>
                        <h6 class="fw-bold text-primary mb-0">Dados do Processo</h6>
                    </div>

                    <div class="row g-2">

                        {{-- COMERCIAL --}}
                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Delivery</small>
                                <div id="wf-delivery-display" class="fw-semibold">—</div>
                            </div>
                        </div>

                        {{-- FISCAL --}}
                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Doc. Faturamento</small>
                                <div id="wf-doc-fat-display" class="fw-semibold">—</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">Ordem de Entrada</small>
                                <div id="wf-ordem-display" class="fw-semibold">—</div>
                            </div>
                        </div>

                        {{-- LOGÍSTICA --}}
                        <div class="col-md-4">
                            <div class="border rounded-2 p-2">
                                <small class="text-muted">MIGO</small>
                                <div id="wf-migo-display" class="fw-semibold">—</div>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- =========================================================
                    | ITENS DA NOTA
                    ========================================================= --}}
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-package text-primary"></i>
                        <h6 class="fw-bold text-primary mb-0">Itens da Nota</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light small text-secondary">
                                <tr>
                                    <th>Artigo</th>
                                    <th>Descrição</th>
                                    <th>NCM</th>
                                    <th>Qtd.</th>
                                    <th>Preço</th>
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
{{-- ===========================
➡️ MODAL — AVANÇAR ETAPA
=========================== --}}
<div class="modal fade" id="modal-advance" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="ti ti-arrow-right me-1"></i> Avançar Etapa
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- campos dinâmicos --}}
                <div id="fieldsContainer"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button class="btn btn-warning"
                    id="btnConfirmAdvance">
                    Avançar
                </button>
            </div>

        </div>
    </div>
</div>
{{-- ===========================
➡️ MODAL — REJEITAR PROCESSO
=========================== --}}
<div class="modal fade" id="modal-reject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Recusar Processo</h5>
                <button class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Motivo da recusa</label>
                <textarea id="rejectComment"
                    class="form-control"
                    rows="4"
                    required></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                    data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger"
                    id="btnConfirmReject">Recusar</button>
            </div>

        </div>
    </div>
</div>

@endsection