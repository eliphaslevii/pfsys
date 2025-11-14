@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold text-primary mb-4">
        Processo #{{ $process->id }} — {{ ucfirst($process->status) }}
    </h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">Informações Gerais</h5>
            <p><strong>Cliente:</strong> {{ $process->cliente_nome }}</p>
            <p><strong>CNPJ:</strong> {{ $process->cliente_cnpj }}</p>
            <p><strong>Status:</strong> {{ $process->status }}</p>
            <p><strong>Etapa Atual:</strong> {{ $process->currentWorkflow->step_name ?? '—' }}</p>
            <p><strong>Observações:</strong> {{ $process->observacoes ?? '—' }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">Itens</h5>
            <table class="table table-sm table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Artigo</th>
                        <th>Descrição</th>
                        <th>NF Saída</th>
                        <th>NF Devolução</th>
                        <th>Qtd</th>
                        <th>Preço Unit.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($process->items as $item)
                        <tr>
                            <td>{{ $item->artigo }}</td>
                            <td>{{ $item->descricao }}</td>
                            <td>{{ $item->nf_saida ?? '—' }}</td>
                            <td>{{ $item->nf_devolucao ?? '—' }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhum item encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
