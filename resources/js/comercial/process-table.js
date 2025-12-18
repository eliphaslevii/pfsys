
document.addEventListener('DOMContentLoaded', () => {

    const tableBody = document.querySelector('#processTable tbody');
    const btnRefresh = document.getElementById('btnRefresh');
    const paginationContainer = document.getElementById('pagination');

    let currentPage = 1;

    carregarTabela();

    btnRefresh?.addEventListener('click', () => carregarTabela(1));

    /* ===============================
     * CARREGAR TABELA
     * =============================== */
    function carregarTabela(page = 1) {
        currentPage = page;

        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    Carregando dados...
                </td>
            </tr>
        `;

        fetch(`/comercial/processes/data?page=${page}`)
            .then(r => r.json())
            .then(({ data, meta }) => {
                renderTabela(data);
                renderPaginacao(meta);
            })
            .catch(() => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center text-danger py-4">
                            Erro ao carregar dados
                        </td>
                    </tr>
                `;
            });
    }

    /* ===============================
     * RENDER TABELA
     * =============================== */
    function renderTabela(data) {
        tableBody.innerHTML = '';

        if (!data.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Nenhum processo encontrado
                    </td>
                </tr>
            `;
            return;
        }
        function renderApproveButton(p) {
            if (!p.can_approve || !p.needs_approval) {
                return '';
            }

            return `
        <button class="btn btn-sm btn-success btn-approve"
            data-id="${p.id}"
            title="Aprovar e iniciar fluxo">
            <i class="ti ti-check"></i>
        </button>
    `;
        }
        function renderDeleteButton(p) {
            if (!p.can_delete) {
                return '';
            }

            return `
        <button class="btn btn-sm btn-outline-danger btn-delete"
            data-id="${p.id}"
            title="Excluir processo">
            <i class="ti ti-trash"></i>
        </button>
    `;
        }

        data.forEach(p => {
            tableBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>
                       ${p.tipo}
                    </td>

                    <td>${p.nomeCliente}</td>

                    <td class="text-muted">
                        ${formatCnpj(p.cnpjCliente)}
                    </td>

                    <td class="text-muted">
                        ${p.motivo}
                    </td>

                    <td>${p.codigoErro ?? '-'}</td>

                    <td>${p.etapa ?? 'Pendente Aprovação'}</td>

                    <td>${p.responsavel ?? '-'}</td>

                    <td class="text-muted">
                        ${formatData(p.created_at)}
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">

                            ${renderApproveButton(p)}
                            ${renderDeleteButton(p)}

                            <button class="btn btn-sm btn-outline-primary btn-view"
                                data-id="${p.id}"
                                title="Visualizar processo">
                                <i class="ti ti-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-outline-secondary btn-workflow"
                                data-id="${p.id}"
                                title="Ações do workflow">
                                <i class="ti ti-route"></i>
                            </button>
                        </div>
                    </td>

                </tr>
            `);
        });

        bindDetalhes();
    }

    /* ===============================
     * PAGINAÇÃO
     * =============================== */
    function renderPaginacao(meta) {
        if (!paginationContainer) return;

        paginationContainer.innerHTML = '';

        if (meta.last_page <= 1) return;

        for (let i = 1; i <= meta.last_page; i++) {
            const btn = document.createElement('button');

            btn.className = `btn btn-sm ${i === meta.current_page
                ? 'btn-primary'
                : 'btn-outline-secondary'
                } ms-1`;

            btn.textContent = i;

            btn.addEventListener('click', () => carregarTabela(i));

            paginationContainer.appendChild(btn);
        }
    }

    /* ===============================
     * EVENTOS
     * =============================== */
    function bindDetalhes() {
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', () => {
                abrirModal(btn.dataset.id);
            });
        });
    }

    function abrirModal(id) {
        const modal = new bootstrap.Modal(
            document.getElementById('modal-process-view')
        );
        modal.show();

        fetch(`/comercial/processes/${id}/detalhes`)
            .then(r => r.json())
            .then(preencherModal);
    }

    /* ===============================
     * MODAL
     * =============================== */
    function preencherModal(data) {
        const p = data.process;

        set('det-cliente-nome', p.cliente_nome);
        set('det-cliente-cnpj', formatCnpj(p.cliente_cnpj));

        set('det-nfd', p.nfd ?? '-');
        set('det-nf-saida', p.nf_saida ?? '-');
        set('det-nf-devolucao', p.nf_devolucao ?? '-');
        set('det-nfo', p.nfo ?? '-');
        set('det-nprot', p.nprot ?? '-');

        set('det-motivo', p.motivo);
        set('det-codigo-erro', p.codigo_erro ?? '-');
        set('det-status', p.status);
        set('det-etapa', p.etapa);

        const tbody = document.getElementById('det-itens-body');
        tbody.innerHTML = '';

        data.itens.forEach(item => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${item.artigo}</td>
                    <td>${item.descricao}</td>
                    <td>${item.ncm ?? '-'}</td>
                    <td>${item.quantidade}</td>
                    <td>${item.preco_unitario}</td>
                </tr>
            `);
        });
    }

    /* ===============================
     * HELPERS
     * =============================== */
    function renderTipo(tipo, mov) {
        if (!mov) {
            return `<span class="fw-semibold">${tipo}</span>`;
        }

        return `
            <span class="fw-semibold">
                ${tipo}
                <span class="text-success ms-1"
                      title="Houve movimentação de mercadoria">
                    · Mov. Merc.
                </span>
            </span>
        `;
    }

    const set = (id, v) =>
        document.getElementById(id).textContent = v;

    const formatData = d =>
        new Date(d).toLocaleString('pt-BR');

    const formatCnpj = c =>
        c
            ? c.replace(
                /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/,
                '$1.$2.$3/$4-$5'
            )
            : '-';
});
function renderPaginacao(meta) {
    const container = document.getElementById('pagination');

    // Se não houver paginação necessária, limpa e sai (ou mostra total apenas)
    if (!meta || meta.total === 0) {
        container.innerHTML = '';
        return;
    }

    // 1. Cálculo dos textos "Mostrando X de Y"
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);
    const labelInfo = `Mostrando <strong>${from}-${to}</strong> de <strong>${meta.total}</strong> registros`;

    // 2. Estrutura Base (Flexbox: Texto à esquerda, Botões à direita)
    container.innerHTML = `
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="text-muted small">
                ${labelInfo}
            </div>
            <ul class="pagination-list" id="pagination-list"></ul>
        </div>
    `;

    const list = container.querySelector('#pagination-list');
    const current = meta.current_page;
    const last = meta.last_page;
    const delta = 2; // Quantidade de botões ao redor da página atual

    // Helper para criar botões
    const createBtn = (content, page, isCurrent, isDisabled) => {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.className = `page-btn ${isCurrent ? 'active' : ''}`;
        btn.innerHTML = content;

        if (isDisabled) {
            btn.disabled = true;
            btn.classList.add('disabled');
        } else {
            btn.onclick = () => carregarTabela(page);
        }
        li.appendChild(btn);
        list.appendChild(li);
    };

    // --- Botão ANTERIOR (<<) ---
    createBtn('<i class="ti ti-chevron-left"></i>', current - 1, false, current === 1);

    // --- Lógica de Janela (1 ... 4 5 6 ... 10) ---
    let range = [];
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            range.push(i);
        }
    }

    let prev;
    range.forEach(i => {
        if (prev) {
            if (i - prev === 2) {
                createBtn(prev + 1, prev + 1, false, false);
            } else if (i - prev !== 1) {
                createBtn('...', null, false, true);
            }
        }
        createBtn(i, i, i === current, false);
        prev = i;
    });

    // --- Botão PRÓXIMO (>>) ---
    createBtn('<i class="ti ti-chevron-right"></i>', current + 1, false, current === last);
}
// --- Botão de aprovação primária ---
/* ===============================
 * APROVAR PROCESSO
 * =============================== */
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-approve');
    if (!btn) return;

    window.processToApprove = btn.dataset.id;

    new bootstrap.Modal(
        document.getElementById('modal-approve')
    ).show();
});

document.getElementById('btnSubmitApprove')
    .addEventListener('click', () => {


        window.notyf = new Notyf({ duration: 2500, position: { x: 'right', y: 'top' } });

        fetch(`/comercial/processes/${window.processToApprove}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => r.json())
            .then(() => {
                notyf.success('Processo aprovado com sucesso');
                setTimeout(() => location.reload(), 400);
            })
            .catch(() => {
                notyf.error('Erro ao aprovar processo');
            });
    });


/* ===============================
 * EXCLUIR PROCESSO
 * =============================== */
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    window.processToDelete = btn.dataset.id;

    new bootstrap.Modal(
        document.getElementById('modal-delete')
    ).show();
});

document.getElementById('btnConfirmDelete')
    .addEventListener('click', () => {


        window.notyf = new Notyf({ duration: 2500, position: { x: 'right', y: 'top' } });

        fetch(`/comercial/processes/${window.processToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => {
                if (!r.ok) throw new Error();
                notyf.success('Processo excluído com sucesso');
                setTimeout(() => location.reload(), 400);
            })
            .catch(() => {
                notyf.error('Erro ao excluir processo');
            });
    });

