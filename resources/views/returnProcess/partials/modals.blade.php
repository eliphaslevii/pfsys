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