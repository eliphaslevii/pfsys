document.addEventListener('DOMContentLoaded', () => {
  console.log('📦 return-process-index.js carregado');

  const tableBody = document.querySelector('#processTableBody');
  const btnRefresh = document.querySelector('#btnRefresh');
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
  function can(permission) {
    return Array.isArray(window.userPermissions) && window.userPermissions.includes(permission);
  }
  async function loadProcesses() {
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center text-muted py-3">
          <div class="spinner-border text-primary me-2" role="status"></div> Carregando processos...
        </td>
      </tr>`;

    try {
      const response = await fetch('/return-process/data', { headers: { 'Accept': 'application/json' } });
      const json = await response.json();

      if (!response.ok || !json.data) throw new Error(json.message || 'Erro ao carregar dados.');

      if (json.data.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="9" class="text-center text-muted py-3">
              Nenhum processo encontrado.
            </td>
          </tr>`;
        return;
      }

      tableBody.innerHTML = json.data.map(p => `
        <tr>
          <td>${p.id}</td>
          <td>${p.tipo}</td>
          <td>${p.cliente}</td>
          <td>${p.cnpj}</td>
          <td>
            <span class="fw-semibold ${statusClass(p.status)}">
              <i class="ti ti-circle-filled me-1"></i>${p.status}
            </span>
          </td>
          <td>${p.etapa ?? '-'}</td>
          <td>${p.responsavel ?? '-'}</td>
          <td>${p.created_at ?? '-'}</td>
        <td class="text-end">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-primary btn-view" data-id="${p.id}">
      <i class="ti ti-eye"></i>
    </button>
${can('process.delete')
          ? `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${p.id}">
       <i class="ti ti-trash"></i>
     </button>`
          : ''
        }

  </div>
</td>

        </tr>
      `).join('');

      attachActions();
    } catch (err) {
      console.error(err);
      tableBody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center text-danger py-3">
            Falha ao carregar processos.
          </td>
        </tr>`;
      notyf.error('Erro ao carregar processos.');
    }
  }

  function attachActions() {
    document.querySelectorAll('.btn-view').forEach(btn => {
      btn.addEventListener('click', () => openProcessModal(btn.dataset.id));
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', () => deleteProcess(btn.dataset.id));
    });
  }

  function statusClass(status) {
    const s = status.toLowerCase();
    if (s.includes('aprovado')) return 'text-success';
    if (s.includes('recusado')) return 'text-danger';
    if (s.includes('aberto') || s.includes('pendente')) return 'text-warning';
    return 'text-muted';
  }
async function openProcessModal(id) {
  console.log(`🔍 Abrindo processo #${id}`);

  const modalEl = document.getElementById('modal-process-view');
  const modal = new bootstrap.Modal(modalEl);
  const body = document.getElementById('modalProcessBody');
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  body.innerHTML = `
    <div class="text-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3"></div><br>
      Carregando processo #${id}...
    </div>`;
  modal.show();

  try {
    const res = await fetch(`/return-process/${id}`, { headers: { 'Accept': 'application/json' } });
    const json = await res.json();

    console.log('🧾 Resposta da API:', json);
    if (!res.ok || !json.success) throw new Error(json.message || `Erro ao buscar processo (#${res.status})`);

    const data = json.data || {};
    const itens = Array.isArray(data.items) ? data.items : [];

    // 🧩 Monta o corpo do modal
    body.innerHTML = `
      <div class="container-fluid">
      <div id="client-info-box_{{ $context }}" class="mt-4 p-3 rounded-3 border-start border-4 border-primary bg-light-subtle shadow-sm">

        <h5 class="fw-bold text-primary mb-3">Informações Gerais</h5>
        <div class="row mb-3">
          <div class="col-md-6"><strong>Cliente:</strong> ${data.cliente_nome ?? '—'}</div>
          <div class="col-md-6"><strong>CNPJ:</strong> ${data.cliente_cnpj ?? '—'}</div>
          <div class="col-md-6"><strong>Status:</strong> ${data.status ?? '—'}</div>
          <div class="col-md-6"><strong>Etapa Atual:</strong> ${data.etapa_atual ?? '—'}</div>
        </div>

      </div>

      <div id="client-info-box_{{ $context }}" class="mt-4 p-3 rounded-3 border-start border-4 border-primary bg-light-subtle shadow-sm">

        <h5 class="fw-bold text-primary mb-3">Informações Fiscais</h5>
        <div class="row mb-3">
          <div class="col-md-6"><strong>NF Saída:</strong> ${data.nf_saida ?? '—'}</div>
          <div class="col-md-6"><strong>NF Devolução (NFD):</strong> ${data.nf_devolucao ?? '—'}</div>
          <div class="col-md-6"><strong>NF Original (NFO):</strong> ${data.nfo ?? '—'}</div>
          <div class="col-md-6"><strong>Protocolo (nProt):</strong> ${data.protocolo ?? '—'}</div>
          <div class="col-md-6"><strong>Recusa SEFAZ:</strong> ${data.recusa_sefaz ?? '—'}</div>
        </div>

      </div>

      <div id="client-info-box_{{ $context }}" class="mt-4 p-3 rounded-3 border-start border-4 border-primary bg-light-subtle shadow-sm">

        <h5 class="fw-bold text-primary mb-3">Documentos e Logística</h5>
        <div class="row mb-3">
          <div class="col-md-6"><strong>Delivery:</strong> ${data.delivery ?? '—'}</div>
          <div class="col-md-6"><strong>Doc Faturamento:</strong> ${data.doc_faturamento ?? '—'}</div>
          <div class="col-md-6"><strong>Ordem de Entrada:</strong> ${data.ordem_entrada ?? '—'}</div>
          <div class="col-md-6"><strong>MIGO:</strong> ${data.migo ?? '—'}</div>
        </div>

        <div class="mb-3">
          <strong>Observação:</strong><br>
          <span class="text-muted">${data.observacoes ?? 'Sem observações.'}</span>
        </div>
      </div>

        <hr class="my-3">

        <div class="d-flex justify-content-between align-items-center mt-4">
          <h5 class="fw-bold text-primary mb-0">Itens do Processo</h5>
          <button type="button" class="btn btn-sm btn-outline-primary btn-pill toggle-items-btn" data-target=".items-container">
            <i class="ti ti-chevron-down me-1"></i> Mostrar Itens
          </button>
        </div>

        <div class="table-responsive mt-3 items-container shadow-sm rounded-3 border" style="display:none;">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Artigo</th>
                <th>Descrição</th>
                <th>NCM</th>
                <th>NF Saída</th>
                <th>NF Devolução</th>
                <th>Qtd</th>
                <th>Preço Unit.</th>
              </tr>
            </thead>
            <tbody>
              ${itens.length
                ? itens.map(i => `
                    <tr>
                      <td>${i.artigo ?? '—'}</td>
                      <td>${i.descricao ?? '—'}</td>
                      <td>${i.ncm ?? '—'}</td>
                      <td>${i.nf_saida ?? '—'}</td>
                      <td>${i.nf_devolucao ?? '—'}</td>
                      <td>${i.quantidade ?? 0}</td>
                      <td>R$ ${parseFloat(i.preco_unitario ?? 0).toFixed(2).replace('.', ',')}</td>
                    </tr>
                  `).join('')
                : `<tr><td colspan="7" class="text-center text-muted">Nenhum item encontrado.</td></tr>`
              }
            </tbody>
          </table>
        </div>
      </div>
    `;

    // ✨ Listener interno do modal (evita duplicar handlers globais)
    const toggleBtn = body.querySelector('.toggle-items-btn');
    const itemsContainer = body.querySelector('.items-container');

    if (toggleBtn && itemsContainer) {
      toggleBtn.addEventListener('click', () => {
        const isHidden = itemsContainer.style.display === 'none' || !itemsContainer.style.display;
        itemsContainer.style.display = isHidden ? 'block' : 'none';
        toggleBtn.innerHTML = `
          <i class="ti ${isHidden ? 'ti-chevron-up' : 'ti-chevron-down'} me-1"></i>
          ${isHidden ? 'Ocultar Itens' : 'Mostrar Itens'}
        `;
      });
    }

  } catch (err) {
    console.error('❌ Erro ao abrir processo:', err);
    body.innerHTML = `
      <div class="alert alert-danger text-center">
        Erro ao carregar detalhes do processo.<br>
        ${err.message}
      </div>`;
    notyf.error(err.message || 'Falha ao abrir processo.');
  }
}


  async function deleteProcess(id) {
    if (!confirm('Tem certeza que deseja excluir este processo?')) return;

    try {
      const res = await fetch(`/return-process/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
      });

      const data = await res.json();
      if (res.ok && data.success) {
        notyf.success(data.message);
        loadProcesses();
      } else {
        notyf.error(data.message || 'Erro ao excluir processo.');
      }
    } catch (err) {
      console.error(err);
      notyf.error('Erro ao se comunicar com o servidor.');
    }
  }

  btnRefresh.addEventListener('click', loadProcesses);
  loadProcesses();
});
const toggleBtn = body.querySelector('.toggle-items-btn');
const itemsContainer = body.querySelector('.items-container');

if (toggleBtn && itemsContainer) {
  toggleBtn.addEventListener('click', () => {
    const isVisible = itemsContainer.style.display === 'block';

    // Alterna display
    itemsContainer.style.display = isVisible ? 'none' : 'block';

    // Atualiza texto e ícone
    toggleBtn.innerHTML = `
      <i class="ti ${isVisible ? 'ti-chevron-down' : 'ti-chevron-up'} me-1"></i>
      ${isVisible ? 'Mostrar Itens' : 'Ocultar Itens'}
    `;
  });
}