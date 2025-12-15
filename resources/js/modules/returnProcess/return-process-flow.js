import { apiFetch, notyf } from "./return-process-utils.js";

/**
 * =============================================
 *  🟦 AVANÇAR ETAPA DO PROCESSO
 * =============================================
 */


async function approveProcess(id) {

    loader.show("Avançando etapa...");

    const json = await apiFetch(`/return-process-flow/${id}/advance`, "POST");

    loader.hide();

    if (json.success) {
        notyf.success(json.message);

        // Atualiza tabela
        if (window.loadProcesses) window.loadProcesses();

        // Atualiza modal se estiver aberto
        if (window.openProcessModal) window.openProcessModal(id);

    } else {
        notyf.error(json.message || "Erro ao avançar etapa.");
    }
}

// expor ao global
window.approveProcess = approveProcess;


/**
 * =============================================
 *  TIMELINE
 * =============================================
 */
window.loadTimeline = async function (id) {

    const box = document.querySelector("#timeline-box");
    if (!box) return;

    box.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary"></div>
            Carregando histórico...
        </div>
    `;

    const json = await apiFetch(`/return-process-flow/${id}/timeline`);

    if (!json.success) {
        box.innerHTML = `<div class="text-danger">Erro ao carregar timeline</div>`;
        return;
    }

    if (!json.timeline.length) {
        box.innerHTML = `<div class="text-muted">Nenhum histórico encontrado.</div>`;
        return;
    }

    box.innerHTML = json.timeline
        .map(t => `
            <div class="border-bottom py-2">
                <b>${t.workflow_step?.name ?? "Etapa"}</b><br>
                <small class="text-muted">${t.created_at}</small><br>
                <span>${t.user?.name ?? "Sistema"}</span>
            </div>
        `)
        .join("");
};
