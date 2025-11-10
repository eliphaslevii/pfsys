@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Fluxo de Processos de Devolução / Recusa</h5>
            <button class="btn btn-light btn-sm" id="refreshTable">
                <i class="ti ti-refresh"></i> Atualizar
            </button>
        </div>

        <div class="card-body">
            <table id="processTable" class="table table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Motivo</th>
                        <th>Status</th>
                        <th>Etapa Atual</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Recusa -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <div class="modal-header bg-danger text-white">
              <h5 class="modal-title" id="rejectModalLabel">Recusar Processo</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <label for="rejectComment" class="form-label">Motivo da recusa</label>
              <textarea id="rejectComment" class="form-control" rows="4" placeholder="Descreva o motivo..."></textarea>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" id="confirmRejectBtn" class="btn btn-danger">Confirmar Recusa</button>
          </div>
      </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    const table = $('#processTable').DataTable({
        ajax: '{{ route("return.process.data") }}',
        columns: [
            { data: 'id' },
            { data: 'cliente_nome', defaultContent: '-' },
            { data: 'observacoes', defaultContent: '-' },
            { data: 'status', defaultContent: '-' },
            {
                data: 'current_workflow_id',
                render: function(data, type, row) {
                    return row.workflow?.step_name ?? '—';
                }
            },
            {
                data: 'created_at',
                render: data => new Date(data).toLocaleString('pt-BR')
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm btn-advance" data-id="${row.id}">
                            <i class="ti ti-arrow-right"></i>
                        </button>
                        <button class="btn btn-warning btn-sm btn-rollback" data-id="${row.id}">
                            <i class="ti ti-arrow-left"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-reject" data-id="${row.id}">
                            <i class="ti ti-x"></i>
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
        }
    });

    // 🔄 Atualizar tabela
    $('#refreshTable').on('click', () => table.ajax.reload());

    // ✅ Avançar etapa
    $(document).on('click', '.btn-advance', function () {
        const id = $(this).data('id');
        sendAction(id, 'advance');
    });

    // 🔁 Voltar etapa
    $(document).on('click', '.btn-rollback', function () {
        const id = $(this).data('id');
        sendAction(id, 'rollback');
    });

    // ❌ Abrir modal de recusa
    let rejectId = null;
    $(document).on('click', '.btn-reject', function () {
        rejectId = $(this).data('id');
        $('#rejectModal').modal('show');
    });

    // ✅ Confirmar recusa
    $('#confirmRejectBtn').on('click', function () {
        const comment = $('#rejectComment').val().trim();
        if (!comment) {
            showToast('Informe o motivo da recusa.', 'error');
            return;
        }
        sendAction(rejectId, 'reject', { comment });
        $('#rejectModal').modal('hide');
        $('#rejectComment').val('');
    });

    // 📤 Envio genérico de ação
    function sendAction(id, action, extra = {}) {
        $.ajax({
            url: `/return-process/${id}/update-step`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', action, ...extra },
            beforeSend: () => showToast('Processando...', 'info'),
            success: function (res) {
                showToast(res.message || 'Ação concluída.', 'success');
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Erro ao processar ação.';
                showToast(msg, 'error');
            }
        });
    }

    // 🍞 Toast genérico
    function showToast(message, type = 'info') {
        const colors = { success: '#2ecc71', error: '#e74c3c', info: '#3498db' };
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: colors[type] || '#555',
        }).showToast();
    }
});
</script>
@endpush
