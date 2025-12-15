import { apiFetch, formatMoney, notyf } from "./return-process-utils.js";

// Mapeamento de regras de campos por etapa (Espelho do PHP Controller)
const STEP_RULES = {
    'Comercial (Refaturamento)': ['delivery'],
    'Financeiro': ['doc_faturamento', 'ordem_entrada'],
    'Financeiro (Pós-Logística)': ['doc_faturamento', 'ordem_entrada'], // *Delivery condicional verificado na lógica
    'Logística': ['migo'],
    'Logística (Refaturamento)': ['migo']
};

// Motivos que exigem Delivery no Financeiro (Espelho do PHP Controller)
const FINANCEIRO_MOTIVOS_EXTRA = [
    "Material Descartado",
    "Devolução + sucateamento"
];

document.addEventListener("DOMContentLoaded", () => {
    console.log("🔥 return-process-ui.js carregado");

    const tableBody = document.querySelector("#processTableBody");
    const btnRefresh = document.querySelector("#btnRefresh");
    
    // Modal de Visualização
    const modalViewEl = document.getElementById("modal-process-view");
    const modalView = new bootstrap.Modal(modalViewEl);
    const modalViewBody = document.getElementById("modalProcessBody");

    /**
     * ============================================
     * 🟦 CARREGAR TABELA
     * ============================================
     */
    window.loadProcesses = async function () {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-3"><div class="spinner-border text-primary"></div> Carregando...</td></tr>`;

        const json = await apiFetch("/return-process/data");

        if (!json?.data) {
            tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">Erro ao carregar processos.</td></tr>`;
            return;
        }

        renderTable(json.data);
    };

    function renderTable(data) {
        tableBody.innerHTML = data.map(p => `
            <tr>
                <td>${p.id}</td>
                <td>${p.tipo}</td>
                <td>${p.cliente}</td>
                <td>${p.cnpj}</td>
                <td>${p.motivo}</td> 
                <td>${p.codigoErro}</td> 
                <td><span class="fw-semibold"><i class="ti ti-circle-filled me-1"></i>${p.status ?? "-"}</span></td>
                <td>${p.etapa ?? "-"}</td>
                <td>${p.responsavel ?? "-"}</td>
                <td>${p.created_at ?? "-"}</td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary btn-view" data-id="${p.id}"><i class="ti ti-eye"></i></button>
                        ${window.authUser.permissions.includes("process.approve") 
                            ? `<button class="btn btn-sm btn-outline-success btn-approve" data-id="${p.id}" data-etapa="${p.etapa}" data-motivo="${p.motivo}"><i class="ti ti-arrow-right"></i></button>` 
                            : ""}
                        ${window.authUser.permissions.includes("process.delete") 
                            ? `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${p.id}"><i class="ti ti-trash"></i></button>` 
                            : ""}
                    </div>
                </td>
            </tr>
        `).join('');

        attachActions();
    }

    /**
     * ============================================
     * 🟦 BIND DE AÇÕES NA TABELA
     * ============================================
     */
    function attachActions() {
        document.querySelectorAll(".btn-view").forEach(btn => {
            btn.addEventListener("click", () => openProcessModal(btn.dataset.id));
        });

        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", () => deleteProcessHandler(btn.dataset.id));
        });

        // Novo Handler para Aprovação
        document.querySelectorAll(".btn-approve").forEach(btn => {
            btn.addEventListener("click", () => {
                // Passamos os dados necessários para decidir quais inputs mostrar
                initiateApproval(btn.dataset.id, btn.dataset.etapa, btn.dataset.motivo);
            });
        });
    }

    /**
     * ============================================
     * 🟩 LÓGICA DE APROVAÇÃO (NOVO)
     * ============================================
     */
    function initiateApproval(id, currentStep, motivo) {
        // Identificar campos necessários
        let requiredFields = STEP_RULES[currentStep] || [];

        // Regra específica do Financeiro Pós-Logística
        if (currentStep === 'Financeiro (Pós-Logística)' && FINANCEIRO_MOTIVOS_EXTRA.includes(motivo)) {
            if (!requiredFields.includes('delivery')) {
                requiredFields.push('delivery');
            }
        }

        // Gerar HTML do formulário dinâmico
        const formHtml = generateApprovalForm(requiredFields);

        // Injetar Modal Dinâmico no DOM (se não existir) ou atualizar existente
        let actionModalEl = document.getElementById('dynamic-action-modal');
        if (!actionModalEl) {
            document.body.insertAdjacentHTML('beforeend', `
                <div class="modal fade" id="dynamic-action-modal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Avançar Etapa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="dynamic-action-body"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success" id="btn-confirm-advance">Confirmar Avanço</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            actionModalEl = document.getElementById('dynamic-action-modal');
        }

        document.getElementById('dynamic-action-body').innerHTML = formHtml;
        
        const actionModal = new bootstrap.Modal(actionModalEl);
        actionModal.show();

        // Bind do botão de confirmar dentro do modal
        const confirmBtn = document.getElementById('btn-confirm-advance');
        
        // Remove listeners antigos clonando o botão (método rápido de reset)
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        
        newBtn.addEventListener("click", () => submitAdvance(id, actionModal));
    }

    function generateApprovalForm(fields) {
        let html = `<form id="approval-form">`;

        if (fields.includes('delivery')) {
            html += `
                <div class="mb-3">
                    <label class="form-label">Delivery (Começa com 5)</label>
                    <input type="text" name="delivery" class="form-control" placeholder="Ex: 50012345" required>
                </div>`;
        }

        if (fields.includes('doc_faturamento')) {
            html += `
                <div class="mb-3">
                    <label class="form-label">Doc. Faturamento</label>
                    <input type="text" name="doc_faturamento" class="form-control" required>
                </div>`;
        }

        if (fields.includes('ordem_entrada')) {
            html += `
                <div class="mb-3">
                    <label class="form-label">Ordem de Entrada</label>
                    <input type="text" name="ordem_entrada" class="form-control" required>
                </div>`;
        }

        if (fields.includes('migo')) {
            html += `
                <div class="mb-3">
                    <label class="form-label">MIGO</label>
                    <input type="text" name="migo" class="form-control" required>
                </div>`;
        }

        // Observações sempre presentes
        html += `
            <div class="mb-3">
                <label class="form-label">Observações</label>
                <textarea name="observations" class="form-control" rows="2"></textarea>
            </div>
        </form>`;

        return html;
    }

    async function submitAdvance(id, modalInstance) {
        const form = document.getElementById('approval-form');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        const btn = document.getElementById('btn-confirm-advance');

        // Validação básica de Delivery no Front
        if (payload.delivery && !/^5[0-9]*$/.test(payload.delivery)) {
            notyf.error("Delivery deve começar com 5.");
            return;
        }

        // Loading State
        const originalText = btn.innerText;
        btn.innerText = "Processando...";
        btn.disabled = true;

        try {
            const response = await apiFetch(`/return-process-flow/${id}/advance`, "POST", payload);

            if (response.success) {
                notyf.success(response.message);
                modalInstance.hide();
                loadProcesses(); // Recarrega tabela
            } else {
                notyf.error(response.message || "Erro ao avançar etapa.");
            }
        } catch (error) {
            console.error(error);
            notyf.error("Erro de comunicação com o servidor.");
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    }

    /**
     * ============================================
     * 🟦 FUNÇÕES EXISTENTES (VISUALIZAR / DELETAR)
     * ============================================
     */
    window.openProcessModal = async function (id) {
        modalViewBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><br>Carregando dados...</div>`;
        modalView.show();

        const json = await apiFetch(`/return-process/${id}`);
        if (!json.success) {
            modalViewBody.innerHTML = `<div class="alert alert-danger">Erro ao carregar processo.</div>`;
            return;
        }
        
        renderViewModal(json.data);
    };

function renderViewModal(p) {
    const itens = Array.isArray(p.items) ? p.items : [];

    modalViewBody.innerHTML = `
        <div class="container-fluid">

            <div class="p-3 mb-3 rounded border-start border-4 border-primary bg-light">
                <h5 class="fw-bold">Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-6"><b>Cliente:</b> ${p.cliente_nome}</div>
                    <div class="col-md-6"><b>CNPJ:</b> ${p.cliente_cnpj}</div>
                    <div class="col-md-6"><b>Status:</b> ${p.status}</div>
                    <div class="col-md-6"><b>Etapa Atual:</b> ${p.etapa ?? "-"}</div>
                </div>
            </div>

            <div class="p-3 mb-3 rounded border-start border-4 border-success bg-light">
                <h5 class="fw-bold">Informações Fiscais</h5>
                <div class="row">
                    <div class="col-md-6"><b>NF Saída:</b> ${p.nf_saida}</div>
                    <div class="col-md-6"><b>NFD:</b> ${p.nf_devolucao}</div>
                    <div class="col-md-6"><b>NFO:</b> ${p.nfo}</div>
                    <div class="col-md-6"><b>Protocolo:</b> ${p.protocolo}</div>
                    <div class="col-md-12"><b>Recusa SEFAZ:</b> ${p.recusa_sefaz}</div>
                </div>
            </div>

            <div class="p-3 mb-3 rounded border-start border-4 border-warning bg-light">
                <h5 class="fw-bold">Documentos & Logística</h5>
                <div class="row">
                    <div class="col-md-6"><b>Delivery:</b> ${p.delivery ?? "-"}</div>
                    <div class="col-md-6"><b>Doc Faturamento:</b> ${p.doc_faturamento ?? "-"}</div>
                    <div class="col-md-6"><b>Ordem Entrada:</b> ${p.ordem_entrada ?? "-"}</div>
                    <div class="col-md-6"><b>MIGO:</b> ${p.migo ?? "-"}</div>
                </div>
                <b>Observações:</b>
                <div>${p.observacoes ?? "Sem observações."}</div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold">Itens da Nota</h5>
                <button class="btn btn-sm btn-outline-primary toggle-items-btn">
                    <i class="ti ti-chevron-down me-1"></i> Mostrar Itens
                </button>
            </div>

            <div class="table-responsive mt-2 items-container border rounded p-2" style="display:none;">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Artigo</th>
                            <th>Descrição</th>
                            <th>NCM</th>
                            <th>NF Saída</th>
                            <th>NFD</th>
                            <th>Qtd</th>
                            <th>Preço Unit.</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${
                            itens.length
                                ? itens.map(i => `
                                    <tr>
                                        <td>${i.artigo}</td>
                                        <td>${i.descricao}</td>
                                        <td>${i.ncm ?? "-"}</td>
                                        <td>${i.nf_saida ?? "-"}</td>
                                        <td>${i.nf_devolucao ?? "-"}</td>
                                        <td>${i.quantidade}</td>
                                        <td>R$ ${formatMoney(i.preco_unitario)}</td>
                                    </tr>
                                `).join("")
                                : `<tr><td colspan="7" class="text-center text-muted">Nenhum item encontrado.</td></tr>`
                        }
                    </tbody>
                </table>
            </div>
        </div>
    `;

    // 🔥 CORREÇÃO: Agora o botão sempre funciona!
    const btnToggle = modalViewBody.querySelector(".toggle-items-btn");
    const itemsContainer = modalViewBody.querySelector(".items-container");

    btnToggle.addEventListener("click", () => {
        const visible = itemsContainer.style.display === "block";

        itemsContainer.style.display = visible ? "none" : "block";

        btnToggle.innerHTML = `
            <i class="ti ${visible ? "ti-chevron-down" : "ti-chevron-up"} me-1"></i>
            ${visible ? "Mostrar Itens" : "Ocultar Itens"}
        `;
    });
}

    function deleteProcessHandler(id) {
        // Assume que confirmDialog existe globalmente ou em utils
        if(typeof confirmDialog === 'function') {
            confirmDialog("Excluir Processo", "Ação irreversível.", () => performDelete(id));
        } else {
            if(confirm("Tem certeza que deseja excluir?")) performDelete(id);
        }
    }

    async function performDelete(id) {
        const json = await apiFetch(`/return-process/${id}`, "DELETE");
        if (json.success) {
            notyf.success(json.message);
            loadProcesses();
        } else {
            notyf.error(json.message);
        }
    }

    // Inicialização
    btnRefresh.addEventListener("click", loadProcesses);
    loadProcesses();
});