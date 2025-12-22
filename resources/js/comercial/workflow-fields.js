/* =========================================================
 * WORKFLOW FIELDS — BASEADO NO NOME DA ETAPA
 * ========================================================= */

const workflowFields = {
    'Comercial (Refaturamento)': {
        fields: [
            { id: 'delivery', label: 'Delivery', required: true }
        ]
    },

    'Fiscal': {
        fields: [
            { id: 'doc_faturamento', label: 'Doc. Faturamento', required: true },
            { id: 'ordem_entrada', label: 'Ordem de Entrada', required: true }
        ]
    },

    'Logística': {
        fields: [
            { id: 'migo', label: 'MIGO', required: true }
        ]
    },

    'Contas a Pagar': {
        fields: [] // apenas observação / email depois
    }
};

/* =========================================================
 * VARIÁVEIS DE CONTROLE
 * ========================================================= */

let processToAdvance = null;
let currentStepName = null;

/* =========================================================
 * BOTÃO AVANÇAR — ABRE MODAL
 * ========================================================= */

document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-advance');
    if (!btn) return;

    processToAdvance = btn.dataset.id;
    currentStepName  = btn.dataset.step;

    montarModalEtapa(currentStepName);

    new bootstrap.Modal(
        document.getElementById('modal-advance')
    ).show();
});

/* =========================================================
 * MONTA CAMPOS DO MODAL
 * ========================================================= */

function montarModalEtapa(stepName) {
    const container = document.getElementById('fieldsContainer');
    container.innerHTML = '';

    const config = workflowFields[stepName];

    if (!config || !config.fields.length) {
        container.innerHTML = `
            <p class="text-muted">
                Nenhuma informação adicional é necessária para esta etapa.
            </p>
        `;
        return;
    }

    config.fields.forEach(f => {
        container.insertAdjacentHTML('beforeend', `
            <div class="mb-3">
                <label class="form-label">${f.label}</label>
                <input
                    type="text"
                    class="form-control"
                    id="field_${f.id}"
                    ${f.required ? 'required' : ''}
                >
            </div>
        `);
    });
}

/* =========================================================
 * CONFIRMAR AVANÇO — ENVIA PAYLOAD
 * ========================================================= */

document.getElementById('btnConfirmAdvance')
    .addEventListener('click', () => {

        const payload = {};
        const config = workflowFields[currentStepName];

        if (config) {
            for (const f of config.fields) {
                const el = document.getElementById(`field_${f.id}`);
                const value = el?.value?.trim();

                if (f.required && !value) {
                    el.focus();
                    return notyf.error(`Campo "${f.label}" é obrigatório.`);
                }

                payload[f.id] = value;
            }
        }

        fetch(`/comercial/processes/${processToAdvance}/advance`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async r => {
            const data = await r.json();
            if (!r.ok) throw data;
            return data;
        })
        .then(res => {
            notyf.success(res.message || 'Etapa avançada com sucesso');
            setTimeout(() => location.reload(), 300);
        })
        .catch(err => {
            notyf.error(err?.message || 'Erro ao avançar etapa');
        });
    });

/* =========================================================
 * RENDER BOTÃO AVANÇAR NA TABELA
 * ========================================================= */

function renderAdvanceButton(p) {
    if (!p.current_step || !p.can_advance) return '';

    return `
        <button
            class="btn btn-sm btn-warning btn-advance"
            data-id="${p.id}"
            data-step="${p.current_step}"
            title="Avançar etapa">
            <i class="ti ti-arrow-right"></i>
        </button>
    `;
}
