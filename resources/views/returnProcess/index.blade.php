@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="page-title fw-bold text-primary mb-1">Central de Devoluções / Recusas</h2>
    <p class="text-muted mb-0">Acompanhe e gerencie processos em andamento.</p>
  </div>
  <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
      data-bs-target="#modal-register" id="btnNewProcess">
    <i class="ti ti-plus me-1"></i> Nova Devolução
  </button>
</div>

{{-- ===========================
📋 TABELA DE PROCESSOS
=========================== --}}
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-title mb-0">Acompanhamento de Processos</h4>
    <button class="btn btn-outline-secondary btn-sm" id="btnRefresh">
      <i class="ti ti-refresh"></i> Atualizar
    </button>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="processTable" class="table table-vcenter table-striped align-middle mb-0">
        <thead class="table-light">
          <tr class="text-secondary">
            <th>ID</th>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>CNPJ</th>
            <th>Status</th>
            <th>Etapa Atual</th>
            <th>Responsável</th>
            <th>Criado em</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody id="processTableBody">
          <tr>
            <td colspan="9" class="text-center text-muted py-3">
              Carregando dados...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('returnProcess.partials.modals')
@endsection
@push('scripts')
  {{-- 🔹 1. Define as permissões globais antes de carregar os JS --}}
  <script>
    window.userPermissions = @json(auth()->user()->level?->permissions->pluck('name') ?? []);
    console.log('🔐 Permissões carregadas:', window.userPermissions);
  </script>

  {{-- 🔹 2. Carrega os scripts do módulo --}}
  @vite([
    'resources/js/modules/returnProcess/return-process.js',
    'resources/js/modules/returnProcess/return-process-index.js'
  ])
@endpush
