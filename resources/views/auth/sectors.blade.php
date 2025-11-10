@extends('layouts.app')

@section('content')
    <div class="container-xl">
        <!-- TÍTULO E AÇÕES -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title fw-bold text-primary mb-1">Gerenciamento de Setores</h2>
                <p class="text-muted mb-0">Administre setores e suas hierarquias.</p>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#modal-sector">
                    <i class="ti ti-plus me-1"></i> Novo Setor
                </button>
                <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#modal-level">
                    <i class="ti ti-plus me-1"></i> Novo Nível
                </button>
            </div>
        </div>
        <!-- DASHBOARD CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Total de Setores</div>
                        <div class="h2 fw-bold mt-1">{{ $stats['total'] ?? 0 }}</div>
                        <i class="ti ti-building fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Ativos</div>
                        <div class="h2 fw-bold mt-1 text-success">{{ $stats['active'] ?? 0 }}</div>
                        <i class="ti ti-check fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Inativos</div>
                        <div class="h2 fw-bold mt-1 text-danger">{{ $stats['inactive'] ?? 0 }}</div>
                        <i class="ti ti-x fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Com Subsetores</div>
                        <div class="h2 fw-bold mt-1">{{ $stats['with_children'] ?? 0 }}</div>
                        <i class="ti ti-hierarchy fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABELA DE SETORES -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0 fw-semibold">Setores Cadastrados</h3>
                <div>
                    <button class="btn btn-light btn-sm"><i class="ti ti-refresh"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter text-nowrap table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sectors as $sector)
                            <tr>
                                <td>{{ $sector->id }}</td>
                                <td>{{ $sector->name }}</td>
                                <td>{{ $sector->description ?? '—' }}</td>
                                <td>
                                    @if ($sector->is_active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-danger">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-2"
                                        data-edit-url="{{ route('admin.sectors.edit', $sector) }}"
                                        data-modal="#editSectorModal">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('admin.sectors.destroy', $sector) }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABELA DE LEVELS -->
        <hr class="my-5">

        <!-- TABELA DE NÍVEIS -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title fw-semibold mb-0">Cargos e Hierarquias</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter text-nowrap table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Setor</th>
                            <th>Autoridade</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($levels as $l)
                            <tr>
                                <td>{{ $l->id }}</td>
                                <td>{{ $l->name }}</td>
                                <td>{{ $l->sector->name ?? '—' }}</td>
                                <td>{{ $l->authority_level }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-2"
                                        data-edit-url="{{ route('admin.levels.edit', $l) }}" data-modal="#editLevelModal">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('admin.levels.destroy', $l) }}">
                                        <i class="ti ti-trash"></i>
                                    </button>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhum nível cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: NOVO SETOR -->
    <div class="modal fade" id="modal-sector" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.sectors.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Setor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Setor Pai</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Nenhum</option>
                                @foreach ($sectors as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" checked>
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDITAR SETOR -->
    <div class="modal fade" id="editSectorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Setor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Setor Pai</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Nenhum</option>
                                @foreach ($sectors as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input">
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Novo Level -->
    <div class="modal fade" id="modal-level" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.levels.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Nível</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Setor</label>
                            <select name="sector_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Autoridade (0–100)</label>
                            <input type="number" name="authority_level" class="form-control" min="0" max="100" value="10">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Editar Level -->
    <div class="modal fade" id="editLevelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Nível</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Setor</label>
                            <select name="sector_id" class="form-select" required>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Autoridade</label>
                            <input type="number" name="authority_level" class="form-control" min="0" max="100">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection