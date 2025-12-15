document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const modalEl = document.getElementById("confirmDeleteReasonModal");
    const modal = new bootstrap.Modal(modalEl);

    let deleteUrl = null; // URL atual a ser deletada

    // ----------------------------------------------------
    // ABRIR MODAL AO CLICAR NO BOTÃO DE EXCLUSÃO
    // ----------------------------------------------------
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-delete-reason");
        if (!btn) return;

        deleteUrl = btn.dataset.url; // salva URL

        modal.show(); // abre modal
    });

    // ----------------------------------------------------
    // CONFIRMAR EXCLUSÃO
    // ----------------------------------------------------
    document.getElementById("confirmDeleteReasonBtn").addEventListener("click", async () => {

        if (!deleteUrl) return;

        try {
            const res = await fetch(deleteUrl, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "Accept": "application/json"
                }
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                notyf.error(json.message || "Erro ao excluir motivo.");
                return;
            }

            notyf.success(json.message || "Motivo excluído com sucesso!");

            modal.hide();

            setTimeout(() => location.reload(), 300);

        } catch (err) {
            console.error(err);
            notyf.error("Erro inesperado.");
        }
    });

});
