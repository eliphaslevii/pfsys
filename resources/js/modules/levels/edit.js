document.addEventListener("DOMContentLoaded", () => {

    const modalEl = document.getElementById("editLevelModal");
    if (!modalEl) return;

    const form = modalEl.querySelector("form");
    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.querySelectorAll("[data-edit-url]").forEach(btn => {
        btn.addEventListener("click", async () => {

            if (btn.dataset.modal !== "#editLevelModal") return;

            try {
                const res = await fetch(btn.dataset.editUrl, {
                    headers: { "Accept": "application/json" }
                });

                const json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.message);

                const level = json.level;

                form.action = `/admin/levels/${level.id}`;
                form.querySelector('[name="name"]').value = level.name;
                form.querySelector('[name="authority_level"]').value = level.authority_level;

                modal.show();

            } catch (err) {
                console.error(err);
                notyf.error("Erro ao carregar dados do nível.");
            }
        });
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = form.querySelector("button[type='submit']");
        btn.disabled = true;

        const fd = new FormData(form);
        fd.append("_method", "PATCH");

        try {
            const res = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "Accept": "application/json"
                },
                body: fd
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                if (res.status === 422) {
                    notyf.error(Object.values(json.errors)[0][0]);
                } else {
                    notyf.error(json.message);
                }
                btn.disabled = false;
                return;
            }

            notyf.success(json.message || "Nível atualizado!");

            modal.hide();
            setTimeout(() => location.reload(), 300);

        } catch (err) {
            console.error(err);
            notyf.error("Erro inesperado.");
        }

        btn.disabled = false;
    });
});
