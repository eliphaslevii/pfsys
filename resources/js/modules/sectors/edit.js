document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('editSectorModal');
  if (!modalEl) return;
  const form = modalEl.querySelector('form');
  if (!form) return;

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  document.querySelectorAll('[data-edit-url]').forEach(btn => {
    btn.addEventListener('click', async (ev) => {
      // só reagir se o data-modal apontar para o nosso modal (evita conflito)
      const target = btn.dataset.modal || null;
      if (target && target !== '#editSectorModal') return;

      const url = btn.dataset.editUrl;
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        if (!res.ok) {
          const text = await res.text();
          throw new Error(`HTTP ${res.status} — response: ${text.substring(0,200)}`);
        }
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Falha ao carregar dados');

        const sector = json.sector;
        form.action = `/admin/sectors/${sector.id}`; // rota patch

        form.querySelector('[name="name"]').value = sector.name ?? '';
        form.querySelector('[name="description"]').value = sector.description ?? '';
        form.querySelector('[name="is_active"]').checked = !!sector.is_active;

        const parentSelect = form.querySelector('[name="parent_id"]');
        parentSelect.innerHTML = '<option value="">Nenhum</option>';
        (json.parents || []).forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.name;
          if (sector.parent_id == p.id) opt.selected = true;
          parentSelect.appendChild(opt);
        });

        modal.show();

      } catch (err) {
        console.error('Erro ao carregar modal edit:', err);
        notyf.error('Não foi possível carregar dados');
      }
    });
  });

  // submit update
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;

    const fd = new FormData(form);
    fd.append('_method', 'PATCH');

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: fd
      });

      const json = await res.json();

      if (!res.ok || !json.success) {
        if (res.status === 422) notyf.error(Object.values(json.errors)[0][0]);
        else notyf.error(json.message || 'Erro ao salvar.');
        btn.disabled = false;
        return;
      }

      notyf.success(json.message || 'Alterado com sucesso.');
      modal.hide();
      setTimeout(() => location.reload(), 300);

    } catch (err) {
      console.error(err);
      notyf.error('Erro inesperado.');
      btn.disabled = false;
    }
  });
});
