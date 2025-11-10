@extends('layouts.app')

@section('content')

<div class="page-">
  <div class="page-">
    <div class="container-">
      <div class="row justify--center">
        <div class="col-12  ">

          {{-- todo o conteúdo atual (tabs, forms, card, etc.) --}}
          <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom-0">
              <h3 class="card-title mb-0">Novo Processo de Devolução / Recusa</h3>
            </div>

            <div class="card-body p-0">
              <!-- Tabs -->
              <ul class="nav nav-tabs px-3 pt-3" id="returnProcessTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="recusa-tab" data-bs-toggle="tab" data-bs-target="#recusa" type="button" role="tab" aria-controls="recusa" aria-selected="true">
                    <i class="ti ti-ban me-1"></i> Recusa
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="devolucao-tab" data-bs-toggle="tab" data-bs-target="#devolucao" type="button" role="tab" aria-controls="devolucao" aria-selected="false">
                    <i class="ti ti-refresh me-1"></i> Devolução
                  </button>
                </li>
              </ul>

              <div class="tab-content p-4" id="returnProcessTabContent">
                <div class="tab-pane fade show active" id="recusa" role="tabpanel" aria-labelledby="recusa-tab">
                  @include('returnProcess.partials.form', ['context' => 'recusa'])
                </div>
                <div class="tab-pane fade" id="devolucao" role="tabpanel" aria-labelledby="devolucao-tab">
                  @include('returnProcess.partials.form', ['context' => 'devolucao'])
                </div>
              </div>
            </div>

            <div class="card-footer bg-white text-end border-top-0 pt-3">
              <button class="btn btn-primary btn-pill" id="sendApprovRequest">
                <i class="ti ti-device-floppy me-1"></i> Salvar Processo
              </button>
            </div>
          </div>
          {{-- fim do card --}}

        </div>
      </div>
    </div>
  </div>
</div>

@vite(['resources/js/modules/returnProcess/create.js'])
@endsection
