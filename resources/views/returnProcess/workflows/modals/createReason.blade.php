
    {{-- ================================================
    MODAL: CRIAR MOTIVO
    ================================================ --}}
    <div class="modal fade" id="createReasonModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-plus me-1"></i> Novo Motivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('workflows.addReason') }}" class="ajax-form">
                    @csrf

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Fluxo Vinculado</label>
                            <select name="workflow_template_id" class="form-select" required>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary">
                            <i class="ti ti-check"></i> Criar Motivo
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
