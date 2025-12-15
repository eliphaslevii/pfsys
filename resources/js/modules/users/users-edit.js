document.addEventListener("DOMContentLoaded", () => {

    const modalEl = document.getElementById("editUserModal");
    const form = document.getElementById("editUserForm");
    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!modalEl || !form) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    /* ============================================================
       1️⃣ ABRIR MODAL DE EDIÇÃO
    ============================================================ */
    document.querySelectorAll("[data-edit-url]").forEach(btn => {
        btn.addEventListener("click", async () => {

            const url = btn.dataset.editUrl;

            try {
                const res = await fetch(url, { headers: { "Accept": "application/json" } });
                const json = await res.json();

                if (!res.ok || !json.success) throw new Error(json.message);

                const { user, sectors, levels } = json;

                // action correta
                form.action = `/admin/users/${user.id}`;

                // preencher campos
                form.querySelector("#editName").value = user.name;
                form.querySelector("#editEmail").value = user.email;
                form.querySelector("#editActive").checked = user.active == 1;

                const sector = form.querySelector("#editSector");
                sector.innerHTML = "";
                sectors.forEach(s => {
                    sector.insertAdjacentHTML("beforeend", `
                        <option value="${s.id}" ${user.sector_id == s.id ? "selected" : ""}>${s.name}</option>
                    `);
                });

                const level = form.querySelector("#editLevel");
                level.innerHTML = "";
                levels.forEach(l => {
                    level.insertAdjacentHTML("beforeend", `
                        <option value="${l.id}" ${user.level_id == l.id ? "selected" : ""}>
                            ${l.name}
                        </option>
                    `);
                });

                modal.show();

            } catch (err) {
                console.error(err);
                notyf.error("Erro ao carregar dados.");
            }

        });
    });

    /* ============================================================
       2️⃣ SUBMIT DO PATCH
    ============================================================ */
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector("button[type='submit']");
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append("_method", "PATCH");

        try {
            const res = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "Accept": "application/json"
                }
            });

            const json = await res.json();
            if (!res.ok || !json.success) {

                if (res.status === 422) {
                    const msg = Object.values(json.errors)[0][0];
                    notyf.error(msg);
                } else {
                    notyf.error(json.message || "Erro ao salvar.");
                }

                submitBtn.disabled = false;
                return;
            }

            notyf.success("Usuário atualizado!");
            modal.hide();
            setTimeout(() => location.reload(), 300);

        } catch (err) {
            notyf.error("Erro inesperado.");
        }

        submitBtn.disabled = false;
    });

});
