document.addEventListener('DOMContentLoaded', () => {
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  document.querySelectorAll('.btn-delete-sector').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      if (!confirm('Deseja realmente excluir este item?')) return;

      const url = btn.dataset.url;
      try {
        const res = await fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          }
        });

        const json = await res.json();
        if (!res.ok || !json.success) {
          notyf.error(json.message || 'Erro ao excluir.');
          return;
        }

        notyf.success(json.message || 'Excluído.');
        setTimeout(() => location.reload(), 200);
      } catch (err) {
        console.error(err);
        notyf.error('Erro inesperado.');
      }
    });
  });
});
