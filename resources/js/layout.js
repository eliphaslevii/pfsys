// ================================================================
// LAYOUT.JS — ARQUITETURA UNIFICADA (UDHA)
// ================================================================
document.addEventListener('DOMContentLoaded', () => {

    console.log('⚙️ layout.js (unificado) carregado');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const notyf = new Notyf({ duration: 2500, position: { x: 'right', y: 'top' } });

    // ================================================================
    // 📱 MENU MOBILE
    // ================================================================
    const sidebar = document.querySelector('aside.navbar-vertical');
    const toggler = document.getElementById('menu-toggler');

    function openDrawer() {
        sidebar.classList.add('show');
        const backdrop = document.createElement('div');
        backdrop.className = 'drawer-backdrop';
        backdrop.id = 'drawerBackdrop';
        document.body.appendChild(backdrop);
        backdrop.addEventListener('click', closeDrawer);
    }

    function closeDrawer() {
        sidebar.classList.remove('show');
        document.getElementById('drawerBackdrop')?.remove();
    }

    toggler?.addEventListener('click', () =>
        sidebar.classList.contains('show') ? closeDrawer() : openDrawer()
    );

    // ================================================================
    // 🧭 SUBMENUS
    // ================================================================
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        toggle.addEventListener('click', e => {
            e.preventDefault();
            const parent = toggle.closest('.has-submenu');
            const submenu = parent.querySelector('.submenu');
            submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        });
    });

    // ================================================================
    // 🗑️ MODAL UNIVERSAL DE DELETE
    // ================================================================
    const deleteModalEl = document.getElementById('confirmDeleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let deleteUrl = null;
    let deleteTemplateId = null; // ← somente p/ STEPS

    const unifiedSelectors = `
        .btn-delete-user,
        .btn-delete-sector,
        .btn-delete-reason,
        .btn-delete-template,
        .btn-delete-step,
        .btn-delete-level
    `;

    if (deleteModalEl && confirmDeleteBtn) {

        const deleteModal = new bootstrap.Modal(deleteModalEl);

        // CAPTURA DE CLIQUE
        document.addEventListener('click', e => {
            const btn = e.target.closest(unifiedSelectors);
            if (!btn) return;

            e.preventDefault();

            deleteUrl = btn.dataset.url;
            deleteTemplateId = btn.dataset.template || null;

            if (!deleteUrl) {
                console.error('❌ Botão delete sem data-url:', btn);
                notyf.error('Erro interno.');
                return;
            }

            deleteModal.show();
        });

        // CONFIRMAR EXCLUSÃO
        confirmDeleteBtn.addEventListener('click', async () => {
            if (!deleteUrl) return;

            confirmDeleteBtn.disabled = true;

            try {
                const res = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await res.json().catch(() => null);

                if (!res.ok || !data?.success) {
                    notyf.error(data?.message || 'Erro ao excluir.');
                    confirmDeleteBtn.disabled = false;
                    return;
                }

                notyf.success(data.message || 'Excluído!');

                deleteModal.hide();

                // STEP EXCLUÍDO → recarregar steps
                if (deleteTemplateId) {
                    document.dispatchEvent(new CustomEvent('step-deleted', {
                        detail: { templateId: deleteTemplateId }
                    }));
                } else {
                    // RESTO → reload da página
                    setTimeout(() => location.reload(), 300);
                }

            } catch (err) {
                console.error(err);
                notyf.error('Erro de comunicação.');
            }

            confirmDeleteBtn.disabled = false;
            deleteUrl = null;
            deleteTemplateId = null;
        });

    }

});
/* ============================================================
   🌐 FUNÇÕES GLOBAIS — DISPONÍVEIS EM TODO O SISTEMA
   ============================================================ */

/**
 * 🔵 Modal de Confirmação Reutilizável
 * Chamado como:
 *    confirmDialog("Título", "Mensagem", () => { ... ação ... })
 */
window.confirmDialog = function (title, message, onConfirm) {

    const modalEl = document.getElementById("modal-confirm");

    // fallback — caso modal não exista, usa confirm nativo
    if (!modalEl) {
        if (confirm(message)) onConfirm();
        return;
    }

    // popula conteúdo
    document.getElementById("modalConfirmTitle").innerText = title;
    document.getElementById("modalConfirmMessage").innerText = message;

    // limpar event listeners antigos
    const yesBtn = document.getElementById("modalConfirmYes");
    const newBtn = yesBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(newBtn, yesBtn);

    // ação
    newBtn.addEventListener("click", () => {
        bootstrap.Modal.getInstance(modalEl)?.hide();
        onConfirm();
    });

    // mostrar modal
    new bootstrap.Modal(modalEl).show();
};


/**
 * 🔵 Loader Global (para telas, tabelas ou botões)
 * Exemplo:
 *    loader.show("Carregando...");
 *    loader.hide();
 */
window.loader = {
    show(message = "Carregando...") {
        let box = document.getElementById("global-loader");
        if (!box) {
            box = document.createElement("div");
            box.id = "global-loader";
            box.style = `
                position: fixed; inset: 0; 
                background: rgba(0,0,0,0.35);
                display: flex; align-items: center; justify-content: center;
                z-index: 9999;
            `;
            box.innerHTML = `
                <div class="p-4 bg-white rounded shadow text-center">
                    <div class="spinner-border text-primary mb-2"></div>
                    <div>${message}</div>
                </div>
            `;
            document.body.appendChild(box);
        }
        box.style.display = "flex";
    },
    hide() {
        const box = document.getElementById("global-loader");
        if (box) box.style.display = "none";
    }
};

console.log("🌐 layout.js carregado — Funções globais disponíveis.");
