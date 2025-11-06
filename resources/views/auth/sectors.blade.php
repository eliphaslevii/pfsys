@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="page-title">Setores</h2>
    <p class="text-muted">Gerencie setores do sistema.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectorCreateModal">Novo Setor</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-vcenter mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Descrição</th>
          <th>Status</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sectors as $s)
        <tr>
          <td>{{ $s->id }}</td>
          <td>{{ $s->name }}</td>
          <td>{{ \Str::limit($s->description, 60) }}</td>
          <td>
            @if($s->is_active)
              <span class="badge bg-success">Ativo</span>
            @else
              <span class="badge bg-danger">Inativo</span>
            @endif
          </td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary me-2 btn-edit-sector" data-id="{{ $s->id }}"> <i class="ti ti-edit"></i> </button>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.sectors.destroy', $s) }}"> <i class="ti ti-trash"></i> </button>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum setor encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="sectorCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.sectors.store') }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Novo Setor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nome</label>
        <input name="name" class="form-control" required>
        <label class="form-label mt-3">Descrição</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
        
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit (reaproveitamos e preenchemos via JS) -->
<div class="modal fade" id="sectorEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="sectorEditForm" method="POST">
      @csrf
      @method('PATCH')
      <div class="modal-header">
        <h5 class="modal-title">Editar Setor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nome</label>
        <input name="name" id="editSectorName" class="form-control" required>
        <label class="form-label mt-3">Descrição</label>
        <textarea name="description" id="editSectorDesc" class="form-control" rows="3"></textarea>
        <div class="form-check mt-2">
          <input type="checkbox" name="is_active" id="editSectorActive" class="form-check-input">
          <label class="form-check-label" for="editSectorActive">Ativo</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const notyf = new Notyf();

  // editar setor: abrir modal e preencher
  document.querySelectorAll('.btn-edit-sector').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const res = await fetch(`/admin/sectors/${id}/edit`, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) return notyf.error('Erro ao carregar setor');
      const json = await res.json();
      const s = json.sector || json.data || json;
      document.getElementById('editSectorName').value = s.name;
      document.getElementById('editSectorDesc').value = s.description || '';
      document.getElementById('editSectorActive').checked = !!s.is_active;
      const form = document.getElementById('sectorEditForm');
      form.action = `/admin/sectors/${s.id}`;
      const modal = new bootstrap.Modal(document.getElementById('sectorEditModal'));
      modal.show();
    });
  });

  // forms em modais já são tratados pelo seu script global (fetch + persistência)
});
</script>
@endpush
