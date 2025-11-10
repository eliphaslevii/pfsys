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

        {{-- DASHBOARD --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="ti ti-layout-dashboard nav-icon"></i> Dashboard
          </a>
        </li>

        {{-- GERENCIAMENTO --}}
        @if(auth()->user()?->hasPermissionTo('Super Admin (TI)') || auth()->user()?->hasPermissionTo('Super Admin (TI)'))
          <li class="nav-item has-submenu">
            <button class="nav-link d-flex justify-content-between align-items-center submenu-toggle" type="button">
              <span><i class="ti ti-settings nav-icon"></i> Gerenciamento</span>
              <i class="ti ti-chevron-down submenu-chevron"></i>
            </button>
            <ul class="navbar-nav flex-column ms-3 submenu">
              @can('manage_users')
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                    href="{{ route('admin.users') }}">
                    <i class="ti ti-users nav-icon"></i> Usuários
                  </a>
                </li>
              @endcan

              @can('manage_sectors')
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.sectors.index') ? 'active' : '' }}"
                    href="{{ route('admin.sectors.index') }}">
                    <i class="ti ti-building nav-icon"></i> Setores e Níveis
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endif

        {{-- RELATÓRIOS (Exemplo extra, se quiser expandir depois) --}}
        @can('view_reports')
          <li class="nav-item has-submenu">
            <button class="nav-link d-flex justify-content-between align-items-center submenu-toggle" type="button">
              <span><i class="ti ti-receipt-refund nav-icon"></i>Devolução</span>
              <i class="ti ti-chevron-down submenu-chevron"></i>
            </button>
            <ul class="navbar-nav flex-column ms-3 submenu">
              <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('return-process.create')}}">Devoluções</a></li>
              <li class="nav-item"><a class="nav-link" href="#">Atendimento</a></li>
            </ul>
          </li>
        @endcan

      </ul>
      <!-- Rodapé -->
      <div class="sidebar-footer mt-auto border-top border-light-subtle pt-3 px-3">
        <div class="dropdown w-100">
          <button
            class="btn d-flex align-items-center justify-content-between w-100 text-start text-white p-2 rounded-2"
            type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false"
            style="background-color: rgba(255, 255, 255, 0.05);">
            <div class="d-flex align-items-center">
              <div class="avatar bg-primary text-white fw-bold me-2">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
              </div>
              <div>
                <div class="fw-semibold small mb-0">{{ auth()->user()->name ?? 'Usuário' }}</div>
                <div class="text-muted small">Conectado</div>
              </div>
            </div>
            <i class="ti ti-chevron-up ms-2 opacity-50"></i>
          </button>

          <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm border-0 w-100" aria-labelledby="userMenuButton"
            style="min-width: 230px;">
            <li>
              <a class="dropdown-item d-flex align-items-center">
                <i class="ti ti-user me-2 text-primary"></i> Perfil
              </a>
            </li>
            <li>
              <button type="button" class="dropdown-item d-flex align-items-center" id="toggleDarkMode">
                <i class="ti ti-moon me-2 text-secondary"></i> Alternar tema
              </button>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item d-flex align-items-center text-danger">
                  <i class="ti ti-logout me-2"></i> Sair
                </button>
              </form>
            </li>
          </ul>
        </div>
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


</body>

</html>