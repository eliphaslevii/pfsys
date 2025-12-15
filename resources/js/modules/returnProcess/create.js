/**
 * CoreFlow - Return Process Creation (Revisado 2025)
 * ---------------------------------------------------------------
 * - Lê XML (NFe)
 * - Extrai campos completos: nf_saida, nf_devolucao, nfo, protocolo, recusa_sefaz
 * - Envia via fetch (sem jQuery)
 */

document.addEventListener('DOMContentLoaded', () => {
  /* =====================================================
 * 4️⃣ Botão "Mostrar mais / menos"
 * ===================================================== */
  document.querySelectorAll('.toggle-items-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetSelector = btn.getAttribute('data-target');
      const target = btn.closest('div').parentElement.querySelector(targetSelector);
      const icon = btn.querySelector('i');
      if (!target) return console.warn('⚠️ Container de itens não encontrado:', targetSelector);

      const isHidden = target.style.display === 'none' || !target.style.display;

      // Alterna visibilidade
      target.style.display = isHidden ? 'block' : 'none';

      // Troca texto e ícone
      btn.innerHTML = `
      <i class="ti ${isHidden ? 'ti-chevron-up' : 'ti-chevron-down'} me-1"></i>
      ${isHidden ? 'Ocultar Itens' : 'Mostrar Itens'}
    `;
    });
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  /* =====================================================
   * 2️⃣ Processar XML
   * ===================================================== */
  function processarXML(file, context) {
    const reader = new FileReader();
    reader.onload = ev => {
      try {
        const parser = new DOMParser();
        const xml = parser.parseFromString(ev.target.result, "text/xml");
        const ns = "http://www.portalfiscal.inf.br/nfe";

        const emit = xml.getElementsByTagNameNS(ns, "emit")[0];
        const dest = xml.getElementsByTagNameNS(ns, "dest")[0];
        const ide = xml.getElementsByTagNameNS(ns, "ide")[0];
        const infCplNode = xml.getElementsByTagNameNS(ns, "infCpl")[0];
        const nProtNode = xml.getElementsByTagNameNS(ns, "nProt")[0];
        const refNFeNode = xml.getElementsByTagNameNS(ns, "NFref")[0]?.getElementsByTagNameNS(ns, "refNFe")[0];

        let nomeCliente = "N/A";
        let cnpjCliente = "N/A";
        let nfSaida = "N/A";
        let nfDevolucao = "N/A";
        let nfo = refNFeNode?.textContent || "N/A";
        let protocolo = nProtNode?.textContent || "—";
        let recusaSefaz = "—";
        let infCpl = infCplNode?.textContent || "";

        // === RECUSA ===
        if (context === "recusa") {
          nomeCliente = dest?.getElementsByTagNameNS(ns, "xNome")[0]?.textContent || "N/A";
          cnpjCliente = dest?.getElementsByTagNameNS(ns, "CNPJ")[0]?.textContent || "N/A";
          nfSaida = ide?.getElementsByTagNameNS(ns, "nNF")[0]?.textContent || "N/A";
          recusaSefaz = protocolo || "—";
        }

        // === DEVOLUÇÃO ===
        else if (context === "devolucao") {
          nomeCliente = emit?.getElementsByTagNameNS(ns, "xNome")[0]?.textContent || "N/A";
          cnpjCliente = emit?.getElementsByTagNameNS(ns, "CNPJ")[0]?.textContent || "N/A";
          nfDevolucao = ide?.getElementsByTagNameNS(ns, "nNF")[0]?.textContent || "N/A";
        }

        // Atualiza campos na UI
        document.getElementById(`client-name-display_${context}`).textContent = nomeCliente;
        document.getElementById(`client-cnpj-display_${context}`).textContent = cnpjCliente;
        document.getElementById(`nf-saida-display_${context}`).textContent = nfSaida;
        document.getElementById(`nprot-display_${context}`).textContent = protocolo;
        document.getElementById(`inf-nfd-display_${context}`).textContent = nfDevolucao;
        document.getElementById(`inf-nfo-display_${context}`).textContent = nfo;
        document.getElementById(`inf-cpl-display_${context}`).textContent = infCpl;

        // === Itens ===
        const itens = xml.getElementsByTagNameNS(ns, "det");
        const tbody = document.getElementById(`product-table-body_${context}`);
        tbody.innerHTML = "";

        if (!itens.length) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum item encontrado.</td></tr>';
          return;
        }

        const parsedItens = [];
        for (let item of itens) {
          const prod = item.getElementsByTagNameNS(ns, "prod")[0];
          if (!prod) continue;

          const artigo = (prod.getElementsByTagNameNS(ns, "cProd")[0]?.textContent || "").replace(/^0+/, "");
          const descricao = prod.getElementsByTagNameNS(ns, "xProd")[0]?.textContent || "";
          const ncm = prod.getElementsByTagNameNS(ns, "NCM")[0]?.textContent || "";
          const qtd = parseFloat(prod.getElementsByTagNameNS(ns, "qCom")[0]?.textContent || 0).toFixed(2);
          const preco = parseFloat(prod.getElementsByTagNameNS(ns, "vUnCom")[0]?.textContent || 0).toFixed(2);

          parsedItens.push({ artigo, descricao, ncm, nf_saida: nfSaida, nf_devolucao: nfDevolucao, quantidade: qtd, preco_unitario: preco });

          tbody.innerHTML += `
            <tr>
              <td>${artigo}</td>
              <td>${descricao}</td>
              <td>${ncm}</td>
              <td>${nfSaida}</td>
              <td>${nfDevolucao}</td>
              <td>${qtd}</td>
              <td>R$ ${preco.replace('.', ',')}</td>
            </tr>`;
        }

        // Guarda tudo
        window._xmlData = { context, nomeCliente, cnpjCliente, nf_saida: nfSaida, nf_devolucao: nfDevolucao, nfo, protocolo, recusa_sefaz: recusaSefaz, inf_cpl: infCpl, itens: parsedItens };

        notyf.success("XML importado com sucesso!");
      } catch (err) {
        console.error("Erro ao processar XML:", err);
        notyf.error("Falha ao processar o XML.");
      }
    };
    reader.readAsText(file);
  }

  document.getElementById('xmlFileInput_recusa').addEventListener('change', e => processarXML(e.target.files[0], 'recusa'));
  document.getElementById('xmlFileInput_devolucao').addEventListener('change', e => processarXML(e.target.files[0], 'devolucao'));

  /* =====================================================
   * 3️⃣ Envio do formulário
   * ===================================================== */
  document.getElementById('sendApprovRequest').addEventListener('click', async () => {
    const activeTab = document.querySelector('#returnProcessTabs button.active').id;
    const context = activeTab.includes('recusa') ? 'recusa' : 'devolucao';
    const motivo = document.getElementById(`motivo_${context}`).value;
    const codigoErro = document.getElementById(`codigo_erro_${context}`).value;
    const observacoes = document.getElementById(`observacoes_${context}`).value;
    const gestor = document.getElementById(`gestorSolicitante_${context}`)?.value?.trim() || '';
    const cnpj = document.getElementById(`client-cnpj-display_${context}`).textContent;

    if (!motivo || !codigoErro || !observacoes || cnpj === 'N/A') {
      notyf.error("Preencha todos os campos obrigatórios e carregue um XML válido.");
      return;
    }

    const btn = document.getElementById('sendApprovRequest');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';

    try {
      const formData = new FormData();
      formData.append('tipo', context === 'recusa' ? 'Recusa' : 'Devolução');
      formData.append('nomeCliente', document.getElementById(`client-name-display_${context}`).textContent);
      formData.append('cnpjCliente', cnpj);
      formData.append('motivo', motivo);
      formData.append('codigoErro', codigoErro);
      formData.append('observacao', observacoes);
      formData.append('gestorSolicitante', gestor);

      // XML extraído
      // XML extraído — valida conforme tipo
      if (window._xmlData) {

        if (context === "recusa") {
          formData.append('nf_saida', window._xmlData.nf_saida || '');
          formData.append('recusa_sefaz', window._xmlData.recusa_sefaz || '');
          formData.append('protocolo', window._xmlData.protocolo || '');
          // NÃO ENVIAR: nf_devolucao / nfo
        }

        if (context === "devolucao") {
          formData.append('nf_devolucao', window._xmlData.nf_devolucao || '');
          formData.append('nfo', window._xmlData.nfo || '');
          // NÃO ENVIAR: nf_saida / recusa_sefaz
        }
      }

      // XML file
      const xmlInput = document.getElementById(`xmlFileInput_${context}`);
      if (xmlInput?.files[0]) formData.append('xml_file', xmlInput.files[0]);

      // Itens
      const itens = window._xmlData?.itens || [];
      formData.append('itens', JSON.stringify(itens));

      const res = await fetch('/return-process', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: formData });
      const data = await res.json();

      if (res.ok) {
        notyf.success(data.message || 'Processo salvo com sucesso!');
        setTimeout(() => window.location.href = '/return-process', 1000);
      } else notyf.error(data.message || 'Erro ao salvar processo.');
    } catch (err) {
      console.error(err);
      notyf.error("Falha ao comunicar com o servidor.");
    } finally {
      btn.disabled = false;
      btn.textContent = "Salvar Processo";
    }
  });
});
