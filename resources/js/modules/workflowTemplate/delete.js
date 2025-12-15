// public/tabler/js/modules/workflowTemplate/delete.js
document.addEventListener("DOMContentLoaded", () => {
  const notyf = new Notyf();
  const csrf = document.querySelector("meta[name='csrf-token']")?.content || null;

  // IDs do modal de confirmação (se você tiver)
  const modalEl = document.getElementById("confirmDeleteTemplateModal");
  const confirmBtn = document.getElementById("confirmDeleteTemplateBtn");

  let pendingDeleteUrl = null;
  let pendingButton = null;

  // Helper para mostrar erro detalhado no console e notyf
  function showError(msg, detail) {
    console.error(msg, detail);
    notyf.error(typeof msg === "string" ? msg : "Erro inesperado.");
  }

  // Delegação de clique: captura cliques no botão (ou no ícone dentro dele)
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-delete-template");
    if (!btn) return;

    // evita que um <form> ancestral dispare submissão
    e.preventDefault();

    // pega a URL certos (dataset ou atributo)
    const url = btn.dataset.url ?? btn.getAttribute("data-url") ?? null;

    if (!url) {
      showError("Botão de exclusão sem url (data-url).", { btn, html: btn.outerHTML });
      return;
    }

    // guarda para uso quando confirmar (modal) ou executar direto
    pendingDeleteUrl = url;
    pendingButton = btn;

    // Se existir modal de confirmação, abre-o. Senão, usa confirm() imediatamente.
    if (modalEl && confirmBtn) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
      // fallback com confirm nativo
      if (!confirm("Excluir fluxo?")) {
        pendingDeleteUrl = null;
        pendingButton = null;
        return;
      }
      doDelete(url, btn);
    }
  });

  // Se tiver modal, ação do botão de confirmar:
  if (confirmBtn) {
    confirmBtn.addEventListener("click", async (ev) => {
      ev.preventDefault();
      if (!pendingDeleteUrl) {
        showError("Url de exclusão não encontrada no momento de confirmar.");
        return;
      }
      // fecha modal imediatamente para feedback rápido
      bootstrap.Modal.getInstance(modalEl).hide();
      doDelete(pendingDeleteUrl, pendingButton);
      pendingDeleteUrl = null;
      pendingButton = null;
    });
  }

  // Função que faz o fetch DELETE com robustez
  async function doDelete(url, btn = null) {
    try {
      // debug: mostra no console o que está sendo enviado
      console.log("DELETE ->", url, { btn });

      const res = await fetch(url, {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": csrf ?? "",
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
      });

      // Se não for JSON, pega o texto (útil para debug)
      const text = await res.text();
      let json = null;
      try { json = JSON.parse(text); } catch (err) { /* não-JSON */ }

      if (!res.ok) {
        // mensagens úteis para debug e usuário
        const serverMsg = json?.message ?? text ?? `HTTP ${res.status}`;
        showError("Falha ao excluir (server).", { status: res.status, msg: serverMsg });
        return;
      }

      // sucesso (aceita tanto JSON padrão quanto fallback)
      const success = json?.success ?? true;
      const message = json?.message ?? "Excluído com sucesso.";

      if (!success) {
        showError(message, { json });
        return;
      }

      notyf.success(message);
      // atualizar UI: recarrega a lista de templates ou a página
      // se você tiver uma função para recarregar somente a tabela, chame aqui
      setTimeout(() => location.reload(), 300);

    } catch (err) {
      showError("Erro de comunicação ao excluir.", err);
    }
  }
});
