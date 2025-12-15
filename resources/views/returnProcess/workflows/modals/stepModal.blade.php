<div class="modal fade" id="stepModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="stepModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="stepForm" class="ajax-step-form">
                @csrf
                <input type="hidden" name="workflow_template_id" id="stepTemplateId">
                <input type="hidden" name="_method" id="stepMethod" value="POST">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nome da Etapa</label>
                        <input type="text" class="form-control" name="name" id="stepName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ordem</label>
                        <input type="number" class="form-control" name="order" id="stepOrder" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Setor Responsável</label>
                        <select class="form-select" name="sector_id" id="stepSector" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nível Necessário</label>
                        <select class="form-select" name="required_level_id" id="stepLevel" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notificação Automática</label>
                        <select class="form-select" name="auto_notify" id="stepAutoNotify">
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check"></i> Salvar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
