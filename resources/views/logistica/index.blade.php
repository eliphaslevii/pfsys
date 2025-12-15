@extends('layouts.app')

@section('content')

<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row g-2 align-items-center">

            <div class="col">
                <h2 class="page-title">
                    Logística – Central de Agendamentos
                </h2>
                <div class="text-muted mt-1">
                    Gerencie coletas, agendamentos e transportadoras.
                </div>
            </div>

        </div>
    </div>
</div>

<div class="page-body mt-0">
    <div class="container-xl">

        {{-- ======================= --}}
        {{-- GRID DE AÇÕES         --}}
        {{-- ======================= --}}
        <div class="row row-cards">

            {{-- Criar novo agendamento --}}
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route('logistica.agendamentos.create') }}"
                    class="card card-link card-link-pop"
                    style="text-decoration:none;">

                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-primary text-white me-3">
                                <i class="ti ti-calendar-plus"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Novo Agendamento</h3>
                                <p class="text-muted small mb-0">
                                    Selecionar transportadora e NFes.
                                </p>
                            </div>
                        </div>
                    </div>

                </a>
            </div>


        {{-- Ver lista de agendamentos --}}
        <div class="col-sm-6 col-lg-4">
            <div class="card card-link card-link-pop">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-blue text-white me-3">
                            <i class="ti ti-list-details"></i>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">Agendamentos</h3>
                            <p class="text-muted small mb-0">
                                Ver, filtrar e acompanhar coletas.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- sem rota por enquanto --}}
            </div>
        </div>

        {{-- Painel de NFes carregadas --}}
        <div class="col-sm-6 col-lg-4">
            <div class="card card-link card-link-pop">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-green text-white me-3">
                            <i class="ti ti-file-invoice"></i>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">NFes Disponíveis</h3>
                            <p class="text-muted small mb-0">
                                Visualizar notas importadas para agendar.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- sem rota por enquanto --}}
            </div>
        </div>

        {{-- Histórico --}}
        <div class="col-sm-6 col-lg-4">
            <div class="card card-link card-link-pop">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-orange text-white me-3">
                            <i class="ti ti-history"></i>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">Histórico</h3>
                            <p class="text-muted small mb-0">
                                Consultar coletas finalizadas.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- sem rota por enquanto --}}
            </div>
        </div>

        {{-- Gestão de transportadoras --}}
        <div class="col-sm-6 col-lg-4">
            <div class="card card-link card-link-pop">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-purple text-white me-3">
                            <i class="ti ti-truck"></i>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">Transportadoras</h3>
                            <p class="text-muted small mb-0">
                                Lista e status das transportadoras.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- sem rota por enquanto --}}
            </div>
        </div>

        {{-- Configurações --}}
        <div class="col-sm-6 col-lg-4">
            <div class="card card-link card-link-pop">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-secondary text-white me-3">
                            <i class="ti ti-settings"></i>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">Configurações</h3>
                            <p class="text-muted small mb-0">
                                Ajustes e preferências do módulo.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- sem rota por enquanto --}}
            </div>
        </div>

    </div>

</div>
</div>

@endsection