@php
    $solicitantes = [
        'AIRTON OSMAR FIALLA',
        'ANDERSON MOURA SANDERS',
        'BRUNA GIOVANA DE SA',
        'ELIANE DA SILVA CAMARGO',
        'HARRY PHELIP CUBAS',
        'JULIA AMARAL RODRIGUES',
        'LUCAS GROCHOSKI',
        'LUIZ CESAR LOPES JUNIOR',
        'RICARDO MAGALHAES BLUM',
        'SIMONE DE QUADROS',
        'THIAGO REIS PUCCI',
        'VITOR HUGO SILVA SMANGORZEVSKI',
        'LOGÍSTICA',
        'COMERCIAL',
        'VENDAS',
        'FINANCEIRO',
        'FISCAL',
        'IMPORTAÇÃO',
        'CLIENTE',
        'DIRETORIA'
    ];
@endphp


      <div class="row justify--center">
        <div class="col-12">

          {{-- CARD COMPLETO --}}

        

            <div class="card-body p-0">
              <!-- Tabs -->
              <ul class="nav nav-tabs px-3 pt-3" id="returnProcessTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="recusa-tab" data-bs-toggle="tab" data-bs-target="#recusa" type="button" role="tab">
                    <i class="ti ti-ban me-1"></i> Recusa
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="devolucao-tab" data-bs-toggle="tab" data-bs-target="#devolucao" type="button" role="tab">
                    <i class="ti ti-refresh me-1"></i> Devolução
                  </button>
                </li>
              </ul>

              <!-- TAB CONTENT -->
              <div class="tab-content p-4" id="returnProcessTabContent">

                {{-- ================= TAB: RECUSA ================= --}}
                <div class="tab-pane fade show active" id="recusa" role="tabpanel" aria-labelledby="recusa-tab">
                  <form id="form_recusa" class="mt-4 p-4 bg-white rounded-3 shadow-sm border">
                    @include('returnProcess.partials.form-fields', ['context' => 'recusa', 'solicitantes' => $solicitantes])
                  </form>
                </div>

                {{-- ================= TAB: DEVOLUÇÃO ================= --}}
                <div class="tab-pane fade" id="devolucao" role="tabpanel" aria-labelledby="devolucao-tab">
                  <form id="form_devolucao" class="mt-4 p-4 bg-white rounded-3 shadow-sm border">
                    @include('returnProcess.partials.form-fields', ['context' => 'devolucao', 'solicitantes' => $solicitantes])
                  </form>
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

@push('scripts')
  @vite(['resources/js/modules/returnProcess/create.js'])
@endpush
