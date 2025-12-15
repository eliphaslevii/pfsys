@extends('layouts.app')

@section('content')

{{-- 🔹 Título --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold text-primary mb-0">
        <i class="ti ti-stairs-up me-2"></i>
        Etapas do Fluxo — {{ $template->name }}
    </h3>

    <button class="btn btn-primary"
            id="btnNewStep"
            data-template="{{ $template->id }}"
            data-bs-toggle="modal"
            data-bs-target="#stepModal">
        <i class="ti ti-plus"></i> Nova Etapa
    </button>
</div>

{{-- 🔹 Tabela de Steps --}}
<div class="card shadow-sm border-0">
    <div class="card-body">

        @if ($template->steps->isEmpty())
            <div class="text-center text-muted py-4">
                Nenhuma etapa cadastrada ainda.
            </div>
        @else
            <table class="table table-hover align-middle" id="stepsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Etapa</th>
                        <th>Setor</th>
                        <th>Nível</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody id="sortableSteps">
                    @foreach ($template->steps as $s)
                        <tr data-id="{{ $s->id }}">
                            <td width="40" class="text-muted">{{ $s->step_order }}</td>
                            <td class="fw-semibold">{{ $s->name }}</td>
                            <td>{{ $s->sector->name ?? '—' }}</td>
                            <td>
                                L{{ $s->level->authority_level ?? '—' }}
                                — {{ $s->level->name ?? 'Sem nível' }}
                            </td>

                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary btn-edit-step"
                                        data-id="{{ $s->id }}"
                                        data-template="{{ $template->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#stepModal">
                                    <i class="ti ti-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('workflows.deleteStep', $s->id) }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        @endif
    </div>
</div>

{{-- 🔹 Importa modal --}}
@include('returnProcess.workflows.modals.step_modal')

@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const notyf = new Notyf();

    /* ============================
       ABRIR MODAL PARA NOVO STEP
    ============================ */
    document.getElementById('btnNewStep').addEventListener('click', e => {
        document.getElementById('stepModalTitle').innerHTML = '<i class="ti ti-plus me-1"></i> Nova Etapa';

        document.getElementById('stepMethod').value = "POST";
        document.getElementById('stepForm').action = "{{ route('workflows.addStep') }}";

        // limpa campos
        document.getElementById('stepName').value = "";
        document.getElementById('stepOrder').value = "";
        document.getElementById('stepTemplateId').value = e.target.dataset.template;
        document.getElementById('stepSector').value = "";
        document.getElementById('stepLevel').value = "";
    });

    /* ============================
       EDITAR STEP
    ============================ */
    document.querySelectorAll('.btn-edit-step').forEach(btn => {
        btn.addEventListener('click', async () => {

            const id = btn.dataset.id;

            const res = await fetch(`/admin/workflows/step/${id}/edit`, {
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json();
            if (!json.success) {
                notyf.error("Erro ao carregar etapa.");
                return;
            }

            const s = json.step;

            document.getElementById('stepModalTitle').innerHTML = '<i class="ti ti-edit me-1"></i> Editar Etapa';
            document.getElementById('stepMethod').value = "PUT";
            document.getElementById('stepForm').action = `/admin/workflows/step/${id}`;

            document.getElementById('stepName').value = s.name;
            document.getElementById('stepOrder').value = s.step_order;
            document.getElementById('stepSector').value = s.sector_id;
            document.getElementById('stepLevel').value = s.level_id;
            document.getElementById('stepTemplateId').value = s.workflow_template_id;
        });
    });

});
</script>
@endsection
