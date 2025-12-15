document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf();
    const csrf = document.querySelector("meta[name='csrf-token']").content;

    const modalElement = document.getElementById("stepModal");
    const stepModal = new bootstrap.Modal(modalElement);

    const form = document.getElementById("stepForm");

    // Campos
    const titleEl = document.getElementById("stepModalTitle");
    const methodEl = document.getElementById("stepMethod");
    const templateEl = document.getElementById("stepTemplateId");

    const nameEl = document.getElementById("stepName");
    const orderEl = document.getElementById("stepOrder");
    const sectorEl = document.getElementById("stepSector");
    const levelEl = document.getElementById("stepLevel");
    const notifyEl = document.getElementById("stepAutoNotify");

    // ============================================================
    // 📌 1. Abrir modal para CRIAR STEP
    // ============================================================
    document.addEventListener("click", async e => {
        const btn = e.target.closest(".btn-create-step");
        if (!btn) return;

        const templateId = btn.dataset.template;

        // Modo CREATE
        form.reset();
        form.action = "/admin/workflows/add-step";
        methodEl.value = "POST";

        titleEl.innerHTML = `<i class="ti ti-plus"></i> Novo Step`;

        templateEl.value = templateId;

        // Carrega opções de setor/nível
        await loadStepOptions(templateId);

        stepModal.show();
    });

    // ============================================================
    // 📌 2. Abrir modal para EDITAR STEP
    // ============================================================
    document.addEventListener("click", async e => {
        const btn = e.target.closest(".btn-edit-step");
        if (!btn) return;

        const id = btn.dataset.id;

        // Carrega os dados do step
        const res = await fetch(`/admin/workflows/step/${id}/edit`);
        const json = await res.json();

        const s = json.step;

        // Carrega opções de setor/nível
        await loadStepOptions(s.workflow_template_id);

        // Modo EDITAR
        titleEl.innerHTML = `<i class="ti ti-edit"></i> Editar Step`;

        form.action = `/admin/workflows/step/${id}`;
        methodEl.value = "PUT";

        templateEl.value = s.workflow_template_id;
        nameEl.value = s.name;
        orderEl.value = s.order;
        sectorEl.value = s.sector_id;
        levelEl.value = s.required_level_id;
        notifyEl.value = s.auto_notify ? 1 : 0;

        stepModal.show();
    });

    // ============================================================
    // 📌 3. Submit AJAX (CREATE + EDIT)
    // ============================================================
    form.addEventListener("submit", async e => {
        e.preventDefault();

        const formData = new FormData(form);

        const res = await fetch(form.action, {
            method: methodEl.value,
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            body: formData
        });

        const json = await res.json();

        if (!res.ok || !json.success) {
            notyf.error(json.message || "Erro ao salvar step.");
            return;
        }

        notyf.success(json.message);

        stepModal.hide();

        // Recarregar steps do template
        window.loadSteps(templateEl.value);
    });

});

// ============================================================
// 📌 4. Carregar setores e níveis para o modal
// ============================================================
async function loadStepOptions(templateId) {

    const res = await fetch(`/admin/workflows/${templateId}/steps/options`);
    const json = await res.json();

    const sectorEl = document.getElementById("stepSector");
    const levelEl = document.getElementById("stepLevel");

    sectorEl.innerHTML = "";
    levelEl.innerHTML = "";

    json.sectors.forEach(s => {
        sectorEl.innerHTML += `<option value="${s.id}">${s.name}</option>`;
    });

    json.levels.forEach(l => {
        levelEl.innerHTML += `
            <option value="${l.id}">
                ${l.name} — ${l.sector_name} (L${l.authority_level})
            </option>`;
    });
}
