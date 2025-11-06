<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ config('app.name', 'CoreFlow') }}</title>

  <!-- Tabler -->
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta22/dist/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

  <!-- Notyf -->
  <link href="{{ asset('notyf/notyf.min.css') }}" rel="stylesheet" />

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root {
      --sidebar-bg: #354A5F;
      --sidebar-hover: #475E75;
      --sidebar-text: #FFFFFF;
      --sidebar-muted: #cbd5e1;
      --border-color: #4a6075;
      --drawer-width: 260px;
    }

    body {
      background: #F4F7FA;
      overflow-x: hidden;
    }

    /* SIDEBAR */
    aside.navbar-vertical {
      background: var(--sidebar-bg);
      color: var(--sidebar-text);
      transition: transform 0.3s ease;
      width: var(--drawer-width);
      z-index: 1250;
    }

    .navbar-vertical .nav-link {
      color: var(--sidebar-muted);
      border-radius: 6px;
      font-weight: 400;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 10px 14px;
    }

    .navbar-vertical .nav-link:hover,
    .navbar-vertical .nav-link.active {
      background-color: var(--sidebar-hover);
      color: #fff !important;
    }

    .navbar-vertical .nav-icon {
      font-size: 1.2rem;
      margin-right: 10px;
    }

    .sidebar-footer {
      border-top: 1px solid var(--border-color);
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sidebar-footer .avatar {
      width: 38px;
      height: 38px;
      background: var(--sidebar-hover);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    .sidebar-footer .user-info {
      margin-left: 10px;
    }

    .sidebar-footer .user-info .name {
      font-weight: 600;
      color: #fff;
      line-height: 1;
    }

    /* Responsividade */
    @media (min-width: 992px) {
      aside.navbar-vertical {
        position: fixed;
        height: 100%;
        left: 0;
        top: 0;
        transform: translateX(0);
        border-right: 1px solid var(--border-color);
      }

      .page-wrapper {
        margin-left: var(--drawer-width);
        padding: 20px;
      }

      .mobile-topbar {
        display: none;
      }
    }

    @media (max-width: 991.98px) {
      aside.navbar-vertical {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        transform: translateX(-100%);
      }

      aside.navbar-vertical.show {
        transform: translateX(0);
        box-shadow: 0 15px 40px rgba(0, 0, 0, .4);
      }

      .page-wrapper {
        margin: 0;
        padding: 18px;
      }
    }

    .drawer-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .45);
      z-index: 1200;
    }

    .mobile-topbar {
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      background: #fff;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
      z-index: 1300;
    }

    .rotate {
      transform: rotate(180deg);
      transition: transform 0.3s ease;
    }
  </style>
</head>

<body>
  <!-- Topbar Mobile -->
  <div class="mobile-topbar d-lg-none">
    <div class="d-flex align-items-center">
      <button id="menu-toggler" class="btn btn-ghost p-0"><i class="ti ti-menu-2 fs-3"></i></button>
      <span class="ms-2 fw-bold text-primary">{{ config('app.name', 'CoreFlow') }}</span>
    </div>
  </div>

  <!-- SIDEBAR -->
  <aside class="navbar navbar-vertical navbar-dark">
    <div class="container-fluid flex-column">
      <h1 class="navbar-brand navbar-brand-autodark mt-3 mb-3">
        <a href="{{ route('dashboard') }}" class="text-white text-decoration-none">CoreFlow</a>
      </h1>

      <ul class="navbar-nav flex-column w-100">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="ti ti-layout-dashboard nav-icon"></i> Dashboard
          </a>
        </li>

        <li class="nav-item">
          <button id="toggleGerenciamento" class="nav-link" title="Expandir">
            Gerenciamento
            <i id="gerenciamentoChevron" class="ti ti-chevron-down"></i>
          </button>
        </li>

        <ul id="gerenciamentoSubmenu" class="navbar-nav flex-column ms-3 collapse show">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
              href="{{ route('admin.users') }}">
              <i class="ti ti-users nav-icon"></i> Usuários
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.sectors.index') ? 'active' : '' }}"
              href="{{ route('admin.sectors.index') }}">
              <i class="ti ti-users nav-icon"></i> Setores
            </a>
          </li>
        </ul>
      </ul>

      <!-- Rodapé -->
      <div class="sidebar-footer mt-auto">
        <div class="d-flex align-items-center">
          <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
          <div class="user-info">
            <div class="name">{{ auth()->user()->name ?? 'Usuário' }}</div>
          </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-link text-white p-0" title="Sair">
            <i class="ti ti-logout"></i>
          </button>
        </form>
      </div>
    </div>
  </aside>

  <!-- Conteúdo -->
  <div class="page-wrapper">
    <div class="container-xl mt-4">
      @yield('content')
    </div>
  </div>

  <!-- Modal Excluir -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Confirmar exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p>Deseja realmente excluir este usuário? Esta ação não pode ser desfeita.</p>
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" id="confirmDeleteBtn">Excluir</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta22/dist/js/tabler.min.js"></script>
  <script src="{{ asset('notyf/notyf.min.js') }}"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sidebar = document.querySelector('aside.navbar-vertical');
      const toggler = document.getElementById('menu-toggler');
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

      /* ===================
         MOBILE MENU TOGGLE
      =================== */
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

      /* ===================
         DROPDOWN GERENCIAMENTO
      =================== */
      const toggleBtn = document.getElementById('toggleGerenciamento');
      const submenu = document.getElementById('gerenciamentoSubmenu');
      const chevron = document.getElementById('gerenciamentoChevron');

      toggleBtn.addEventListener('click', () => {
        const isShown = submenu.classList.contains('show');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate', !isShown);
      });

      /* ===================
         DELETE MODAL
      =================== */
      const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
      const confirmBtn = document.getElementById('confirmDeleteBtn');
      let deleteUrl = null;

      document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-delete[data-url]');
        if (!btn) return;
        e.preventDefault();
        deleteUrl = btn.dataset.url;
        modal.show();
      });

      confirmBtn.addEventListener('click', async () => {
        if (!deleteUrl) return;
        confirmBtn.disabled = true;
        try {
          const res = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
          });
          if (res.ok) {
            notyf.success('Usuário excluído com sucesso!');
            modal.hide();
            setTimeout(() => location.reload(), 800);
          } else notyf.error('Erro ao excluir usuário.');
        } catch {
          notyf.error('Falha na comunicação com o servidor.');
        } finally {
          confirmBtn.disabled = false;
          deleteUrl = null;
        }
      });
    });

    document.addEventListener('DOMContentLoaded', () => {
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const notyf = new Notyf({
        duration: 3000,
        ripple: true,
        dismissible: true,
        position: { x: 'right', y: 'top' }
      });

      document.querySelectorAll('.modal form').forEach(form => {
        const modalEl = form.closest('.modal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

        form.addEventListener('submit', async e => {
          e.preventDefault();

          const formData = new FormData(form);
          const submitBtn = form.querySelector('button[type="submit"]');
          submitBtn.disabled = true;

          try {
            const methodField = form.querySelector('input[name="_method"]');
            let method = form.method || 'POST';
            if (methodField) method = methodField.value.toUpperCase();

            const response = await fetch(form.action, {
              method: method,
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
              },
              body: formData
            });

            const data = await response.json();
            if (response.ok && data.success) {
              notyf.success(data.message || 'Operação concluída!');
              modal.hide();
              setTimeout(() => window.location.reload(), 800);
            }
            else if (response.status === 422 && data.errors) {
              // 🔥 Tratamento elegante de erros de validação
              let firstError = null;

              for (const [field, messages] of Object.entries(data.errors)) {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                  input.classList.add('is-invalid');

                  let feedback = input.nextElementSibling;
                  if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    input.insertAdjacentElement('afterend', feedback);
                  }
                  feedback.textContent = messages[0];
                }

                if (!firstError) firstError = messages[0];
              }

              notyf.error(firstError || 'Verifique os campos e tente novamente.');
            }
            else {
              notyf.error(data.message || 'Erro ao processar requisição.');
            }

          } catch (err) {
            console.error(err);
            notyf.error('Falha na comunicação com o servidor.');
          } finally {
            submitBtn.disabled = false;
          }
        });
      });

      // DELETE UNIVERSAL
      document.querySelectorAll('.btn-delete[data-url]').forEach(btn => {
        btn.addEventListener('click', async e => {
          e.preventDefault();
          const url = btn.dataset.url;
          if (!url) return;

          const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
          const confirmBtn = document.getElementById('confirmDeleteBtn');
          confirmModal.show();

          confirmBtn.onclick = async () => {
            try {
              const res = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
              });
              const data = await res.json();
              if (res.ok && data.success) {
                notyf.success(data.message || 'Registro removido!');
                confirmModal.hide();
                setTimeout(() => window.location.reload(), 800);
              } else {
                notyf.error(data.message || 'Erro ao excluir.');
              }
            } catch (err) {
              console.error(err);
              notyf.error('Erro ao comunicar com o servidor.');
            }
          };
        });
      });
    });
    document.addEventListener('DOMContentLoaded', () => {
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

      /* ===============================
         UNIVERSAL: EDIÇÃO VIA MODAL
         (para usuários, setores, etc.)
      =============================== */
      document.querySelectorAll('[data-edit-url]').forEach(btn => {
        btn.addEventListener('click', async () => {
          const url = btn.dataset.editUrl;
          const modalId = btn.dataset.modal; // Ex: "#editUserModal" ou "#sectorEditModal"

          try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return notyf.error('Erro ao carregar dados.');
            const data = await res.json();

            // Suporte universal: se vier "user" ou "sector" no JSON
            const item = data.user || data.sector || data.level;
            if (!item) return notyf.error('Erro: resposta inválida do servidor.');

            // Preenche campos automaticamente conforme os nomes dos inputs
            Object.entries(item).forEach(([key, value]) => {
              const input = document.querySelector(`${modalId} [name="${key}"]`);
              if (!input) return;

              if (input.type === 'checkbox') {
                input.checked = !!value;
              } else {
                input.value = value ?? '';
              }
            });

            // Define o action do form (ex: /admin/users/{id})
            const form = document.querySelector(`${modalId} form`);
            if (form) {
              form.action = url.replace('/edit', ''); // converte /edit → /{id}
            }

            // Abre o modal
            const modal = new bootstrap.Modal(document.querySelector(modalId));
            modal.show();

          } catch (err) {
            console.error(err);
            notyf.error('Erro ao comunicar com o servidor.');
          }
        });
      });
    });
  </script>
  @stack('scripts')

</body>

</html>