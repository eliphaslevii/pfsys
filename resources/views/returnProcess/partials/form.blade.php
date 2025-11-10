@php
    $solicitantes = [
        'AIRTON OSMAR FIALLA',
        'ANDERSON MOURA SANDERS',
        'BRUNA GIOVANA DE SA',
        'ELIANE DA SILVA CAMARGO',
        'HARRY PHELIP CUBAS',
        'JULIA AMARAL RODRIGUES',
        'LUCAS GROCHOSKI',
        'LUIZ CESAR LOPES JUNIOR',
        'RICARDO MAGALHAES BLUM',
        'SIMONE DE QUADROS',
        'THIAGO REIS PUCCI',
        'VITOR HUGO SILVA SMANGORZEVSKI',
        'LOGÍSTICA',
        'COMERCIAL',
        'VENDAS',
        'FINANCEIRO',
        'FISCAL',
        'IMPORTAÇÃO',
        'CLIENTE',
        'DIRETORIA'
    ];
@endphp

<form id="form_{{ $context }}" class="mt-4 p-4 bg-white rounded-3 shadow-sm border">
    <div class="row g-4">
        <!-- Motivo -->
        <div class="col-md-5">
            <label for="motivo_{{ $context }}" class="form-label fw-semibold text-secondary">
                <i class="ti ti-alert-circle me-1 text-muted"></i> Motivo
            </label>
            <select id="motivo_{{ $context }}" class="form-select" required>
                <option value="">Selecione um motivo</option>
                <optgroup label="Notas Fiscais e Reentregas">
                    <option value="Emissão de nova nota fiscal + reentrega">Emissão de nova nota fiscal + reentrega</option>
                    <option value="Somente Emissão de nova nota fiscal">Somente Emissão de nova nota fiscal</option>
                    <option value="Somente ajuste de estoque">Somente ajuste de estoque</option>
                    <option value="Baixa financeira">Baixa financeira</option>
                </optgroup>
                <optgroup label="Sucateamento / Descarte">
                    <option value="Material Descartado">Material Descartado</option>
                    <option value="Devolução + sucateamento">Devolução + sucateamento</option>
                </optgroup>
            </select>
        </div>

        <!-- Código de erro -->
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

        <!-- Checkbox -->
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="movimentacaoMercadoria_{{ $context }}">
                <label class="form-check-label text-secondary" for="movimentacaoMercadoria_{{ $context }}">
                    Movimentação?
                </label>
            </div>
        </div>

        <!-- XML -->
        <div class="col-md-6">
            <label for="xmlFileInput_{{ $context }}" class="form-label fw-semibold text-secondary">
                <i class="ti ti-file-code me-1 text-muted"></i> Carregar XML
            </label>
            <input type="file" id="xmlFileInput_{{ $context }}" class="form-control" accept=".xml">
        </div>

        <!-- Responsável -->
        <div class="col-md-6">
            <label for="gestorSolicitante_{{ $context }}" class="form-label fw-semibold text-secondary">
                <i class="ti ti-user me-1 text-muted"></i> Responsável
            </label>
            <select id="gestorSolicitante_{{ $context }}" class="form-select" required>
                <option value="">Selecione o responsável</option>
                @foreach ($solicitantes as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <!-- Observações -->
        <div class="col-12">
            <label for="observacoes_{{ $context }}" class="form-label fw-semibold text-secondary">
                <i class="ti ti-notes me-1 text-muted"></i> Observações
            </label>
            <textarea id="observacoes_{{ $context }}" class="form-control" rows="3" required style="resize: none;"></textarea>
        </div>
    </div>

    <!-- Informações XML -->
    <div id="client-info-box_{{ $context }}" class="mt-4 p-3 rounded-3 border-start border-4 border-primary bg-light-subtle shadow-sm">
        <h6 class="fw-bold text-primary mb-3"><i class="ti ti-file-description me-1"></i> Informações do XML</h6>
        <div class="row small text-secondary">
            <div class="col-md-6">
                <p><strong>Cliente:</strong> <span id="client-name-display_{{ $context }}">N/A</span></p>
                <p><strong>CNPJ:</strong> <span id="client-cnpj-display_{{ $context }}">N/A</span></p>
                <p><strong>NF Saída:</strong> <span id="nf-saida-display_{{ $context }}">N/A</span></p>
            </div>
            <div class="col-md-6">
                <p><strong>NF Devolução:</strong> <span id="inf-nfd-display_{{ $context }}">N/A</span></p>
                <p><strong>Protocolo:</strong> <span id="nprot-display_{{ $context }}">N/A</span></p>
                <p><strong>Info. Complementares:</strong> <span id="inf-cpl-display_{{ $context }}">N/A</span></p>
            </div>
        </div>
    </div>

    <!-- Itens -->
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <h6 class="text-muted mb-0"><i class="ti ti-list-details me-1"></i> Itens da Nota Fiscal</h6>
        <button type="button" class="btn btn-sm btn-outline-primary btn-pill toggle-items-btn"
            data-target="#product-table-body_{{ $context }}">
            <i class="ti ti-chevron-down me-1"></i> Mostrar Itens
        </button>
    </div>

    <div class="table-responsive mt-3 items-container shadow-sm rounded-3 border" style="display:none;">
        <table class="table table-vcenter table-striped align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary">
                    <th>Artigo</th>
                    <th>Descrição</th>
                    <th>NCM</th>
                    <th>NF Saída</th>
                    <th>NF Devolução</th>
                    <th>Qtd.</th>
                    <th>Preço Unit.</th>
                </tr>
            </thead>
            <tbody id="product-table-body_{{ $context }}">
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">Nenhum item carregado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</form>
