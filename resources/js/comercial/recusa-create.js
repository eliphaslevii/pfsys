/**
 * CoreFlow — Recusa (XML)
 * ======================
 * PADRÃO FIÉL AO JS ANTIGO
 *
 * - Cliente = EMITENTE
 * - nfd = ide.nNF
 * - nfo = ide.nNF
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
     * PROCESSAR XML — RECUSA
     * =============================== */
    function processarXMLRecusa(file) {

        const reader = new FileReader();

        reader.onload = e => {

            const xml = new DOMParser().parseFromString(e.target.result, 'text/xml');

            if (xml.getElementsByTagName('parsererror').length) {
                notyf.error('XML inválido');
                return;
            }

            /* ===== EXTRAÇÃO (RECUSA) ===== */

            // CLIENTE = EMITENTE
            const emit = get(xml, 'emit');
            const ide = get(xml, 'ide');

            const cliente_nome = get(emit, 'xNome')?.textContent || '';
            const cliente_cnpj = get(emit, 'CNPJ')?.textContent || '';

            // NOTA RECUSADA
            const nfd = get(ide, 'nNF')?.textContent || '';

            // NFO = NFD
            const nfo = nfd;

            // PROTOCOLO
            const nprot = get(
                get(get(xml, 'protNFe'), 'infProt'),
                'nProt'
            )?.textContent || '';

            // NATUREZA OPERAÇÃO (auxilia motivo)
            const natOp = get(ide, 'natOp')?.textContent || '';

            // OBSERVAÇÕES
            const inf_cpl = get(xml, 'infCpl')?.textContent || '';

            /* ===== UI ===== */
            setTxt('client-name-display_recusa', cliente_nome);
            setTxt('client-cnpj-display_recusa', cliente_cnpj);
            setTxt('nfd-display_recusa', nfd);
            setTxt('nfo-display_recusa', nfo);
            setTxt('nprot-display_recusa', nprot);
            setTxt('inf-cpl-display_recusa', inf_cpl);

            setVal('nfd_recusa', nfd);
            setVal('nfo_recusa', nfo);
            setVal('nprot_recusa', nprot);

            /* ===== ITENS ===== */
            const tbody = document.getElementById('product-table-body_recusa');
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
                        <td>${nfd}</td>
                        <td class="text-end">${item.quantidade}</td>
                        <td class="text-end">R$ ${item.preco_unitario.replace('.', ',')}</td>
                    </tr>
                `);
            }

            /* ===== OBJETO FINAL ===== */
            xmlData = {
                cliente_nome,
                cliente_cnpj,
                nfd,
                nfo,
                nprot,
                natOp,
                inf_cpl,
                itens
            };

            notyf.success('XML de recusa carregado');
        };

        reader.readAsText(file);
    }

    /* ===============================
     * INPUT XML
     * =============================== */
    document.getElementById('xmlFileInput_recusa')
        ?.addEventListener('change', e => {
            if (e.target.files?.[0]) {
                processarXMLRecusa(e.target.files[0]);
            }
        });

    /* ===============================
     * SALVAR RECUSA
     * =============================== */
    document.getElementById('btnSalvarRecusa')
        ?.addEventListener('click', async () => {

            if (!xmlData) {
                notyf.error('Carregue um XML válido');
                return;
            }

            const formData = new FormData();
            
            const movimentacao = document.getElementById('movimentacao_recusa')?.checked;
            formData.append('cliente_nome', xmlData.cliente_nome);
            formData.append('cliente_cnpj', xmlData.cliente_cnpj);
            formData.append('nfd', xmlData.nfd);
            formData.append('nfo', xmlData.nfo);
            formData.append('nprot', xmlData.nprot);
            formData.append('itens', JSON.stringify(xmlData.itens));
            formData.append('codigo_erro', getVal('codigo_erro_recusa'));

            formData.append('motivo', getVal('motivo_recusa'));
            formData.append('observacoes', getVal('observacoes_recusa'));
            formData.append('responsavel', getVal('gestorSolicitante_recusa'));
            
            if (movimentacao) {
                formData.append('movimentacao_mercadoria', '1');
            }
            
            const res = await fetch('/comercial/recusa', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) {
                notyf.error(data.message || 'Erro ao salvar recusa');
                return;
            }

            notyf.success('Recusa registrada com sucesso');
            setTimeout(() => location.href = '/comercial/processes', 800);
        });

});
