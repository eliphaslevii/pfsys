/**
 * CoreFlow - Return Process Creation
 * Adaptado do sistema antigo para o layout com abas (Recusa / Devolução)
 * ---------------------------------------------------------------
 * - Processa XML
 * - Popula campos automaticamente
 * - Envia via AJAX moderno (fetch)
 */

document.addEventListener('DOMContentLoaded', () => {
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  /* =====================================================
   * 1️⃣ Configurações de motivos e códigos
   * ===================================================== */
  const motivosBase = {
    grupos: [
      { label: "Notas Fiscais e Reentregas", valores: ["Emissão de nova nota fiscal + reentrega", "Somente Emissão de nova nota fiscal", "Somente ajuste de estoque", "Baixa financeira"] },
      { label: "Condições Especiais (Retorno para PFERD)", valores: ["Retorno do material para a PFERD", "Retorno de material para PFERD + Envio de nova remessa", "Retorno de Material para a PFERD + Transporte CLIENTE", "Retorno de Material para a PFERD + Transporte PFERD"] },
      { label: "Sucateamento / Descarte", valores: ["Material Descartado", "Devolução + sucateamento"] }
    ]
  };

  const codigosErroBase = {
    grupos: [
      { label: "Erros Comerciais", valores: ["Acordo comercial", "Negociação comercial", "Pedido programado", "Preço errado", "Preço negociado não informado", "Faturamento sem autorização", "Comprou errado", "Recusa indevida", "Outros"] },
      { label: "Erros Fiscais e Tributários", valores: ["Alteração ICMS (FCI)", "Redução IPI", "Imposto incorreto", "CNPJ incorreto"] },
      { label: "Erros Logísticos e de Transporte", valores: ["Embarque não autorizado", "Erro no transporte", "Cobrança de frete", "Frete incorreto", "Extravio mercadoria (transportadora)", "Sem corte no físico"] },
      { label: "Erros de Produto / Quantidade", valores: ["Produto errado", "Produto com defeito", "Produto divergente (físico)", "Quantidade divergente (físico)", "Quantidade errada", "Substituição de item", "Conserto", "Retorno demonstração", "Utilização incorreta (consumo / industrialização)"] },
      { label: "Erros de Embalagem / Sistema / Avaria", valores: ["Avaria material (embalagem ou produto)", "Erro embalagem (pallet/etiqueta)", "Erro sistêmico", "Erro de digitação", "Duplicidade"] }
    ]
  };

  /* =====================================================
   * 2️⃣ Função para popular selects de motivos e códigos
   * ===================================================== */
  function popularSelects(context) {
    const motivoSelect = document.getElementById(`motivo_${context}`);
    const codigoSelect = document.getElementById(`codigo_erro_${context}`);
    if (!motivoSelect || !codigoSelect) return;

    motivoSelect.innerHTML = '<option value="">Selecione...</option>';
    motivosBase.grupos.forEach(g => {
      const group = document.createElement('optgroup');
      group.label = g.label;
      g.valores.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v;
        opt.textContent = v;
        group.appendChild(opt);
      });
      motivoSelect.appendChild(group);
    });

    codigoSelect.innerHTML = '<option value="">Selecione...</option>';
    codigosErroBase.grupos.forEach(g => {
      const group = document.createElement('optgroup');
      group.label = g.label;
      g.valores.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v;
        opt.textContent = v;
        group.appendChild(opt);
      });
      codigoSelect.appendChild(group);
    });
  }

  ['recusa', 'devolucao'].forEach(ctx => popularSelects(ctx));

  /* =====================================================
   * 3️⃣ Leitura e parsing do XML
   * ===================================================== */
  function processarXML(file, context) {
    const reader = new FileReader();
    reader.onload = ev => {
      try {
        const parser = new DOMParser();
        const xml = parser.parseFromString(ev.target.result, "text/xml");
        const ns = "http://www.portalfiscal.inf.br/nfe";

        // Buscas com namespace SEFAZ
        const emit = xml.getElementsByTagNameNS(ns, "emit")[0];
        const dest = xml.getElementsByTagNameNS(ns, "dest")[0];
        const ide = xml.getElementsByTagNameNS(ns, "ide")[0];
        const infCplNode = xml.getElementsByTagNameNS(ns, "infCpl")[0];
        const nProtNode = xml.getElementsByTagNameNS(ns, "nProt")[0];

        // Determina se cliente é emitente ou destinatário conforme aba
        let nomeCliente = "N/A";
        let cnpjCliente = "N/A";
        if (context === 'recusa' && dest) {
          nomeCliente = dest.getElementsByTagNameNS(ns, "xNome")[0]?.textContent || "N/A";
          cnpjCliente = dest.getElementsByTagNameNS(ns, "CNPJ")[0]?.textContent || "N/A";
        } else if (emit) {
          nomeCliente = emit.getElementsByTagNameNS(ns, "xNome")[0]?.textContent || "N/A";
          cnpjCliente = emit.getElementsByTagNameNS(ns, "CNPJ")[0]?.textContent || "N/A";
        }

        const nfSaida = ide?.getElementsByTagNameNS(ns, "nNF")[0]?.textContent || "N/A";
        const nProt = nProtNode?.textContent || "N/A";
        const infCpl = infCplNode?.textContent || "N/A";

        // Atualiza campos visuais
        document.getElementById(`client-name-display_${context}`).textContent = nomeCliente;
        document.getElementById(`client-cnpj-display_${context}`).textContent = cnpjCliente;
        document.getElementById(`nf-saida-display_${context}`).textContent = nfSaida;
        document.getElementById(`nprot-display_${context}`).textContent = nProt;
        document.getElementById(`inf-cpl-display_${context}`).textContent = infCpl;

        // Itens da nota
        const itens = xml.getElementsByTagNameNS(ns, "det");
        const tbody = document.getElementById(`product-table-body_${context}`);
        tbody.innerHTML = "";

        if (!itens.length) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum item encontrado.</td></tr>';
          return;
        }

        for (let item of itens) {
          const prod = item.getElementsByTagNameNS(ns, "prod")[0];
          if (!prod) continue;

          const artigo = (prod.getElementsByTagNameNS(ns, "cProd")[0]?.textContent || "").replace(/^0+/, "");
          const descricao = prod.getElementsByTagNameNS(ns, "xProd")[0]?.textContent || "";
          const ncm = prod.getElementsByTagNameNS(ns, "NCM")[0]?.textContent || "";
          const qtd = parseFloat(prod.getElementsByTagNameNS(ns, "qCom")[0]?.textContent || 0).toFixed(2);
          const preco = parseFloat(prod.getElementsByTagNameNS(ns, "vUnCom")[0]?.textContent || 0).toFixed(2);

          tbody.innerHTML += `
          <tr>
            <td>${artigo}</td>
            <td>${descricao}</td>
            <td>${ncm}</td>
            <td>${nfSaida}</td>
            <td>N/A</td>
            <td>${qtd}</td>
            <td>R$ ${preco.replace('.', ',')}</td>
          </tr>
        `;
        }

        notyf.success("XML importado com sucesso!");
      } catch (err) {
        console.error("Erro ao processar XML:", err);
        notyf.error("Falha ao processar o XML. Verifique o arquivo.");
      }
    };
    reader.readAsText(file);
  }


  document.getElementById('xmlFileInput_recusa').addEventListener('change', e => processarXML(e.target.files[0], 'recusa'));
  document.getElementById('xmlFileInput_devolucao').addEventListener('change', e => processarXML(e.target.files[0], 'devolucao'));

  /* =====================================================
   * 4️⃣ Envio do formulário (AJAX)
   * ===================================================== */
  document.getElementById('sendApprovRequest').addEventListener('click', async () => {
    const activeTab = document.querySelector('#returnProcessTabs button.active').id;
    const context = activeTab.includes('recusa') ? 'recusa' : 'devolucao';
    const form = document.getElementById(`form_${context}`);

    const motivo = document.getElementById(`motivo_${context}`).value;
    const codigoErro = document.getElementById(`codigo_erro_${context}`).value;
    const observacoes = document.getElementById(`observacoes_${context}`).value;
    const gestor = document.getElementById(`gestorSolicitante_${context}`).value;
    const cnpj = document.getElementById(`client-cnpj-display_${context}`).textContent;

    if (!motivo || !codigoErro || !observacoes || cnpj === 'N/A') {
      notyf.error("Preencha todos os campos obrigatórios e carregue um XML válido.");
      return;
    }

    const btn = document.getElementById('sendApprovRequest');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';

    const payload = {
      tipo: context === 'recusa' ? 'Recusa' : 'Devolução',
      nomeCliente: document.getElementById(`client-name-display_${context}`).textContent,
      cnpjCliente: cnpj,
      motivo,
      codigoErro,
      observacao: observacoes,
      gestorSolicitante: gestor,
    };

    try {
      // dentro do bloco try do create.js
      const formData = new FormData();
      formData.append('tipo', payload.tipo);
      formData.append('nomeCliente', payload.nomeCliente);
      formData.append('cnpjCliente', payload.cnpjCliente);
      formData.append('motivo', payload.motivo);
      formData.append('codigoErro', payload.codigoErro);
      formData.append('observacao', payload.observacao);
      formData.append('gestorSolicitante', payload.gestorSolicitante);

      // arquivo XML (ajuste o nome conforme validação do controller)
      const xmlInput = document.getElementById(`xmlFileInput_${context}`);
      if (xmlInput && xmlInput.files[0]) {
        formData.append('xml_file', xmlInput.files[0]); // <-- usar snake_case se o controller usa isso
      }

      // coleta os itens da tabela
      const itens = [];
      document.querySelectorAll(`#product-table-body_${context} tr`).forEach(tr => {
        const cols = tr.querySelectorAll('td');
        if (cols.length === 7 && !cols[0].textContent.includes('Nenhum')) {
          itens.push({
            artigo: cols[0].textContent.trim(),
            descricao: cols[1].textContent.trim(),
            ncm: cols[2].textContent.trim(),
            nf_saida: cols[3].textContent.trim(),
            nf_devolucao: cols[4].textContent.trim(),
            quantidade: parseFloat(cols[5].textContent.trim().replace(',', '.')),
            preco_unitario: parseFloat(cols[6].textContent.replace('R$', '').replace(',', '.'))
          });
        }
      });
      formData.append('itens', JSON.stringify(itens));

      const res = await fetch('/return-process', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
        body: formData
      });

      const data = await res.json();
      if (res.ok) {
        notyf.success(data.message || 'Processo salvo com sucesso!');
        setTimeout(() => window.location.href = '/return-process', 1000);
      } else {
        notyf.error(data.message || 'Erro ao salvar processo.');
      }
    } catch (err) {
      console.error(err);
      notyf.error("Falha ao comunicar com o servidor.");
    } finally {
      btn.disabled = false;
      btn.textContent = "Salvar Processo";
    }
  });
});
// Toggle de exibição dos itens do XML
document.querySelectorAll('.toggle-items-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    const container = btn.closest('.tab-pane').querySelector('.items-container');
    const icon = btn.querySelector('i');
    const showing = container.style.display === 'block';

    if (showing) {
      container.style.display = 'none';
      icon.classList.replace('ti-chevron-up', 'ti-chevron-down');
      btn.innerHTML = '<i class="ti ti-chevron-down me-1"></i> Mostrar Itens';
    } else {
      container.style.display = 'block';
      icon.classList.replace('ti-chevron-down', 'ti-chevron-up');
      btn.innerHTML = '<i class="ti ti-chevron-up me-1"></i> Ocultar Itens';
    }
  });
});
document.addEventListener('DOMContentLoaded', function () {
    console.log("✅ Script do dropdown carregado");

    const btn = document.querySelector('#userMenuButton');
    if (!btn) {
      console.warn("❌ Botão #userMenuButton não encontrado!");
      return;
    }

    const dropdown = btn.closest('.dropdown')?.querySelector('.dropdown-menu');
    if (!dropdown) {
      console.warn("❌ Menu dropdown não encontrado!");
      return;
    }

    // Garantir que o bootstrap não interfira
    btn.removeAttribute('data-bs-toggle');
    btn.removeAttribute('data-bs-target');

    // Toggle manual do dropdown
    btn.addEventListener('click', function (ev) {
      ev.stopPropagation();
      dropdown.classList.toggle('show');
      console.log("🔁 Toggle menu:", dropdown.classList.contains('show') ? "ABERTO" : "FECHADO");
    });

    // Fecha ao clicar fora
    document.addEventListener('click', function (ev) {
      if (!dropdown.contains(ev.target) && !btn.contains(ev.target)) {
        dropdown.classList.remove('show');
      }
    });

    // Fecha com ESC
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') {
        dropdown.classList.remove('show');
      }
    });

    // Dark mode toggle
    const darkBtn = document.querySelector('#toggleDarkMode');
    if (darkBtn) {
      darkBtn.addEventListener('click', function (ev) {
        ev.stopPropagation();
        document.body.classList.toggle('dark-mode');
        const dark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
        console.log(`🌙 Tema: ${dark ? 'escuro' : 'claro'}`);
      });
    }
  });