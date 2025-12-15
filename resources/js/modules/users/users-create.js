document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("#modal-register form");
    if (!form) return;

    const notyf = new Notyf({ duration: 3000, position: { x: "right", y: "top" } });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector("button[type='submit']");
        submitBtn.disabled = true;

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: { "Accept": "application/json" }
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                if (res.status === 422) {
                    const msg = Object.values(json.errors)[0][0];
                    notyf.error(msg);
                } else {
                    notyf.error(json.message || "Erro ao criar usuário.");
                }
                submitBtn.disabled = false;
                return;
            }

            notyf.success("Usuário criado!");

            bootstrap.Modal.getInstance(document.getElementById("modal-register")).hide();

            setTimeout(() => location.reload(), 300);

        } catch (err) {
            notyf.error("Erro inesperado.");
        }

        submitBtn.disabled = false;
    });

});
