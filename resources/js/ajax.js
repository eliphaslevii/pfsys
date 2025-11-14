document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  /* ===============================
     1️⃣ ABRIR MODAL DE EDIÇÃO (GENÉRICO)
  =============================== */
  document.querySelectorAll('[data-edit-url]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const url = btn.dataset.editUrl;
      const target = btn.dataset.modal || '#editModal';

      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        if (!res.ok || !json.success) throw new Error(json.message || "Erro ao carregar dados.");

        // Detecta automaticamente o tipo de entidade retornada
        const modalEl = document.querySelector(target);
        const form = modalEl.querySelector('form');
        if (!form) throw new Error("Formulário não encontrado no modal.");

        // === SE É USER ===
        if (json.user) {
          const { user, sectors, levels } = json;

          form.querySelector('[name="name"]').value = user.name || '';
          form.querySelector('[name="email"]').value = user.email || '';
          const activeInput = form.querySelector('[name="active"]');
          if (activeInput) activeInput.checked = !!user.active;

          // Atualiza selects
          const sectorSelect = form.querySelector('select[name="sector_id"]');
          if (sectorSelect) {
            sectorSelect.innerHTML = '<option value="">Selecione...</option>';
            sectors.forEach(s => {
              const opt = document.createElement('option');
              opt.value = s.id;
              opt.textContent = s.name;
              if (user.sector_id === s.id) opt.selected = true;
              sectorSelect.appendChild(opt);
            });
          }

          const levelSelect = form.querySelector('select[name="level_id"]');
          if (levelSelect && levels) {
            levelSelect.innerHTML = '<option value="">Selecione...</option>';
            levels.forEach(l => {
              const opt = document.createElement('option');
              opt.value = l.id;
              opt.textContent = `${l.name} — ${l.sector?.name || 'Sem setor'}`;
              if (user.level_id === l.id) opt.selected = true;
              levelSelect.appendChild(opt);
            });
          }
        }

        // === SE É LEVEL ===
        if (json.level) {
          const { level, sectors } = json;

          form.querySelector('[name="name"]').value = level.name || '';
          form.querySelector('[name="authority_level"]').value = level.authority_level ?? 10;

          const sectorSelect = form.querySelector('select[name="sector_id"]');
          if (sectorSelect && sectors) {
            sectorSelect.innerHTML = '<option value="">Selecione...</option>';
            sectors.forEach(s => {
              const opt = document.createElement('option');
              opt.value = s.id;
              opt.textContent = s.name;
              if (level.sector_id === s.id) opt.selected = true;
              sectorSelect.appendChild(opt);
            });
          }
        }

        // Atualiza a action do form
        form.action = url.replace('/edit', '');

        // Mostra o modal
        new bootstrap.Modal(modalEl).show();

      } catch (err) {
        console.error("💥 Erro ao carregar dados:", err);
        notyf.error("Falha ao carregar dados para edição.");
      }
    });
  });

  /* ===============================
     2️⃣ SUBMIT UNIVERSAL AJAX
  =============================== */
document.addEventListener('submit', async e => {
    const form = e.target;

    // 🔥 Agora captura forms dentro de modais de qualquer estrutura
    const modal = form.closest('.modal, .modal-dialog, .modal-content');

    if (!modal) return; // não é modal → deixa envio normal
    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(form);
    const override = form.querySelector('input[name="_method"]')?.value?.toUpperCase() || 'POST';
    const method = ['PUT', 'PATCH', 'DELETE'].includes(override) ? override : 'POST';

    console.log("📨 Método detectado:", method);
    console.log("📤 Enviando dados:", Object.fromEntries(formData.entries()));

    try {
        const res = await fetch(form.action, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        let data;
        try {
            data = await res.json();
        } catch {
            const html = await res.text();
            console.error("⚠️ Resposta HTML:", html);
            notyf.error("Resposta inválida do servidor.");
            return;
        }

        console.log("📥 Resposta:", data);

        if (res.ok && data.success) {
            notyf.success(data.message);
            const bsModal = bootstrap.Modal.getInstance(modal.closest('.modal'));
            bsModal?.hide();
            setTimeout(() => location.reload(), 400);
        } else if (res.status === 422) {
            notyf.error(Object.values(data.errors)[0][0]);
        } else {
            notyf.error(data.message || "Erro ao salvar.");
        }

    } finally {
        if (submitBtn) submitBtn.disabled = false;
    }
});


});
