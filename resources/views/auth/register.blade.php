@extends('layouts.app')

@section('content')
    <div class="container-xl">

        <!-- TÍTULO E AÇÕES -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title fw-bold text-primary mb-1">Central de Administração de Usuários</h2>
                <p class="text-muted mb-0">Gerencie registros, níveis de acesso e permissões.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                data-bs-target="#modal-register">
                <i class="ti ti-user-plus me-1"></i> Novo Usuário
            </button>
        </div>

        <!-- DASHBOARD CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Total de Usuários</div>
                        <div class="h2 fw-bold mt-1">{{ $stats['total'] ?? 0 }}</div>
                        <i class="ti ti-users fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Ativos</div>
                        <div class="h2 fw-bold mt-1 text-success">{{ $stats['active'] ?? 0 }}</div>
                        <i class="ti ti-user-check fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Inativos</div>
                        <div class="h2 fw-bold mt-1 text-danger">{{ $stats['inactive'] ?? 0 }}</div>
                        <i class="ti ti-user-off fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-muted">Setores</div>
                        <div class="h2 fw-bold mt-1">{{ $stats['sectors'] ?? 0 }}</div>
                        <i class="ti ti-building fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABELA DE USUÁRIOS -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0 fw-semibold">Usuários Cadastrados</h3>
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
                            <th>Email</th>
                            <th>Setor</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->sector->name ?? 'N/A' }}</td>
                                <td>{{ $user->level->name ?? 'N/A' }}</td>
                                <td>
                                    @if ($user->active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-danger">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-2"
                                        data-edit-url="{{ route('admin.users.edit', $user) }}" data-modal="#editUserModal">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('admin.users.destroy', $user) }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nenhum usuário encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE REGISTRO -->
    <div class="modal fade" id="modal-register" tabindex="-1" aria-labelledby="modal-register-label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal-register-label">Registrar Novo Usuário</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">

                        @csrf

                        <!-- Campos falsos para bloquear autocomplete -->
                        <input type="text" name="fakeusernameremembered" style="display:none">
                        <input type="password" name="fakepasswordremembered" style="display:none">

                        <!-- Nome -->
                        <div class="mb-3">
                            <label class="form-label required">Nome Completo</label>
                            <input type="text" name="name" class="form-control" required placeholder="Nome Completo"
                                autocomplete="off">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="usuario@email.com"
                                autocomplete="off">
                        </div>

                        <!-- Setor -->
                        <div class="mb-3">
                            <label class="form-label required">Setor</label>
                            <select name="sector_id" class="form-select" required autocomplete="off">
                                <option value="">Selecione...</option>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nível -->
                        <div class="mb-3">
                            <label class="form-label required">Nível</label>
                            <select name="level_id" class="form-select" required autocomplete="off">
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">
                                        {{ $level->name }} — {{ $level->sector->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Senha -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Senha</label>
                                <input type="password" name="password" class="form-control" required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Confirme a Senha</label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-user-plus me-1"></i> Registrar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO DE USUÁRIO -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="editUserForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" id="editName" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="editEmail" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Setor</label>
                            <select name="sector_id" id="editSector" class="form-select" required>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nível</label>
                            <select name="level_id" id="editLevel" class="form-select" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active" id="editActive">
                            <label class="form-check-label" for="editActive">Ativo</label>
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