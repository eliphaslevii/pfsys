{{-- Modal de Registro de Nova Devolução --}}
<div class="modal fade" id="modal-register" tabindex="-1" aria-labelledby="modalRegisterLabel" aria-hidden="true">
  {{-- AQUI: Altere modal-lg para modal-xl para largura máxima --}}
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRegisterLabel">Criar Novo Processo de Devolução</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @include('returnProcess.partials.form', ['context' => 'return_process', 'modal' => true])
      </div>
    </div>
  </div>
</div>
<!-- Modal de Visualização de Processo -->
<div class="modal fade" id="modal-process-view" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-primary">Detalhes do Processo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalProcessBody">
        <div class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-3"></div><br>
          Carregando informações...
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-approve" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Confirmar Aprovação</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="approveFields"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" id="btnSubmitApprove">Salvar & Avançar</button>
            </div>

        </div>
    </div>
</div>
