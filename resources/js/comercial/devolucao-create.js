/**
 * CoreFlow — Devolução (XML)
 * =========================
 * PADRÃO FIÉL AO JS ANTIGO
 *
 * - Cliente = DEST
 * - nf_saida = ide.nNF
 * - nf_devolucao = NFref.refNFe
 * - nfo = nf_saida
 * - nprot = protNFe.infProt.nProt
 */

document.addEventListener('DOMContentLoaded', () => {

    const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let xmlData = null;

    /* ===============================
     * HELPERS
     * =============================== */
    const get = (parent, tag) =>
        parent?.getElementsByTagName(tag)[0]
        || parent?.getElementsByTagNameNS('*', tag)[0]
        || null;

    const setTxt = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };
    const setVal = (id, v) => { const e = document.getElementById(id); if (e) e.value = v; };
    const getVal = id => document.getElementById(id)?.value || '';

    /* ===============================
     * PROCESSAR XML — DEVOLUÇÃO
     * =============================== */
    function processarXMLDevolucao(file) {

        const reader = new FileReader();

        reader.onload = e => {

            const xml = new DOMParser().parseFromString(e.target.result, 'text/xml');

            if (xml.getElementsByTagName('parsererror').length) {
                notyf.error('XML inválido');
                return;
            }

            /* ===== EXTRAÇÃO (FIÉL AO JS ANTIGO) ===== */

            // CLIENTE = DEST
            const dest = get(xml, 'dest');
            const ide = get(xml, 'ide');

            const cliente_nome = get(dest, 'xNome')?.textContent || '';
            const cliente_cnpj = get(dest, 'CNPJ')?.textContent || '';

            // NF SAÍDA
            const nf_saida = get(ide, 'nNF')?.textContent || '';

            // NF DEVOLUÇÃO (XML REFERENCIADO)
            const nf_devolucao = get(
                get(xml, 'NFref'),
                'refNFe'
            )?.textContent || '';

            // NFO = NF SAÍDA
            const nfo = nf_saida;

            // PROTOCOLO SEFAZ
            const nprot = get(
                get(get(xml, 'protNFe'), 'infProt'),
                'nProt'
            )?.textContent || '';

            // OBSERVAÇÕES
            const inf_cpl = get(xml, 'infCpl')?.textContent || '';

            /* ===== UI ===== */
            setTxt('client-name-display_devolucao', cliente_nome);
            setTxt('client-cnpj-display_devolucao', cliente_cnpj);
            setTxt('nf-saida-display_devolucao', nf_saida);
            setTxt('nf-devolucao-display_devolucao', nf_devolucao);
            setTxt('nprot-display_devolucao', nprot);
            setTxt('inf-cpl-display_devolucao', inf_cpl);

            setVal('nf_saida_devolucao', nf_saida);
            setVal('nf_devolucao_devolucao', nf_devolucao);
            setVal('nprot_devolucao', nprot);

            /* ===== ITENS ===== */
            const tbody = document.getElementById('product-table-body_devolucao');
            if (!tbody) return;

            tbody.innerHTML = '';

            const itens = [];
            const dets = xml.getElementsByTagName('det');

            for (const det of dets) {
                const prod = get(det, 'prod');
                if (!prod) continue;

                const item = {
                    artigo: (get(prod, 'cProd')?.textContent || '').replace(/^0+/, ''),
                    descricao: get(prod, 'xProd')?.textContent || '',
                    ncm: get(prod, 'NCM')?.textContent || '',
                    quantidade: Number(get(prod, 'qCom')?.textContent || 0).toFixed(2),
                    preco_unitario: Number(get(prod, 'vUnCom')?.textContent || 0).toFixed(2),
                };

                itens.push(item);

                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${item.artigo}</td>
                        <td>${item.descricao}</td>
                        <td>${item.ncm}</td>
                        <td>${nf_saida}</td>
                        <td>${nf_devolucao}</td>
                        <td class="text-end">${item.quantidade}</td>
                        <td class="text-end">R$ ${item.preco_unitario.replace('.', ',')}</td>
                    </tr>
                `);
            }

            /* ===== OBJETO FINAL (BANCO) ===== */
            xmlData = {
                cliente_nome,
                cliente_cnpj,
                nf_saida,
                nf_devolucao,
                nfo,
                nprot,
                itens
            };

            notyf.success('XML de devolução carregado');
        };

        reader.readAsText(file);
    }

    /* ===============================
     * INPUT XML
     * =============================== */
    document.getElementById('xmlFileInput_devolucao')
        ?.addEventListener('change', e => {
            if (e.target.files?.[0]) {
                processarXMLDevolucao(e.target.files[0]);
            }
        });

    /* ===============================
     * SALVAR DEVOLUÇÃO
     * =============================== */
    document.getElementById('btnSalvarDevolucao')
        ?.addEventListener('click', async () => {

            if (!xmlData) {
                notyf.error('Carregue um XML válido');
                return;
            }

            const formData = new FormData();
            const movimentacao = document.getElementById('movimentacao_devolucao')?.checked;

            formData.append('cliente_nome', xmlData.cliente_nome);
            formData.append('cliente_cnpj', xmlData.cliente_cnpj);
            formData.append('nf_saida', xmlData.nf_saida);
            formData.append('nf_devolucao', xmlData.nf_devolucao);
            formData.append('nfo', xmlData.nfo);
            formData.append('nprot', xmlData.nprot);
            formData.append('itens', JSON.stringify(xmlData.itens));

            formData.append('motivo', getVal('motivo_devolucao'));
            formData.append('observacoes', getVal('observacoes_devolucao'));
            formData.append('responsavel', getVal('gestorSolicitante_devolucao'));
            formData.append('codigo_erro', getVal('codigo_erro_devolucao'));

            if (movimentacao) {
                formData.append('movimentacao_mercadoria', '1');
            }
            
            const res = await fetch('/comercial/devolucao', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) {
                notyf.error(data.message || 'Erro ao salvar devolução');
                return;
            }

            notyf.success('Devolução registrada com sucesso');
            setTimeout(() => location.href = '/comercial/processes', 800);
        });

});
