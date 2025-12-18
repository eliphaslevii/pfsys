window.openXmlDetails = function (context) {
    const area = document.getElementById(`xmlDetailsArea_${context}`);
    if (!area) return;

    const collapse = bootstrap.Collapse.getOrCreateInstance(area);
    collapse.show();
};


/* ================= RENDER XML ================= */

window.renderXmlDetails = function (context, data) {

    const set = (id, value) => {
        const el = document.getElementById(`${id}_${context}`);
        if (el) el.textContent = value || '—';
    };

    set('client-name-display', data.nomeCliente);
    set('client-cnpj-display', data.cnpjCliente);
    set('nf-saida-display', data.nf_saida);
    set('inf-nfd-display', data.nf_devolucao);
    set('inf-nfo-display', data.nfo);
    set('nprot-display', data.protocolo);
    set('inf-cpl-display', data.inf_cpl);

    const tbody = document.getElementById(`product-table-body_${context}`);
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!data.itens?.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    Nenhum item encontrado
                </td>
            </tr>
        `;
        return;
    }

    data.itens.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td>${item.artigo}</td>
                <td>${item.descricao}</td>
                <td class="d-none d-md-table-cell">${item.ncm || '-'}</td>
                <td class="d-none d-lg-table-cell">${item.nf_saida || '-'}</td>
                <td class="d-none d-lg-table-cell">${item.nf_devolucao || '-'}</td>
                <td class="text-end">${item.quantidade}</td>
                <td class="text-end">R$ ${item.preco_unitario.replace('.', ',')}</td>
            </tr>
        `;
    });

    // abre automaticamente
    window.openXmlDetails(context);
};
