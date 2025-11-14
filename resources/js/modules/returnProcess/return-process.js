/* ===============================
   📦 MÓDULO: RETURN PROCESS (Devoluções / Recusas)
   =============================== */
document.addEventListener('DOMContentLoaded', () => {
  const notyf = new Notyf({ duration: 2500, position: { x: 'right', y: 'top' } });
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const modalEl = document.getElementById('modal-process-view');
  const modalBody = document.getElementById('modalProcessBody');
  if (!modalEl || !modalBody) return;

  const modal = new bootstrap.Modal(modalEl);

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-view-process');
    if (!btn) return;

    const id = btn.dataset.id;
    modalBody.innerHTML = `
      <div class="text-center py-5 text-muted">
        <div class="spinner-border text-primary mb-3"></div><br>
        Carregando detalhes do processo #${id}...
      </div>
    `;
    modal.show();

    try {
      const res = await fetch(`/return-process/${id}`, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      if (!res.ok) throw new Error(data.message || 'Erro ao carregar detalhes.');

      modalBody.innerHTML = `
        <div class="row">
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header"><h4 class="card-title">Informações do Cliente</h4></div>
              <div class="card-body">
                <p><strong>Nome:</strong> ${data.cliente_nome}</p>
                <p><strong>CNPJ:</strong> ${data.cliente_cnpj}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>Etapa Atual:</strong> ${data.etapa_atual}</p>
                <p><strong>Criado em:</strong> ${data.created_at}</p>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header"><h4 class="card-title">Observações</h4></div>
              <div class="card-body">
                <p>${data.observacoes || 'Nenhuma observação registrada.'}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h4 class="card-title">Itens do Processo</h4></div>
          <div class="card-body p-0">
            <table class="table table-vcenter table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Artigo</th>
                  <th>Descrição</th>
                  <th>NCM</th>
                  <th>Qtd</th>
                  <th>Preço Unitário</th>
                </tr>
              </thead>
              <tbody>
                ${data.itens.map(i => `
                  <tr>
                    <td>${i.artigo}</td>
                    <td>${i.descricao}</td>
                    <td>${i.ncm ?? '-'}</td>
                    <td>${i.quantidade}</td>
                    <td>R$ ${parseFloat(i.preco_unitario).toFixed(2)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    } catch (err) {
      console.error(err);
      modalBody.innerHTML = `
        <div class="alert alert-danger text-center m-3">
          Erro ao carregar detalhes do processo.
        </div>
      `;
      notyf.error("Falha ao carregar processo.");
    }
  });
});
