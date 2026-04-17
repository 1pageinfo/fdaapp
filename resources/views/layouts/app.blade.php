<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel App') }}</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="{{ asset('theme/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('theme/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('theme/vendors/css/vendor.bundle.base.css') }}">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="{{ asset('theme/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
  <link rel="stylesheet" href="{{ asset('theme/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('theme/js/select.dataTables.min.css') }}">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{ asset('theme/css/vertical-layout-light/style.css') }}">
  <!-- endinject -->
  <link rel="shortcut icon" href="{{ asset('theme/images/logo.jpg') }}" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('styles')

</head>

<body>
  <div class="container-scroller">
    <!-- Top Navbar-->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <!-- Brand -->
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="{{ url('/dashboard') }}">
          <img src="{{ asset('theme/images/logo-dash.png') }}" class="mr-2" alt="logo" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ url('/dashboard') }}">
          <img src="{{ asset('theme/images/logo.jpg') }}" alt="logo" />
        </a>
      </div>

      <!-- Right Side -->
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <!-- Sidebar toggle -->
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>

        <!-- Search -->
        <ul class="navbar-nav mr-lg-2">
          <li class="nav-item nav-search d-none d-lg-block">
            <form class="input-group" action="{{ route('search.index') }}" method="GET">
              <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
                <span class="input-group-text" id="search"><i class="icon-search"></i></span>
              </div>
              <input type="text" class="form-control" name="q" id="navbar-search-input"
                placeholder="Search receipts, sanghs, files, meetings, groups" aria-label="search"
                value="{{ request('q') }}">
            </form>
          </li>
        </ul>

        <!-- Right Icons -->
        <ul class="navbar-nav navbar-nav-right">
          <!-- Notifications -->
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#"
              data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" role="button">
              <i class="icon-bell mx-0"></i>
              <span id="notifBadge" class="count d-none">0</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
              aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal dropdown-header">
                <b><u>Tomorrow’s Meetings/Events</u></b>
              </p>

              <div id="notifList" style="max-height:320px; overflow:auto;">
                <div class="p-3 text-muted small">Loading…</div>
              </div>

              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-end small" href="{{ route('meetings.index') }}">
                Open calendar →
              </a>
            </div>
          </li>


          <!-- Profile -->
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img
                src="{{ auth()->user()->photo_path ? Storage::url('app/public/' . auth()->user()->photo_path) : asset('theme/images/default-avatar.png') }}"
                alt="profile" class="rounded-circle" style="object-fit: cover; width:32px; height:32px;">
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="{{ route('profile.index') }}">
                <i class="ti-user text-primary"></i> Profile
              </a>
              <a class="dropdown-item" href="{{ route('settings.index') }}">
                <i class="ti-settings text-primary"></i> Settings
              </a>
              <div class="dropdown-divider"></div>
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class="ti-power-off text-primary"></i> Logout
                </button>
              </form>
            </div>
          </li>
        </ul>

        <!-- Mobile menu toggle -->
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>

    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_settings-panel.html -->



      <!-- Left Sidebar -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          {{-- Dashboard --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}"
              data-perm="dashboard.view">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          {{-- Folders --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('folders*') ? 'active' : '' }}" href="{{ route('folders.index') }}"
              data-perm="folders.view">
              <i class="icon-layout menu-icon"></i>
              <span class="menu-title">Folders</span>
            </a>
          </li>

          {{-- Sanghs --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('sanghs*') ? 'active' : '' }}" href="{{ route('sanghs.index') }}"
              data-perm="sanghs.view">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Sanghs</span>
            </a>
          </li>

          {{-- Groups --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('groups*') ? 'active' : '' }}" href="{{ route('groups.index') }}"
              data-perm="groups.view">
              <i class="ti-comments menu-icon"></i>
              <span class="menu-title">Groups</span>
            </a>
          </li>

          {{-- Meetings --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('meetings*') ? 'active' : '' }}" href="{{ route('meetings.index') }}"
              data-perm="meetings.view">
              <i class="ti-calendar menu-icon"></i>
              <span class="menu-title">Meetings</span>
            </a>
          </li>
    {{-- Meetings --}}
          <li class="nav-item">
            <a class="nav-link" href="{{ route('links.index') }}"
              data-perm="meetings.view">
              <i class="ti-link menu-icon"></i>
              <span class="menu-title">Links</span>
            </a>
          </li>


          {{-- Accounts
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#receiptsMenu"
              aria-expanded="{{ request()->is('receipts*') ? 'true' : 'false' }}" aria-controls="receiptsMenu"
              data-perm="receipts.view">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Accounts</span>
              <i class="menu-arrow"></i>
            </a>

            <div class="collapse" id="receiptsMenu">
              <ul class="nav flex-column sub-menu">

                <li class="nav-item">
                  <a class="nav-link" href="{{ route('receipts.create') }}">
                    Receipts
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" href="#">
                    Paymennts
                  </a>
                </li>

              </ul>
            </div>
          </li>
          --}}

          {{-- Settings --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->is('settings') ? 'active' : '' }}" href="{{ route('settings.index') }}"
              data-perm="settings.view">
              <i class="ti-settings menu-icon"></i>
              <span class="menu-title">Settings</span>
            </a>
          </li>
          </br></br></br></br></br>

          <li class="nav-item mt-3 px-3 text-white">
            <span class="d-block"><i class="ti-user menu-icon"></i> {{ auth()->user()->name }}</span>
          </li>
          <li class="nav-item px-3 mt-2">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="btn btn-sm btn-danger w-100" type="submit">LOGOUT</button>
            </form>
          </li>
        </ul>
      </nav>
      <!-- Body -->
      <div class="main-panel">

        @yield('content')


      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <script>
      (function () {
        // Safely embed the route URL
        const url = @json(route('notifications.index'));

        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        const dd = document.getElementById('notificationDropdown');
        if (!badge || !list || !dd) return;

        // Basic HTML escape to prevent accidental injection from server data
        const esc = (s) => String(s ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');

        async function fetchNotifs() {
          try {
            const u = new URL(url, window.location.origin);
            u.searchParams.set('t', Date.now()); // cache-bust

            const res = await fetch(u.toString(), {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
              },
              credentials: 'same-origin',
              cache: 'no-store'
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const ct = (res.headers.get('Content-Type') || '').toLowerCase();
            if (!ct.includes('application/json')) throw new Error('Unexpected content type');

            const { count = 0, items = [] } = await res.json();

            // Badge update
            if (count > 0) {
              badge.classList.remove('d-none');
              badge.textContent = count;
              badge.setAttribute('aria-label', count + ' notifications');
            } else {
              badge.classList.add('d-none');
              badge.textContent = '';
              badge.removeAttribute('aria-label');
            }

            // List render
            if (!Array.isArray(items) || items.length === 0) {
              list.innerHTML = '<div class="p-3 text-muted small">No meetings/events tomorrow.</div>';
              return;
            }

            list.innerHTML = items.map(i => {
              const href = esc(i.url ?? '#');
              const title = esc(i.title ?? 'Untitled');
              const when = esc(i.when ?? '');
              const group = i.group ? ' • ' + esc(i.group) : '';
              return `
          <a class="dropdown-item d-flex flex-column" href="${href}">
            <span class="fw-semibold">${title}</span>
            <small class="text-muted">${when}${group}</small>
          </a>
        `;
            }).join('');
          } catch (e) {
            // Soft error message
            list.innerHTML = '<div class="p-3 text-muted small">Unable to load notifications.</div>';
            // console.error(e);
          }
        }

        // Initial + every 5 minutes
        fetchNotifs();
        const interval = setInterval(fetchNotifs, 5 * 60 * 1000);

        // Refresh when the dropdown is opened (Bootstrap 4 event)
        if (window.jQuery && typeof jQuery.fn.on === 'function') {
          jQuery(dd).on('show.bs.dropdown', () => setTimeout(fetchNotifs, 120));
        } else {
          // Fallback if jQuery/BS event isn’t available
          dd.addEventListener('click', () => setTimeout(fetchNotifs, 150));
        }

        // Cleanup on navigation
        window.addEventListener('beforeunload', () => clearInterval(interval));
      })();
    </script>

    @php
      $appPerms = auth()->check()
        ? auth()->user()->allPermissionSlugs()
        : [];
    @endphp

    <script>
      window.APP_PERMS = @json($appPerms);

      function gateHideByPermission() {
        const have = new Set(window.APP_PERMS || []);
        document.querySelectorAll('[data-perm]').forEach(el => {
          const need = (el.getAttribute('data-perm') || '')
            .split('|')
            .map(s => s.trim())
            .filter(Boolean);
          const ok = need.some(req => have.has(req));
          if (!ok) el.classList.add('d-none');
        });
      }

      document.addEventListener('DOMContentLoaded', gateHideByPermission);
    </script>
    @yield('scripts')
    @stack('scripts')

    <!-- plugins:js -->
    <script src="{{ asset('theme/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('theme/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('theme/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('theme/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('theme/js/dataTables.select.min.js') }}"></script>

    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('theme/js/off-canvas.js') }}"></script>
    <script src="{{ asset('theme/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('theme/js/template.js') }}"></script>
    <script src="{{ asset('theme/js/settings.js') }}"></script>
    <script src="{{ asset('theme/js/todolist.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('theme/js/dashboard.js') }}"></script>
    <script src="{{ asset('theme/js/Chart.roundedBarCharts.js') }}"></script>
    <!-- End custom js for this page-->
</body>

</html>