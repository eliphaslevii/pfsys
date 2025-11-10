document.addEventListener('DOMContentLoaded', () => {
  console.log('⚙️ layout.js carregado');

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const notyf = new Notyf();

  const sidebar = document.querySelector('aside.navbar-vertical');
  const toggler = document.getElementById('menu-toggler');
  const toggleGerenciamento = document.getElementById('toggleGerenciamento');
  const submenu = document.getElementById('gerenciamentoSubmenu');
  const chevron = document.getElementById('gerenciamentoChevron');

  /* ===============================
     📱 MENU LATERAL (MOBILE)
  =============================== */
  function openDrawer() {
    sidebar.classList.add('show');
    const backdrop = document.createElement('div');
    backdrop.className = 'drawer-backdrop';
    backdrop.id = 'drawerBackdrop';
    document.body.appendChild(backdrop);
    backdrop.addEventListener('click', closeDrawer);
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    sidebar.classList.remove('show');
    document.getElementById('drawerBackdrop')?.remove();
    document.body.style.overflow = '';
  }

  toggler?.addEventListener('click', () => {
    sidebar.classList.contains('show') ? closeDrawer() : openDrawer();
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) closeDrawer();
  });

  /* ===============================
    🧭 SUBMENUS DINÂMICOS UNIVERSAIS
    =============================== */
  document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', e => {
      e.preventDefault();

      const parent = toggle.closest('.has-submenu');
      const submenu = parent.querySelector('.submenu');
      const chevron = toggle.querySelector('.submenu-chevron');

      // Fecha todos os outros submenus antes de abrir o atual
      document.querySelectorAll('.submenu').forEach(el => {
        if (el !== submenu) el.style.display = 'none';
      });
      document.querySelectorAll('.submenu-chevron').forEach(icon => {
        if (icon !== chevron) icon.classList.remove('rotate');
      });

      // Alterna o submenu atual
      const isOpen = submenu.style.display === 'block';
      submenu.style.display = isOpen ? 'none' : 'block';
      chevron.classList.toggle('rotate', !isOpen);
    });
  });


  /* ===============================
     🗑️ MODAL UNIVERSAL DE EXCLUSÃO
  =============================== */
  const deleteModalEl = document.getElementById('confirmDeleteModal');
  const confirmBtn = document.getElementById('confirmDeleteBtn');

  if (deleteModalEl && confirmBtn) {
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    let deleteUrl = null;

    document.addEventListener('click', e => {
      const btn = e.target.closest('.btn-delete[data-url]');
      if (!btn) return;
      e.preventDefault();
      deleteUrl = btn.dataset.url;
      deleteModal.show();
    });

    confirmBtn.addEventListener('click', async () => {
      if (!deleteUrl) return;
      confirmBtn.disabled = true;
      try {
        const res = await fetch(deleteUrl, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        if (res.ok && data.success) {
          notyf.success(data.message || 'Registro excluído com sucesso!');
          deleteModal.hide();
          setTimeout(() => location.reload(), 700);
        } else {
          notyf.error(data.message || 'Erro ao excluir registro.');
        }
      } catch (err) {
        console.error(err);
        notyf.error('Falha na comunicação com o servidor.');
      } finally {
        confirmBtn.disabled = false;
        deleteUrl = null;
      }
    });
  }
});
// Dark Mode Toggle
document.addEventListener("DOMContentLoaded", function () {
  const darkBtn = document.getElementById('toggleDarkMode');
  const body = document.body;

  // aplica o tema salvo
  if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark-mode');
  }

  if (darkBtn) {
    darkBtn.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      const icon = darkBtn.querySelector('i');
      icon.className = isDark ? 'ti ti-sun me-2 text-warning' : 'ti ti-moon me-2 text-secondary';
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('userMenuButton');
  if (!btn) return;
  // dropdown-menu é o próximo .dropdown-menu no DOM do mesmo container
  const dropdown = btn.closest('.dropdown').querySelector('.dropdown-menu');

  // Se existir algum data-bs-* (Bootstrap) e vc não quer usar Bootstrap, remova atributo para evitar conflito:
  btn.removeAttribute('data-bs-toggle');

  // Toggle ao clicar no botão
  btn.addEventListener('click', function (ev) {
    ev.stopPropagation();
    const isOpen = dropdown.classList.toggle('show');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // Fecha quando clicar fora
  document.addEventListener('click', function (ev) {
    if (!dropdown.classList.contains('show')) return;
    if (!dropdown.contains(ev.target) && !btn.contains(ev.target)) {
      dropdown.classList.remove('show');
      btn.setAttribute('aria-expanded', 'false');
    }
  });

  // Fecha com ESC
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && dropdown.classList.contains('show')) {
      dropdown.classList.remove('show');
      btn.setAttribute('aria-expanded', 'false');
    }
  });

  // Toggle darkmode do dropdown (se exist)
  const darkBtn = document.getElementById('toggleDarkMode');
  if (darkBtn) {
    darkBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      // troca ícone se quiser
      const i = darkBtn.querySelector('i');
      if (i) {
        i.className = isDark ? 'ti ti-sun me-2 text-warning' : 'ti ti-moon me-2 text-secondary';
      }
    });
  }
});