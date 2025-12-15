document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("#modal-level form");
    if (!form) return;

    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = form.querySelector("button[type='submit']");
        btn.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: { "Accept": "application/json" }
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                if (res.status === 422) {
                    notyf.error(Object.values(json.errors)[0][0]);
                } else {
                    notyf.error(json.message || "Erro ao criar nível.");
                }
                btn.disabled = false;
                return;
            }

            notyf.success(json.message || "Nível criado!");

            bootstrap.Modal.getInstance(document.getElementById("modal-level")).hide();

            setTimeout(() => location.reload(), 300);

        } catch (err) {
            console.error(err);
            notyf.error("Erro inesperado.");
        }

        btn.disabled = false;
    });

});
