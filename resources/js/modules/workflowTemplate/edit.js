document.addEventListener("DOMContentLoaded", () => {

    const notyf = new Notyf();
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll("[id^='editTemplateModal_']").forEach(modalEl => {

        const form = modalEl.querySelector("form");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const data = new FormData(form);

            const res = await fetch(form.action, {
                method: "POST", // Laravel entende o @method('PUT')
                headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
                body: data
            });

            const json = await res.json();

            if (!json.success) {
                notyf.error(json.message || "Erro ao salvar.");
                return;
            }

            notyf.success("Fluxo atualizado!");

            bootstrap.Modal.getInstance(modalEl).hide();

            setTimeout(() => location.reload(), 300);
        });

    });

});
