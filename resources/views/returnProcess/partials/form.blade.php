<div class="row justify-content-center">
    <div class="col-12 col-xl-12"> {{-- Largura controlada para leitura --}}

        <div class="card ">
            
       

            {{-- 2. ICON TAB BAR (Navegação) --}}
            <ul class="nav nav-tabs fiori-tabs" id="returnProcessTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="recusa-tab" 
                            data-bs-toggle="tab" data-bs-target="#recusa" type="button" role="tab">
                        <i class="ti ti-ban me-2"></i>Recusa
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="devolucao-tab" 
                            data-bs-toggle="tab" data-bs-target="#devolucao" type="button" role="tab">
                        <i class="ti ti-refresh me-2"></i>Devolução
                    </button>
                </li>
            </ul>

            {{-- 3. CONTENT AREA --}}
            <div class="card-body p-0">
                <div class="tab-content" id="returnProcessTabContent">

                    {{-- TAB: RECUSA --}}
                    <div class="tab-pane fade show active" id="recusa" role="tabpanel">
                        <form id="form_recusa" class="p-4 p-md-5">
                            {{-- Info Bar Discreto --}}
                            <div class="d-flex align-items-center mb-4 text-muted">
                                <i class="ti ti-info-circle me-2"></i>
                                <span class="small">Preencha os dados abaixo para registrar uma recusa de mercadoria no ato da entrega.</span>
                            </div>

                            @include('returnProcess.partials.form-fields', ['context' => 'recusa', 'solicitantes' => $solicitantes])
                        </form>
                    </div>

                    {{-- TAB: DEVOLUÇÃO --}}
                    <div class="tab-pane fade" id="devolucao" role="tabpanel">
                        <form id="form_devolucao" class="p-4 p-md-5">
                            <div class="d-flex align-items-center mb-4 text-muted">
                                <i class="ti ti-info-circle me-2"></i>
                                <span class="small">Preencha os dados para registrar uma devolução comercial de itens já recebidos.</span>
                            </div>

                            @include('returnProcess.partials.form-fields', ['context' => 'devolucao', 'solicitantes' => $solicitantes])
                        </form>
                    </div>

                </div>
            </div>

            {{-- 4. FOOTER TOOLBAR --}}
            <div class="fiori-footer">
                {{-- Botão Cancelar (Ghost) --}}
                <button type="button" class="btn btn-fiori-ghost px-3 me-2" onclick="window.history.back()">
                    Cancelar
                </button>
                
                {{-- Botão Ação Principal --}}
                <button class="btn btn-fiori-primary px-4" id="sendApprovRequest">
                    Salvar Processo
                </button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
  @vite(['resources/js/modules/returnProcess/create.js'])
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnSave = document.getElementById('sendApprovRequest');
        if(btnSave){
            btnSave.addEventListener('click', (e) => {
                e.preventDefault();
                const activeTab = document.querySelector('.tab-pane.active');
                if(!activeTab) return;
                const form = activeTab.querySelector('form');
                if(form) form.requestSubmit();
            });
        }
    });
  </script>
@endpush