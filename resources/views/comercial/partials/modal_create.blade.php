<!-- MODAL CRIAR PROCESSO (REFATORADO E FUNCIONAL) -->
<div class="modal fade" id="modalCreateProcesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- ================= HEADER ================= -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-file-plus me-1"></i> Criar Novo Processo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- ================= BODY ================= -->
            <div class="modal-body p-0">

                <!-- TABS -->
                <ul class="nav nav-tabs px-3 pt-3" id="returnProcessTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recusa-tab"
                                data-bs-toggle="tab" data-bs-target="#recusa" type="button" role="tab">
                            <i class="ti ti-ban me-1"></i> Recusa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="devolucao-tab"
                                data-bs-toggle="tab" data-bs-target="#devolucao" type="button" role="tab">
                            <i class="ti ti-refresh me-1"></i> Devolução
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="returnProcessTabContent">

                    <!-- ================= RECUSA ================= -->
                    <div class="tab-pane fade show active" id="recusa" role="tabpanel">
                        <form id="form_recusa" enctype="multipart/form-data">
                            <input type="hidden" name="tipo" value="recusa">

                            <div class="row g-4">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Motivo</label>
                                    <select id="motivo_recusa" name="motivo" class="form-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($motivos as $m)
                                            <option value="{{ $m->name }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Código de erro</label>
                                    <select id="codigo_erro_recusa" name="codigo_erro" class="form-select" required>
                                        <option value="">Selecione</option>
                                        <option value="Preço errado">Preço errado</option>
                                        <option value="Produto errado">Produto errado</option>
                                        <option value="Faturamento sem autorização">Faturamento sem autorização</option>
                                        <option value="Duplicidade">Duplicidade</option>
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="movimentacaoMercadoria_recusa" name="movimentacao">
                                        <span class="form-check-label">Movimentação?</span>
                                    </label>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">XML</label>
                                    <input type="file" id="xmlFileInput_recusa" name="xml" class="form-control" accept=".xml">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Responsável</label>
                                    <select id="gestorSolicitante_recusa" name="responsavel" class="form-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($solicitantes as $u)
                                            <option value="{{ $u->name }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Observações</label>
                                    <textarea id="observacoes_recusa" name="observacoes" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>

                            @include('comercial.partials.detalhes', ['context' => 'recusa'])
                        </form>
                    </div>

                    <!-- ================= DEVOLUÇÃO ================= -->
                    <div class="tab-pane fade" id="devolucao" role="tabpanel">
                        <form id="form_devolucao" enctype="multipart/form-data">
                            <input type="hidden" name="tipo" value="devolucao">

                            <div class="row g-4">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Motivo</label>
                                    <select id="motivo_devolucao" name="motivo" class="form-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($motivos as $m)
                                            <option value="{{ $m->name }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Código de erro</label>
                                    <select id="codigo_erro_devolucao" name="codigo_erro" class="form-select" required>
                                        <option value="">Selecione</option>
                                        <option value="Preço errado">Preço errado</option>
                                        <option value="Produto errado">Produto errado</option>
                                        <option value="Faturamento sem autorização">Faturamento sem autorização</option>
                                        <option value="Duplicidade">Duplicidade</option>
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="movimentacaoMercadoria_devolucao" name="movimentacao">
                                        <span class="form-check-label">Movimentação?</span>
                                    </label>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">XML</label>
                                    <input type="file" id="xmlFileInput_devolucao" name="xml" class="form-control" accept=".xml">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Responsável</label>
                                    <select id="gestorSolicitante_devolucao" name="responsavel" class="form-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($solicitantes as $u)
                                            <option value="{{ $u->name }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Observações</label>
                                    <textarea id="observacoes_devolucao" name="observacoes" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>

                            @include('comercial.partials.detalhes', ['context' => 'devolucao'])
                        </form>
                    </div>
                </div>
            </div>

            <!-- ================= FOOTER ================= -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button class="btn btn-primary" id="sendApprovRequest">Salvar Processo</button>
            </div>

        </div>
    </div>
</div>
@push('scripts')
<script>
    console.log('XML DETAILS SCRIPT CARREGADO');
    window.openXmlDetails = function (context) {

        // 1️⃣ ativa a tab correta
        const tabButton = document.querySelector(
            `[data-bs-target="#${context}"]`
        );
        
        if (tabButton) {
            bootstrap.Tab.getOrCreateInstance(tabButton).show();
        }

        // 2️⃣ aguarda a tab ficar visível e abre o collapse
        setTimeout(() => {
            const area = document.getElementById(`xmlDetailsArea_${context}`);
            if (!area) {
                console.warn('Collapse não encontrado:', context);
                return;
            }

            const collapse = bootstrap.Collapse.getOrCreateInstance(area);
            collapse.show();
        }, 100);
    };
</script>

@endpush
