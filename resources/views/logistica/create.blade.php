@extends('layouts.app')

@section('content')

<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <h2 class="page-title">Novo Agendamento</h2>
        <div class="text-muted">Selecione transportadora e notas fiscais para gerar o agendamento.</div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <div class="card">
            <div class="card-body">

                {{-- =========================== --}}
                {{-- SELEÇÃO DA TRANSPORTADORA --}}
                {{-- =========================== --}}
                <div class="mb-3">
                    <label class="form-label">Transportadora</label>

                    <select id="transportadora-select" class="form-select">
                        <option value="">Selecione...</option>

                        @foreach ($transportadoras as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- =========================== --}}
                {{-- ÁREA DA TABELA DE NFES     --}}
                {{-- =========================== --}}
                <div id="nfes-area" class="d-none">

                    <h3 class="card-title mb-2">Notas Encontradas</h3>

                    <div class="table-responsive">
                        <table class="table table-vcenter table-sm table-nowrap card-table table-hover">
                            <thead>
                                <tr>
                                    <th class="w-1">
                                        <input type="checkbox" id="check-all">
                                    </th>
                                    <th>NF / Série</th>
                                    <th>Destinatário</th>
                                    <th>Volumes</th>
                                    <th>Peso</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th class="text-end">Emissão</th>
                                </tr>
                            </thead>
                            <tbody id="nfes-table-body"></tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button id="btn-criar" class="btn btn-primary">
                            Criar Agendamento
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

{{-- =========================== --}}
{{-- MODAL DE EMAIL             --}}
{{-- =========================== --}}
<div class="modal fade" id="modal-email" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Enviar Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Destinatário</label>
                    <input type="email" id="email-to" class="form-control" placeholder="email@transportadora.com">
                </div>

                <div class="mb-3">
                    <label class="form-label">CC</label>

                    <div id="cc-list">
                        <input type="email" class="form-control mb-2 cc-email" placeholder="cc@empresa.com">
                    </div>

                    <button type="button" id="add-cc" class="btn btn-sm btn-outline-secondary">
                        + Adicionar CC
                    </button>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btn-confirmar">
                    Confirmar e Enviar
                </button>
            </div>

        </div>
    </div>
</div>

{{-- =========================== --}}
{{-- SCRIPT ÚNICO               --}}
{{-- =========================== --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const select = document.getElementById('transportadora-select');
        const areaNF = document.getElementById('nfes-area');
        const tbody = document.getElementById('nfes-table-body');
        const checkAll = document.getElementById('check-all');
        const modal = new bootstrap.Modal(document.getElementById('modal-email'));

        let nfesSelecionadas = [];

        const TEMPLATE_ROW = nf => `
        <tr>
            <td><input type="checkbox" class="nf-check" value="${nf.id}"></td>
            <td class="fw-bold text-primary me-2"><strong>${nf.numero}</strong> <span class="text-muted">S:${nf.serie ?? '1'}</span></td>
            <td>${nf.dest}<br><small>${nf.cidade ?? ''}/${nf.uf ?? ''}</small></td>
            <td>${nf.volumes ?? '-'}</td>
            <td>${nf.peso_bruto ?? '-'} kg</td>
            <td>R$ ${nf.valor ?? '-'}</td>
            <td><span class="${nf.badgeClass}">${nf.badgeText}</span></td>
            <td class="text-end">${nf.data ? new Date(nf.data).toLocaleString('pt-BR') : '-'}</td>
        </tr>
    `;

        select.addEventListener('change', () => {
            const tp = select.value;

            if (!tp) {
                areaNF.classList.add('d-none');
                return;
            }

            fetch(`/logistica/agendamentos/nfes?transportadora=${encodeURIComponent(tp)}`)
                .then(r => r.json())
                .then(data => {
                    areaNF.classList.remove('d-none');
                    tbody.innerHTML = '';
                    data.forEach(nf => tbody.insertAdjacentHTML('beforeend', TEMPLATE_ROW(nf)));
                    checkAll.checked = false;
                });
        });

        checkAll.addEventListener('change', () => {
            document.querySelectorAll('.nf-check').forEach(c => c.checked = checkAll.checked);
        });

        document.getElementById('btn-criar').addEventListener('click', () => {
            nfesSelecionadas = [...document.querySelectorAll('.nf-check:checked')]
                .map(i => i.value);

            if (nfesSelecionadas.length === 0) {
                alert('Selecione ao menos uma NF-e');
                return;
            }

            modal.show();
        });

        document.getElementById('add-cc').addEventListener('click', () => {
            document.getElementById('cc-list')
                .insertAdjacentHTML('beforeend',
                    `<input type="email" class="form-control mb-2 cc-email" placeholder="cc@empresa.com">`
                );
        });

        document.getElementById('btn-confirmar').addEventListener('click', () => {
            const to = document.getElementById('email-to').value;
            const ccs = [...document.querySelectorAll('.cc-email')]
                .map(i => i.value)
                .filter(v => v);

            fetch('/logistica/agendamentos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        transportadora: select.value,
                        nfes: nfesSelecionadas,
                        email_to: to,
                        email_cc: ccs
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        modal.hide();

                        const notyf = new Notyf();
                        notyf.success(res.message ?? 'Agendamento criado com sucesso!');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1200); // 1.2s
                    } else {
                        new Notyf().error(res.message ?? 'Erro ao criar agendamento');
                    }
                })
                .catch(() => {
                    new Notyf().error('Erro inesperado ao processar o agendamento');
                });


        });

    });
</script>

@endsection