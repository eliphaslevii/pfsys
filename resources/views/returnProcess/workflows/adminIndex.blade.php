@extends('layouts.app')

@section('content')

    {{-- ================================================
    TOASTFY (Notyf)
    ================================================ --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new Notyf().success("{{ session('success') }}");
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new Notyf().error("{{ session('error') }}");
            });
        </script>
    @endif



    {{-- ================================================
    CABEÇALHO
    ================================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="ti ti-git-branch me-2"></i> Hub de Configuração — Motivos e Fluxos
        </h3>

        <button class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#createReasonModal">
            <i class="ti ti-plus"></i> Novo Motivo
        </button>
        <button class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
            <i class="ti ti-plus"></i> Novo Fluxo
        </button>
    </div>



    {{-- ================================================
    TABELA DE MOTIVOS
    ================================================ --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Motivo</th>
                            <th>Fluxo Vinculado</th>
                            <th>Criado em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reasons as $reason)
                            <tr>
                                <td>{{ $reason->name }}</td>
                                <td>
                                    <span class="badge bg-primary-lt">
                                        {{ $reason->template->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $reason->created_at->format('d/m/Y H:i') }}</td>

                                <td class="text-end">

                                    {{-- Botão Editar --}}
                                    <button class="btn btn-sm btn-outline-primary me-1 " data-bs-toggle="modal"
                                        data-bs-target="#editReasonModal_{{ $reason->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    {{-- Botão Excluir --}}
                                    {{-- Remova qualquer tag <form> que envolva este botão --}}
                                        <button class="btn btn-sm btn-outline-danger btn-delete-reason"
                                            data-url="{{ route('workflows.deleteReason', $reason->id) }}">
                                            <i class="ti ti-trash"></i>
                                        </button>

                                </td>
                            </tr>

                            {{-- MODAL EDITAR MOTIVO --}}
                            <div class="modal fade" id="editReasonModal_{{ $reason->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">

                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="ti ti-edit me-1"></i> Editar Motivo
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('workflows.updateReason', $reason->id) }}">
                                            @csrf @method('PUT')

                                            <div class="modal-body">

                                                <div class="mb-3">
                                                    <label class="form-label small text-muted">Nome</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ $reason->name }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small text-muted">Fluxo Vinculado</label>
                                                    <select name="workflow_template_id" class="form-select" required>
                                                        @foreach ($templates as $template)
                                                            <option value="{{ $template->id }}"
                                                                @selected($template->id == $reason->workflow_template_id)>
                                                                {{ $template->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light border"
                                                    data-bs-dismiss="modal">Cancelar</button>

                                                <button class="btn btn-primary">
                                                    <i class="ti ti-check"></i> Salvar
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Nenhum motivo cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- paginação motivos --}}
            <div class="mt-2">
                {{ $reasons->links() }}
            </div>

        </div>
    </div>


    {{-- ================================================
    TABELA DE FLUXOS (TEMPLATES)
    ================================================ --}}
    <div class="card mt-4 shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Motivos</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($templates as $template)

                            {{-- ============================
                            LINHA DO TEMPLATE
                            ============================ --}}
                            <tr>
                                <td class="fw-bold">{{ $template->name }}</td>

                                <td>
                                    <span class="badge bg-blue-lt">
                                        {{ $template->reasons->count() }} motivos
                                    </span>
                                </td>

                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success-lt">Ativo</span>
                                    @else
                                        <span class="badge bg-danger-lt">Inativo</span>
                                    @endif
                                </td>

                                <td class="text-end">

                                    {{-- Botão que expande os STEPS --}}
                                    <button class="btn btn-sm btn-outline-primary btn-expand-steps"
                                        data-template="{{ $template->id }}">
                                        <i class="ti ti-stairs-up"></i> Steps
                                    </button>

                                    {{-- Editar fluxo --}}
                                    <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#editTemplateModal_{{ $template->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    {{-- Excluir --}}
                                    <button class="btn btn-sm btn-outline-danger btn-delete-template"
                                        data-url="{{ route('workflows.deleteTemplate', $template->id) }}">
                                        <i class="ti ti-trash"></i>
                                    </button>

                                </td>
                            </tr>

                            {{-- ============================
                            LINHA EXPANDÍVEL DOS STEPS
                            ============================ --}}
                            <tr id="stepsRow_{{ $template->id }}" class="steps-row" style="display:none;">
                                <td colspan="4" class="bg-light p-0">

                                    <div class="p-3">

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">
                                                <i class="ti ti-stairs-up text-primary"></i>
                                                Steps de <strong>{{ $template->name }}</strong>
                                            </h5>

                                            <button class="btn btn-sm btn-primary btn-create-step"
                                                data-template="{{ $template->id }}">
                                                <i class="ti ti-plus"></i> Novo Step
                                            </button>
                                        </div>

                                        {{-- Aqui o JS irá injetar a tabela --}}
                                        <div id="stepsArea_{{ $template->id }}" class="steps-area text-center text-muted py-3">
                                            <em>Clique em “Steps” para carregar…</em>
                                        </div>

                                    </div>

                                </td>
                            </tr>

                            {{-- ============================
                            MODAL EDITAR FLUXO
                            ============================ --}}
                            <div class="modal fade" id="editTemplateModal_{{ $template->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">

                                        <div class="modal-header bg-primary-lt">
                                            <h5 class="modal-title fw-bold">
                                                <i class="ti ti-edit me-1"></i> Editar Fluxo
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('workflows.updateTemplate', $template->id) }}"
                                            class="ajax-template-edit">

                                            @csrf @method('PUT')

                                            <div class="modal-body">

                                                <div class="mb-3">
                                                    <label class="form-label">Nome do Fluxo</label>
                                                    <input type="text" name="name" value="{{ $template->name }}"
                                                        class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small">Status</label>
                                                    <select name="is_active" class="form-select">
                                                        <option value="1" @selected($template->is_active)>Ativo</option>
                                                        <option value="0" @selected(!$template->is_active)>Inativo</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light border"
                                                    data-bs-dismiss="modal">Cancelar</button>

                                                <button class="btn btn-primary">
                                                    <i class="ti ti-check"></i> Salvar
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $templates->links() }}
        </div>
    </div>


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

                <form method="POST" action="{{ route('workflows.addTemplate') }}" class="ajax-template-create">

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
    @include('returnProcess.workflows.modals.stepModal')
    @push('scripts')
        @vite([
            'resources/js/modules/workflowTemplate/edit.js',
            'resources/js/modules/workflowTemplate/create.js',
            'resources/js/modules/steps/form.js'
        ])

    @endpush

@endsection