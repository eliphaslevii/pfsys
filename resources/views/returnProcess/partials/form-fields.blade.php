<div class="row g-4">
    {{-- ================= FORMULÁRIO PRINCIPAL (IDs Intactos) ================= --}}

    <div class="col-md-5">
        <label for="motivo_{{ $context }}" class="form-label fw-semibold text-secondary">
            <i class="ti ti-alert-circle me-1 text-muted"></i> Motivo
        </label>
        <select id="motivo_{{ $context }}" class="form-select" required>
            <option value="">Selecione um motivo</option>
            @foreach ($motivos as $m)
            <option value="{{ $m->name }}">{{ $m->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-5">
        <label for="codigo_erro_{{ $context }}" class="form-label fw-semibold text-secondary">
            <i class="ti ti-bug me-1 text-muted"></i> Código de erro
        </label>
        <select id="codigo_erro_{{ $context }}" class="form-select" required>
            <option value="">Selecione um código</option>
            <option value="Preço errado">Preço errado</option>
            <option value="Produto errado">Produto errado</option>
            <option value="Faturamento sem autorização">Faturamento sem autorização</option>
            <option value="Duplicidade">Duplicidade</option>
        </select>
    </div>

    <div class="col-md-2 d-flex align-items-end pb-1">
        <label class="form-check form-switch cursor-pointer">
            <input class="form-check-input" type="checkbox" id="movimentacaoMercadoria_{{ $context }}">
            <span class="form-check-label fw-semibold text-dark ms-2">Movimentação Física?</span>
        </label>
    </div>

    <div class="col-md-6">
        <label for="xmlFileInput_{{ $context }}" class="form-label fw-semibold text-secondary">
            <i class="ti ti-file-code me-1 text-muted"></i> Carregar XML
        </label>
        <input type="file" id="xmlFileInput_{{ $context }}" class="form-control" accept=".xml">
    </div>

    <div class="col-md-6">
        <label for="gestorSolicitante_{{ $context }}" class="form-label fw-semibold text-secondary">
            <i class="ti ti-user me-1 text-muted"></i> Responsável
        </label>
        <select id="gestorSolicitante_{{ $context }}" class="form-select" required>
            <option value="">Selecione o responsável</option>
            @foreach ($solicitantes as $user)
            <option value="{{ $user->name }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label for="observacoes_{{ $context }}" class="form-label fw-semibold text-secondary">
            <i class="ti ti-notes me-1 text-muted"></i> Observações
        </label>
        <textarea id="observacoes_{{ $context }}" class="form-control" rows="2" required
            style="resize: none;" placeholder="Insira as observações aqui..."></textarea>
    </div>
</div>

{{-- ================= ÁREA DE DETALHES (COLLAPSE) ================= --}}

<div class="mt-4 pt-2 border-top">
    
    {{-- Botão Gatilho --}}
    <button type="button" 
            class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center py-2" 
            data-bs-toggle="collapse" 
            data-bs-target="#xmlDetailsArea_{{ $context }}" 
            aria-expanded="false">
        <span class="fw-bold"><i class="ti ti-list-details me-2"></i> Visualizar Dados da Nota e Itens</span>
        <i class="ti ti-chevron-down"></i>
    </button>

    {{-- Container que abre/fecha --}}
    <div class="collapse mt-3" id="xmlDetailsArea_{{ $context }}">
        <div class="card card-body bg-light border-0">
            
            {{-- 1. Informações do Cabeçalho --}}
            <div id="client-info-box_{{ $context }}" 
                 class="p-3 rounded-3 border-start border-4 border-primary bg-white shadow-sm mb-3">
                
                <h6 class="fw-bold text-primary mb-3">
                    <i class="ti ti-file-description me-1"></i> Informações do XML
                </h6>
                
                <div class="row small text-secondary">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Cliente:</strong> <span id="client-name-display_{{ $context }}" class="text-dark">N/A</span></p>
                        <p class="mb-1"><strong>CNPJ:</strong> <span id="client-cnpj-display_{{ $context }}" class="text-dark">N/A</span></p>
                        <p class="mb-1"><strong>NF de Saída:</strong> <span id="nf-saida-display_{{ $context }}" class="text-dark">N/A</span></p>
                        <p class="mb-0"><strong>Info. Compl.:</strong> <span id="inf-cpl-display_{{ $context }}" class="text-dark">N/A</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Protocolo (nProt):</strong> <span id="nprot-display_{{ $context }}" class="text-dark">N/A</span></p>
                        <p class="mb-1"><strong>NF Devolução (NFD):</strong> <span id="inf-nfd-display_{{ $context }}" class="text-dark">N/A</span></p>
                        <p class="mb-0"><strong>NF Original (NFO):</strong> <span id="inf-nfo-display_{{ $context }}" class="text-dark">N/A</span></p>
                    </div>
                </div>
            </div>

            {{-- 2. Tabela de Itens --}}
            {{-- CORREÇÃO AQUI: style="display: block !important" para sobrescrever seu CSS antigo --}}
            <div class="table-responsive items-container shadow-sm rounded-3 border bg-white" style="display: block !important;">
                <table class="table table-vcenter table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th>Artigo</th>
                            <th>Descrição</th>
                            <th>NCM</th>
                            <th>NF Saída</th>
                            <th>NF Dev.</th>
                            <th>Qtd.</th>
                            <th>Preço Unit.</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body_{{ $context }}">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="ti ti-package-off fs-2 d-block mb-1"></i>
                                Aguardando carregamento do arquivo...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>