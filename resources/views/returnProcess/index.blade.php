@extends('layouts.app')

@section('content')

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title fw-bold text-primary mb-1">Central de Administração de Usuários</h2>
                <p class="text-muted mb-0">Gerencie registros, níveis de acesso e permissões.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                data-bs-target="#modal-register" id="toggleFormBtn">
                <i class="ti ti-user-plus me-1"></i> Nova Devolução
            </button>
        </div>


      {{-- ===========================
      📋 TABELA DE PROCESSOS
      ============================ --}}
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0">Acompanhamento de Processos</h4>
          <button class="btn btn-outline-secondary btn-sm" id="refreshTableBtn">
            <i class="ti ti-refresh"></i> Atualizar
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="processTable" class="table table-vcenter table-striped align-middle mb-0">
              <thead class="table-light">
                <tr class="text-secondary">
                  <th>#</th>
                  <th>Tipo</th>
                  <th>Motivo</th>
                  <th>Responsável</th>
                  <th>Status</th>
                  <th>Criado em</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($processes as $p)
                  <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ ucfirst($p->tipo) }}</td>
                    <td>{{ $p->motivo }}</td>
                    <td>{{ $p->responsavel ?? '-' }}</td>
                    <td>
                      @php
                          $statusClass = [
                              'aprovado' => 'text-success',
                              'recusado' => 'text-danger',
                              'pendente' => 'text-warning',
                          ][$p->status] ?? 'text-muted';
                      @endphp
                      <span class="fw-semibold {{ $statusClass }}">
                        <i class="ti ti-circle-filled me-1"></i> {{ ucfirst($p->status) }}
                      </span>
                    </td>
                    <td>{{ $p->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                      <div class="btn-group">
                        <a href="{{ route('return.process.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                          <i class="ti ti-eye"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-id="{{ $p->id }}" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">Nenhum processo encontrado.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- ===========================
      ⚙️ MODAIS
      ============================ --}}
      @include('returnProcess.partials.modals')

@endsection