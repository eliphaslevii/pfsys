<div class="row g-4">

    {{-- Motivo --}}
    <div class="col-md-5">
        <label for="motivo_recusa" class="form-label fw-semibold text-secondary">
            <i class="ti ti-alert-circle me-1 text-muted"></i> Motivo
        </label>
        <select id="motivo_recusa" name="motivo" class="form-select" required>
            <option value="">Selecione um motivo</option>
            @foreach ($motivos as $m)
                <option value="{{ $m->name }}">{{ $m->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Código de erro --}}
    <div class="col-md-5">
        <label for="codigo_erro_recusa" class="form-label fw-semibold text-secondary">
            <i class="ti ti-bug me-1 text-muted"></i> Código de erro
        </label>
        <select id="codigo_erro_recusa" name="codigo_erro" class="form-select" required>
            <option value="">Selecione um código</option>
            <option value="Preço errado">Preço errado</option>
            <option value="Produto errado">Produto errado</option>
            <option value="Faturamento sem autorização">Faturamento sem autorização</option>
            <option value="Duplicidade">Duplicidade</option>
        </select>
    </div>

    {{-- Movimentação --}}
    <div class="col-md-2 d-flex align-items-end pb-1">
        <label class="form-check form-switch cursor-pointer">
            <input class="form-check-input"
                   type="checkbox"
                   id="movimentacaoMercadoria_recusa"
                   name="movimentacao">
            <span class="form-check-label fw-semibold text-dark ms-2">
                Movimentação Física?
            </span>
        </label>
    </div>

    {{-- XML --}}
    <div class="col-md-6">
        <label for="xmlFileInput_recusa" class="form-label fw-semibold text-secondary">
            <i class="ti ti-file-code me-1 text-muted"></i> Carregar XML
        </label>
        <input type="file"
               id="xmlFileInput_recusa"
               name="xml"
               class="form-control"
               accept=".xml"
               required>
    </div>

    {{-- Responsável --}}
    <div class="col-md-6">
        <label for="gestorSolicitante_recusa" class="form-label fw-semibold text-secondary">
            <i class="ti ti-user me-1 text-muted"></i> Responsável
        </label>
        <select id="gestorSolicitante_recusa"
                name="responsavel"
                class="form-select"
                required>
            <option value="">Selecione o responsável</option>
            @foreach ($solicitantes as $user)
                <option value="{{ $user->name }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Observações --}}
    <div class="col-12">
        <label for="observacoes_recusa" class="form-label fw-semibold text-secondary">
            <i class="ti ti-notes me-1 text-muted"></i> Observações
        </label>
        <textarea id="observacoes_recusa"
                  name="observacoes"
                  class="form-control"
                  rows="2"
                  required
                  style="resize: none;"
                  placeholder="Insira as observações aqui..."></textarea>
    </div>

</div>
<div class="mt-4 pt-2 border-top">

    <button type="button"
            class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center py-2"
            data-bs-toggle="collapse"
            data-bs-target="#xmlDetailsArea_recusa"
            aria-expanded="false">
        <span class="fw-bold">
            <i class="ti ti-list-details me-2"></i>
            Visualizar Dados da Nota e Itens
        </span>
        <i class="ti ti-chevron-down"></i>
    </button>

    <div class="collapse mt-3" id="xmlDetailsArea_recusa">

        {{-- TODO o conteúdo interno permanece IGUAL,
             só trocando {{ $context }} por _recusa --}}
    </div>
</div>
