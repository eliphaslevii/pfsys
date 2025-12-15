    {{-- ================================================
    MODAL: CRIAR NOVO FLUXO
    ================================================ --}}
    <div class="modal fade" id="createTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-git-branch-plus me-1"></i> Novo Fluxo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('workflows.addTemplate') }}" class="ajax-form">
                    @csrf

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Nome do Fluxo</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Processo</label>
                            <select name="process_type_id" class="form-select" required>
                                @foreach ($processTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary">
                            <i class="ti ti-check"></i> Criar Fluxo
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>