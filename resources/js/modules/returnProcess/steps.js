document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf();

    document.querySelectorAll(".btn-expand-steps").forEach(btn => {

        btn.addEventListener("click", async () => {

            const tplId = btn.dataset.template;
            const row = document.getElementById(`stepsRow_${tplId}`);
            const area = document.getElementById(`stepsArea_${tplId}`);

            // Toggle visual
            const isVisible = row.style.display === "table-row";
            row.style.display = isVisible ? "none" : "table-row";

            // Se já carregou uma vez, não recarregar
            if (area.dataset.loaded === "1") return;

            // loading
            area.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 text-muted">Carregando etapas…</div>
                </div>
            `;

            try {
                const res = await fetch(`/admin/workflows/${tplId}/steps`, {
                    headers: { "Accept": "application/json" }
                });

                const json = await res.json();

                if (!json.success) {
                    notyf.error("Erro ao carregar steps.");
                    return;
                }

                area.dataset.loaded = "1";
                area.innerHTML = buildStepsTable(json.steps);

            } catch (err) {
                console.error(err);
                notyf.error("Erro ao buscar etapas.");
            }

        });
    });

});


function buildStepsTable(steps) {

    if (!steps.length) {
        return `
            <div class="alert alert-warning">Nenhuma etapa cadastrada ainda.</div>
        `;
    }

    let html = `
        <table class="table table-sm table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Setor</th>
                    <th>Nível Obrigatório</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
    `;

    steps.forEach(s => {
        html += `
            <tr>
                <td>${s.order}</td>
                <td>${s.name}</td>
                <td>${s.sector_name}</td>
                <td>${s.required_level_name}</td>

                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-step"
                        data-id="${s.id}">
                        <i class="ti ti-edit"></i>
                    </button>

                    <button class="btn btn-sm btn-outline-danger btn-delete-step"
                        data-id="${s.id}">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += `</tbody></table>`;
    return html;
}
