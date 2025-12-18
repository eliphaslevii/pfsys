<div class="card shadow-sm">
    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table table-vcenter table-striped align-middle mb-0">

                <thead class="table-light">
                    <tr class="text-uppercase small text-secondary">
                        <th class="ps-3">#</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>CNPJ</th>
                        <th>Status</th>
                        <th>Etapa</th>
                        <th>Criado em</th>
                        <th class="text-end pe-3">Ações</th>
                    </tr>
                </thead>

                <tbody id="processTableBody">

                    @forelse ($processes as $process)
                        <tr>

                            <td class="ps-3 fw-semibold">
                                {{ $process->id }}
                            </td>

                            <td>
                                <span class="badge bg-indigo-lt">
                                    {{ $process->type->name }}
                                </span>
                            </td>

                            <td class="text-truncate" style="max-width: 220px;">
                                {{ $process->cliente_nome }}
                            </td>

                            <td class="text-muted">
                                {{ $process->cliente_cnpj }}
                            </td>

                            <td>
                                @php
                                    $statusColor = match ($process->status) {
                                        'Em Andamento' => 'bg-blue-lt',
                                        'Em Execução'  => 'bg-yellow-lt',
                                        'Finalizado'   => 'bg-green-lt',
                                        'Rejeitado'    => 'bg-red-lt',
                                        default        => 'bg-secondary-lt',
                                    };
                                @endphp

                                <span class="badge {{ $statusColor }}">
                                    {{ $process->status }}
                                </span>
                            </td>

                            <td>
                                {{ $process->currentStep?->name ?? '—' }}
                            </td>

                            <td class="text-muted">
                                {{ $process->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="text-end pe-3">
                                <button
                                    class="btn btn-sm btn-outline-primary btnVerProcesso"
                                    data-id="{{ $process->id }}"
                                    title="Visualizar processo">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                                Nenhum processo encontrado.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

    </div>
</div>
