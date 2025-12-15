@extends('layouts.app')

@section('content')
    @php
        $titulosGraficos = [
            "Recusas por Mês",
            "Devoluções por Mês",
            "Processos Aprovados",
            "Tempo Médio de Análise",
            "Volume Total Processado",
            "Taxa de Eficiência (%)"
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 d-flex align-items-center gap-4">

                    <img src="{{ asset('images/unnamed.jpg') }}" alt="Welcome" class="rounded-3 shadow-md"
                        style="width: 120px; height: 120px; object-fit: cover;">

                    <div>
                        <h2 class="fw-bold text-primary mb-1">
                            Bem-vindo ao CoreFlow
                        </h2>

                        <p class="text-muted mb-2">
                            Automação Inteligente de Processos Empresariais
                        </p>

                        <p class="mb-1">
                            <strong>Usuário:</strong> {{ auth()->user()->name }}
                        </p>

                        <p class="mb-1">
                            <strong>Nível de Acesso:</strong>
                            <span class="text-primary">
                                {{ auth()->user()->level->name ?? 'N/A' }}
                            </span>
                        </p>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- =============================================
    GRÁFICOS — GRID 3x2
    ============================================= --}}
    <div class="row g-4 mt-2">

        @for ($i = 1; $i <= 6; $i++)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0 fw-semibold">
                            {{ $titulosGraficos[$i - 1] }}
                        </h4>

                    </div>
                    <div class="card-body" style="height: 320px">
                        <canvas id="chart{{ $i }}" height="140"></canvas>
                    </div>
                </div>
            </div>
        @endfor

    </div>


    </div>

@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Configurações globais
        Chart.defaults.color = "#333";
        Chart.defaults.font.family = "'Inter', sans-serif";

        const labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
        const values = [12, 19, 14, 23, 17, 28];

        const chartColor = "#467FCF";
        const chartBG = "rgba(70, 127, 207, 0.25)";

        // Tipos diferentes para cada gráfico
        const chartTypes = [
            "line",
            "bar",
            "pie",
            "doughnut",
            "radar",
            "polarArea"
        ];

        for (let i = 1; i <= 6; i++) {
            new Chart(document.getElementById(`chart${i}`), {
                type: chartTypes[i - 1],
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Processos',
                        data: values,
                        borderColor: chartColor,
                        backgroundColor: [
                            chartBG,
                            chartBG,
                            chartBG,
                            chartBG,
                            chartBG,
                            chartBG
                        ],
                        borderWidth: 3,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: i >= 3 // Nos gráficos circulares precisa
                        }
                    },
                    scales: (i <= 2) ? { // Só line e bar usam eixos
                        y: { beginAtZero: true }
                    } : {}
                }
            });
        }
    </script>
@endpush