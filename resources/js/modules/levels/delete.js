document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.btn-delete-level').forEach(btn => {
        btn.addEventListener("click", async (e) => {

            if (!btn.dataset.url.includes("/levels/")) return;

            if (!confirm("Deseja realmente excluir este nível?")) return;

            try {
                const res = await fetch(btn.dataset.url, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        "Accept": "application/json"
                    }
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    notyf.error(json.message);
                    return;
                }

                notyf.success(json.message);

                setTimeout(() => location.reload(), 250);

            } catch (err) {
                console.error(err);
                notyf.error("Erro inesperado.");
            }
        });
    });

});
