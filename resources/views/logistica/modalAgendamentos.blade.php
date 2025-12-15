
{{-- ========================================================= --}}
{{-- MODAL + SCRIPT UNIFICADO – AGENDAMENTO LOGÍSTICA --}}
{{-- ========================================================= --}}

{{-- MODAL --}}
<div class="modal fade" id="modal-email" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Enviar Agendamento de Coleta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Destinatário (Para)</label>
                    <input type="email" id="email-to" class="form-control" placeholder="email@transportadora.com">
                </div>

                <div class="mb-2">
                    <label class="form-label">CC</label>

                    <div id="cc-area">
                        <input type="email" class="form-control mb-2 cc-input" placeholder="email em cópia">
                    </div>

                    <button type="button" id="btn-add-cc" class="btn btn-sm btn-outline-secondary">
                        + Adicionar CC
                    </button>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button id="btn-confirmar-envio" class="btn btn-primary">
                    Confirmar e Enviar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const btnCriar = document.getElementById('btn-criar');
    const modalEl = document.getElementById('modal-email');
    const modal = new bootstrap.Modal(modalEl);

    const btnAddCc = document.getElementById('btn-add-cc');
    const ccArea = document.getElementById('cc-area');
    const btnConfirmar = document.getElementById('btn-confirmar-envio');

    // ==========================
    // ABRIR MODAL
    // ==========================
    btnCriar.addEventListener('click', () => {
        const selecionadas = document.querySelectorAll('.nf-check:checked');

        if (selecionadas.length === 0) {
            alert('Selecione ao menos uma NF-e.');
            return;
        }

        modal.show();
    });

    // ==========================
    // ADICIONAR CC
    // ==========================
    btnAddCc.addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = 'email';
        input.className = 'form-control mb-2 cc-input';
        input.placeholder = 'email em cópia';
        ccArea.appendChild(input);
    });

    // ==========================
    // CONFIRMAR ENVIO
    // ==========================
    btnConfirmar.addEventListener('click', async () => {

        const transportadora = document.getElementById('transportadora-select').value;
        const to = document.getElementById('email-to').value.trim();
        const cc = Array.from(document.querySelectorAll('.cc-input'))
            .map(i => i.value)
            .filter(v => v);

        const nfes = Array.from(document.querySelectorAll('.nf-check:checked'))
            .map(el => el.value);

        if (!to) {
            alert('Informe o destinatário.');
            return;
        }

        btnConfirmar.disabled = true;
        btnConfirmar.innerText = 'Enviando...';

        try {
            const res = await fetch('/logistica/agendamentos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    transportadora,
                    nfes,
                    email_to: to,
                    email_cc: cc
                })
            });

            const json = await res.json();

            if (!res.ok || !json.ok) {
                throw json;
            }

            modal.hide();

            alert('Agendamento criado e e-mail enviado com sucesso.');

            if (json.download_url) {
                window.open(json.download_url, '_blank');
            }

            location.reload();

        } catch (err) {
            console.error(err);
            alert('Erro ao criar agendamento.');
        } finally {
            btnConfirmar.disabled = false;
            btnConfirmar.innerText = 'Confirmar e Enviar';
        }
    });

});
</script>
