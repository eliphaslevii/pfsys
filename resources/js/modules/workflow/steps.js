document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf();
    const csrf = document.querySelector("meta[name=csrf-token]").content;
    const stepModal = new bootstrap.Modal(document.getElementById("stepModal"));

    // Expor loadSteps globalmente para o layout.js
    window.loadSteps = loadSteps;

    // ----------------------------------------------------
    // EXPANDIR STEPS
    // ----------------------------------------------------
    document.querySelectorAll(".btn-expand-steps").forEach(btn => {
        btn.addEventListener("click", async () => {
            const id = btn.dataset.template;
            const row = document.getElementById(`stepsRow_${id}`);

            if (row.style.display === "none") {
                row.style.display = "";
                loadSteps(id);
            } else {
                row.style.display = "none";
            }
        });
    });

    // ----------------------------------------------------
    // RECARREGAR STEPS via EVENTO GLOBAL
    // ----------------------------------------------------
    document.addEventListener('step-deleted', e => {
        const id = e.detail.templateId;
        loadSteps(id);
    });

    // ----------------------------------------------------
    // CARREGAR LISTA DE STEPS
    // ----------------------------------------------------
    async function loadSteps(templateId) {
        const area = document.getElementById(`stepsArea_${templateId}`);
        area.innerHTML = `<div class="text-center py-3 text-muted">Carregando…</div>`;

        const res = await fetch(`/admin/workflows/${templateId}/steps`, {
            headers: { "Accept": "application/json" }
        });

        const json = await res.json();

        if (!json.success) {
            area.innerHTML = `<div class="text-danger">Erro ao carregar steps.</div>`;
            return;
        }

        let html = `
            <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Etapa</th>
                    <th>Setor</th>
                    <th>Nível</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
        `;

        json.steps.forEach(step => {
            html += `
                <tr>
                    <td>${step.order}</td>
                    <td>${step.name}</td>
                    <td>${step.sector_name}</td>
                    <td>${step.required_level_name}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1 btn-edit-step"
                                data-id="${step.id}">
                            <i class="ti ti-edit"></i>
                        </button>

                        <button 
                            class="btn btn-sm btn-outline-danger btn-delete-step"
                            data-url="/admin/workflows/step/${step.id}"
                            data-template="${templateId}"
                        >
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        area.innerHTML = html;
    }

    // ----------------------------------------------------
    // EDITAR STEP
    // ----------------------------------------------------
    document.addEventListener("click", async e => {
        const btn = e.target.closest(".btn-edit-step");
        if (!btn) return;

        const id = btn.dataset.id;
        const res = await fetch(`/admin/workflows/step/${id}/edit`);
        const json = await res.json();
        const s = json.step;

        document.getElementById("stepForm").action = `/admin/workflows/step/${id}`;
        document.getElementById("stepMethod").value = "PUT";

        await loadOptions(s.workflow_template_id);

        document.getElementById("stepTemplateId").value = s.workflow_template_id;
        document.getElementById("stepName").value = s.name;
        document.getElementById("stepOrder").value = s.order;
        document.getElementById("stepSector").value = s.sector_id ?? "";
        document.getElementById("stepLevel").value = s.required_level_id ?? "";
        document.getElementById("stepAutoNotify").value = s.auto_notify ? 1 : 0;

        document.getElementById("stepModalTitle").innerHTML =
            `<i class="ti ti-edit"></i> Editar Etapa`;

        stepModal.show();
    });

    // ----------------------------------------------------
    // CARREGAR OPÇÕES
    // ----------------------------------------------------
    async function loadOptions(templateId) {
        const res = await fetch(`/admin/workflows/${templateId}/steps/options`);
        const json = await res.json();

        const selSector = document.getElementById("stepSector");
        const selLevel = document.getElementById("stepLevel");

        selSector.innerHTML = "";
        selLevel.innerHTML = "";

        json.sectors.forEach(s => selSector.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        json.levels.forEach(l => selLevel.innerHTML += `
            <option value="${l.id}">${l.name} — ${l.sector_name} (L${l.authority_level})</option>
        `);
    }
});
