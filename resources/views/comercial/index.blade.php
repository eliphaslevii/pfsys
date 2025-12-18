@extends('layouts.app')

@section('content')

<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Comercial – Recusa e Devolução
                </h2>
                <div class="text-muted mt-1">
                    Central de abertura e acompanhamento de processos comerciais.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body mt-0">
    <div class="container-xl">

        <div class="row row-cards">

            {{-- Nova Recusa --}}
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route('recusa.create') }}"
                    class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-ban"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Nova Recusa</h3>
                                <p class="text-muted small mb-0">
                                    Registrar recusa no ato da entrega.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Nova Devolução --}}
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route('devolucao.create') }}"
                    class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-arrow-back-up"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Nova Devolução</h3>
                                <p class="text-muted small mb-0">
                                    Registrar devolução de mercadoria.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Processos em Andamento --}}
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route('processes.index') }}"
                    class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-list-check"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Processos Abertos</h3>
                                <p class="text-muted small mb-0">
                                    Acompanhar status e etapas.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Histórico --}}
            <div class="col-sm-6 col-lg-4">
                <a href="#" class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-history"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Histórico</h3>
                                <p class="text-muted small mb-0">
                                    Consultar processos finalizados.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Motivos --}}
            <div class="col-sm-6 col-lg-4">
                <a href="#" class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-alert-circle"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Motivos</h3>
                                <p class="text-muted small mb-0">
                                    Gerenciar motivos de recusa e devolução.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Configurações --}}
            <div class="col-sm-6 col-lg-4">
                <a href="#" class="card card-link card-link-pop text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="ti ti-settings"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">Configurações</h3>
                                <p class="text-muted small mb-0">
                                    Ajustes do módulo comercial.
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>
</div>

@endsection