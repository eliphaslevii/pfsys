@extends('layouts.app')

@section('content')



        {{-- ✅ Alertas de sucesso/erro --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- 🔹 Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary mb-0">
                <i class="ti ti-git-branch me-2"></i> Hub de Configuração — Motivos e Fluxos
            </h3>
            <button class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#createReasonModal">
                <i class="ti ti-plus"></i> Novo Motivo
            </button>
        </div>

        {{-- 🔹 Tabela de Motivos --}}
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
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $reason->template->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $reason->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">

                                        {{-- Botão Editar --}}
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editReasonModal_{{ $reason->id }}">
                                            <i class="ti ti-edit"></i>
                                        </button>

                                        {{-- Botão Excluir --}}
                                        <form action="{{ route('workflows.deleteReason', $reason->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Tem certeza que deseja excluir este motivo?');">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- 🔹 Modal de Edição --}}
                                <div class="modal fade" id="editReasonModal_{{ $reason->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><i class="ti ti-edit me-1"></i> Editar Motivo</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('workflows.updateReason', $reason->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small text-muted">Nome do Motivo</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $reason->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small text-muted">Fluxo Vinculado</label>
                                                        <select name="workflow_template_id" class="form-select" required>
                                                            @foreach ($templates as $template)
                                                                <option value="{{ $template->id }}"
                                                                    {{ $template->id == $reason->workflow_template_id ? 'selected' : '' }}>
                                                                    {{ $template->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ti ti-check"></i> Salvar Alterações
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Nenhum motivo cadastrado ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



{{-- 🔹 Modal: Criar Novo Motivo --}}
<div class="modal fade" id="createReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ti ti-plus me-1"></i> Novo Motivo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workflows.addReason') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nome do Motivo</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Retorno de Material" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Fluxo Vinculado</label>
                        <select name="workflow_template_id" class="form-select" required>
                            <option value="">Selecione um fluxo...</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check"></i> Criar Motivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
