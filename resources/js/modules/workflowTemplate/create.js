document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf();
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const modal = document.getElementById("createTemplateModal");
    const form = modal.querySelector("form");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = new FormData(form);

        const res = await fetch(form.action, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
            body: data
        });

        const json = await res.json();

        if (!json.success) {
            notyf.error(json.message || "Erro ao criar fluxo.");
            return;
        }

        notyf.success("Fluxo criado com sucesso!");

        bootstrap.Modal.getInstance(modal).hide();

        setTimeout(() => location.reload(), 300);
    });

});
