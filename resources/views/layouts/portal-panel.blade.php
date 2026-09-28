<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Panel Administrador') - Mi Tienda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/revops-panel.css') }}?v=20260910">
  {{-- Tailwind CDN con preflight desactivado para coexistir con revops-panel.css (paridad con el prototipo UI-first) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { corePlugins: { preflight: false } };</script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="collapsed">
  @php($authUser = auth()->user())
  @php($initials = $authUser ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authUser->name, 0, 2)) : 'MR')
  <header class="topbar">
    <div class="topbar-left">
      <div class="brand"><span class="brand-mark">M</span><span><strong>MI TIENDA</strong><small>PORTAL ADMINISTRADOR</small></span></div>
      <div class="topbar-divider"></div>
      <div class="topbar-hub-info">
        <span class="topbar-hub-kicker">@yield('hub_kicker','Hub')</span>
        <h1 class="topbar-hub-title">@yield('hub_title', 'Panel Administrador')</h1>
        <span class="topbar-hub-badge operative">@yield('hub_status','Activo')</span>
      </div>
    </div>
    <div class="top-actions">
      <button type="button" class="env-indicator-btn sandbox" data-action="open-env-modal" title="Entorno del sistema: SANDBOX (simulado)">
        <span class="env-pulse-dot amber"></span>
        <span class="font-bold">SANDBOX</span>
        <span class="text-[10px] opacity-75 font-normal hidden sm:inline">&bull; Simulado</span>
      </button>

      <div class="topbar-notif-wrapper">
        <button class="topbar-icon-btn" data-action="toggle-notifications" aria-label="Notificaciones" title="Notificaciones operativas">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span class="topbar-badge" data-notif-badge style="display:none;">0</span>
        </button>
        <div class="notif-popover" data-notif-popover style="display:none;">
          <div class="notif-header">
            <div>
              <h3 class="notif-title">Centro de Notificaciones</h3>
              <span class="notif-subtitle" data-notif-status-text>Cargando alertas...</span>
            </div>
            <button class="notif-mark-read" data-action="mark-all-read">Marcar leidas</button>
          </div>
          <div class="notif-tabs">
            <button class="notif-tab active" data-notif-tab="all">Todas (<span data-notif-count-all>0</span>)</button>
            <button class="notif-tab" data-notif-tab="critical">Criticas (<span data-notif-count-critical>0</span>)</button>
            <button class="notif-tab" data-notif-tab="info">Operativas (<span data-notif-count-info>0</span>)</button>
          </div>
          <div class="notif-list" data-notif-list>
            <div class="p-4 text-center text-xs text-slate-500" data-notif-loading>
              Cargando notificaciones...
            </div>
          </div>
        </div>
      </div>

      <button class="topbar-icon-btn" data-action="toggle-chat" aria-label="Chat entre areas" title="Chat inter-areas">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        <span class="topbar-badge chat-badge" data-chat-badge style="display:none;">0</span>
      </button>

      <!-- AJUSTES Y CONFIGURACIÓN DEL MÓDULO -->
      <button class="topbar-icon-btn" data-action="toggle-module-settings" aria-label="Ajustes del Módulo" title="Ajustes y Configuración del Módulo" onclick="window.dispatchEvent(new CustomEvent('open-module-settings'))">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
      </button>

      <a href="{{ route('store.home') }}" class="button button-light flex items-center gap-1.5" style="text-decoration:none;" target="_blank" rel="noopener" title="Ver Tienda">
        <span>Ver tienda</span>
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>
      <form method="POST" action="{{ route('auth.modal.logout') }}">
        @csrf
        <button class="button button-quiet flex items-center gap-1.5" type="submit">
          <span>Salir</span>
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
    </div>
    <button class="menu-button" data-action="toggle-menu" aria-label="Abrir menu"><span></span><span></span><span></span></button>
  </header>

  <div class="layout">
    <aside class="sidebar" data-sidebar>
      <div class="sidebar-nav-container">
        <div class="sidebar-heading"><span>Hubs Operativos</span><button class="collapse-button" data-action="collapse" aria-label="Contraer modulos">&lsaquo;</button></div>
        <nav class="module-nav" aria-label="Hubs operativos">
          @php($activeRole = session('active_role') ?: ($authUser ? ($authUser->active_role ?? $authUser->role) : 'guest'))
          @if($activeRole === 'vendor')
            <a class="module-link {{ request()->routeIs('portal.vendor.*') ? 'active' : '' }}" href="{{ route('portal.vendor.extranet') }}" data-screen="vendor-extranet" title="Hub 7: Extranet de Proveedores & Fabricantes">
              <svg viewBox="0 0 24 24"><path d="m12 3 8 4v10l-8 4-8-4V7l8-4ZM4 7l8 4 8-4M12 11v10"/></svg>
              <span>7. Extranet Proveedor</span>
            </a>
          @elseif($activeRole === 'ally')
            <a class="module-link {{ request()->routeIs('portal.allies') ? 'active' : '' }}" href="{{ route('portal.allies') }}" data-screen="allies" title="Hub 7: Extranet de Aliados">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="12" y1="1" x2="12" y2="7"/></svg>
              <span>7. Extranet Aliado</span>
            </a>
          @else
            <a class="module-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" href="{{ route('portal.dashboard') }}" data-screen="dashboard" title="Hub 1: Dashboard General">
              <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
              <span>1. Dashboard General</span>
            </a>
            <a class="module-link {{ request()->routeIs('portal.store') ? 'active' : '' }}" href="{{ route('portal.store') }}" data-screen="store" title="Hub 2: Tienda &amp; CMS Multiverso">
              <svg viewBox="0 0 24 24"><path d="m4 8 8-4 8 4-8 4-8-4Zm0 0v8l8 4 8-4V8M12 12v8"/><circle cx="12" cy="12" r="2"/></svg>
              <span>2. Tienda &amp; CMS</span>
            </a>
            <a class="module-link {{ request()->routeIs('portal.marketing') || request()->routeIs('leads.*') || request()->routeIs('portal.scoring') ? 'active' : '' }}" href="{{ route('portal.marketing') }}" data-screen="crm" title="Hub 3: Marketing &amp; CRM 360°">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span>3. Marketing &amp; CRM</span>
            </a>
            <a class="module-link {{ request()->routeIs('portal.inventory') ? 'active' : '' }}" href="{{ route('portal.inventory') }}" data-screen="inventory" title="Hub 4: Inventario &amp; Cadena de Suministro">
              <svg viewBox="0 0 24 24"><path d="m12 3 8 4v10l-8 4-8-4V7l8-4ZM4 7l8 4 8-4M12 11v10"/></svg>
              <span>4. Inventario &amp; Compras</span>
            </a>
            @canViewFinancials
            <a class="module-link {{ request()->routeIs('portal.finance') ? 'active' : '' }}" href="{{ route('portal.finance') }}" data-screen="finance" title="Hub 5: Finanzas &amp; Conciliacion">
              <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
              <span>5. Finanzas &amp; Conciliacion</span>
            </a>
            @endcanViewFinancials
            <a class="module-link {{ request()->routeIs('portal.logistics') ? 'active' : '' }}" href="{{ route('portal.logistics') }}" data-screen="logistics" title="Hub 6: Logística &amp; Post-Venta">
              <svg viewBox="0 0 24 24"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14v10Z"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
              <span>6. Logística &amp; Envíos</span>
            </a>
            <a class="module-link {{ request()->routeIs('portal.allies') || request()->routeIs('portal.vendor.*') ? 'active' : '' }}" href="{{ route('portal.allies') }}" data-screen="allies" title="Hub 7: Convenios, Aliados &amp; Extranets B2B">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="12" y1="1" x2="12" y2="7"/></svg>
              <span>7. Convenios &amp; Extranets</span>
            </a>
            <a class="module-link {{ request()->routeIs('portal.governance') ? 'active' : '' }}" href="{{ route('portal.governance') }}" data-screen="governance" title="Hub 8: Gobernanza, Auditoria &amp; RBAC">
              <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span>8. Gobernanza &amp; RBAC</span>
            </a>
          @endif
        </nav>
      </div>

      <div class="sidebar-footer">
        <button class="sidebar-user-btn" type="button" data-action="open-user-roles" title="Cambiar vista de demostracion RBAC">
          <div class="sidebar-user-avatar" data-user-avatar-badge>{{ $initials }}</div>
          <div class="sidebar-user-info">
            <span class="sidebar-user-name" data-user-display-name>{{ $authUser->name ?? 'Usuario Portal' }}</span>
            <span class="sidebar-user-role-badge" data-user-display-role>Super Administrador</span>
          </div>
          <svg class="user-chevron" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--muted);margin-left:auto;"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <button class="sidebar-settings-btn" type="button" data-action="open-system-settings" title="Ajustes del Sistema">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path></svg>
          <span>Ajustes del Sistema</span>
        </button>
      </div>
    </aside>

    <main class="main" data-main @if(request()->routeIs('portal.store')) data-screen="store" @endif>
      @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-error bg-rose-50 text-rose-800 border border-rose-200 p-3 rounded-lg mb-4 text-xs font-semibold" role="alert">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
      @endif
      @yield('content')
    </main>
  </div>

  {{-- CHAT INTER-AREAS (TASK-COM-01) --}}
  <div class="chat-drawer-container" data-chat-container style="display:none;">
    <div class="chat-backdrop" data-action="close-chat"></div>
    <aside class="chat-drawer">
      <div class="chat-header">
        <div class="chat-header-title">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <div><h3>Comunicacion Inter-Areas</h3><span class="chat-status-sub"><span class="status-indicator-online"></span> En linea - Trazabilidad formal</span></div>
        </div>
        <button class="chat-close-btn" data-action="close-chat" aria-label="Cerrar chat"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="chat-channels-bar">
        <button class="chat-channel-btn active" data-channel="almacen"># almacen-compras <span class="text-[10px] ml-1 px-1 rounded bg-slate-200" data-channel-count="almacen" style="display:none;">0</span></button>
        <button class="chat-channel-btn" data-channel="ventas"># atencion-ventas <span class="text-[10px] ml-1 px-1 rounded bg-slate-200" data-channel-count="ventas" style="display:none;">0</span></button>
        <button class="chat-channel-btn" data-channel="logistica"># logistica-guias <span class="text-[10px] ml-1 px-1 rounded bg-slate-200" data-channel-count="logistica" style="display:none;">0</span></button>
        <button class="chat-channel-btn" data-channel="general"># revops-general <span class="text-[10px] ml-1 px-1 rounded bg-slate-200" data-channel-count="general" style="display:none;">0</span></button>
      </div>
      <div class="chat-messages-wrap" data-chat-messages></div>
      <div class="chat-context-chips flex items-center justify-between gap-1 px-3 py-1.5 bg-slate-50 border-t border-slate-200 text-xs">
        <div class="flex items-center gap-1 overflow-x-auto">
          <span class="chip-label text-slate-500 font-semibold text-[11px]">Etiquetar:</span>
          <button type="button" class="chat-chip text-[11px] px-1.5 py-0.5 rounded bg-white border border-slate-300 hover:bg-slate-100" data-chip="@SKU:">+ SKU</button>
          <button type="button" class="chat-chip text-[11px] px-1.5 py-0.5 rounded bg-white border border-slate-300 hover:bg-slate-100" data-chip="@Pedido:">+ Pedido</button>
          <button type="button" class="chat-chip text-[11px] px-1.5 py-0.5 rounded bg-white border border-slate-300 hover:bg-slate-100" data-chip="@OC:">+ OC</button>
          <button type="button" class="chat-chip text-[11px] px-1.5 py-0.5 rounded bg-white border border-slate-300 hover:bg-slate-100" data-chip="@Guia:">+ Guia</button>
        </div>
        <select class="chat-priority-select text-[11px] px-1.5 py-0.5 rounded border border-slate-300 bg-white" data-chat-priority title="Prioridad operativa">
          <option value="normal">Normal</option>
          <option value="high">Alta</option>
          <option value="urgent">Urgente</option>
        </select>
      </div>
      <form class="chat-input-bar" data-chat-form>
        <input type="text" class="chat-input" placeholder="Escribe una nota formal..." data-chat-input required autocomplete="off">
        <button type="submit" class="chat-send-btn" aria-label="Enviar mensaje"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
      </form>
    </aside>
  </div>

  {{-- MODAL RBAC DE DEMOSTRACION --}}
  <div class="modal-backdrop" data-roles-modal style="display:none;">
    <div class="modal-dialog max-w-md">
      <div class="modal-header">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Vista de demostracion RBAC</h3>
        <button class="close-icon-btn" data-action="close-roles-modal"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="p-4">
        <p class="text-xs text-slate-600 mb-3">Alterna entre perfiles para previsualizar la etiqueta de rol en el panel. Esta demostracion aun no restringe datos reales (ver TASK-RBAC-02).</p>
        <div class="space-y-2" data-roles-list></div>
      </div>
    </div>
  </div>

  {{-- AJUSTES DEL SISTEMA (PANTALLA COMPLETA - CERO MODALES) --}}
  <div id="system-settings-fullscreen" data-settings-fullscreen class="fixed inset-0 z-[95] bg-[#f8fafc] flex flex-col w-screen h-screen overflow-hidden" style="display:none;" role="region" aria-label="Ajustes del Sistema">
    <!-- Topbar Workstation Header -->
    <header class="px-6 py-3.5 border-b border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0 shadow-xs">
      <div class="flex items-center gap-3">
        <button type="button" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 border border-slate-200 shadow-2xs" data-action="close-settings-fullscreen" title="Volver al Panel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          <span>Volver al Panel</span>
        </button>
        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
        <div class="flex items-center gap-2.5">
          <span class="p-2 bg-slate-900 text-white rounded-xl shadow-2xs">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </span>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base font-bold text-slate-900 m-0">Ajustes del Sistema &amp; Configuración Global</h2>
              <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-800 text-[10px] font-bold uppercase tracking-wider border border-slate-300">Pantalla Completa</span>
            </div>
            <p class="text-xs text-slate-500 m-0">Parámetros globales: activación de vitrinas del multiverso Terra, conexión del scraper Torzo Bot, reglas de stock y matriz RBAC</p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs" data-action="save-settings">
          Guardar Ajustes
        </button>
      </div>
    </header>

    <!-- Full-Width Horizontal Tabs Bar -->
    <div class="px-6 border-b border-slate-200 bg-white flex gap-2 text-xs font-bold overflow-x-auto no-scrollbar shrink-0 shadow-2xs">
      <button class="settings-tab active border-indigo-600 text-indigo-600 border-b-2 py-3 px-3 cursor-pointer whitespace-nowrap" data-settings-tab="multiverse">Multiverso Terra</button>
      <button class="settings-tab text-slate-500 hover:text-slate-800 py-3 px-3 border-b-2 border-transparent cursor-pointer whitespace-nowrap" data-settings-tab="integrations">APIs &amp; Scraper</button>
      <button class="settings-tab text-slate-500 hover:text-slate-800 py-3 px-3 border-b-2 border-transparent cursor-pointer whitespace-nowrap" data-settings-tab="inventory-rules">Reglas de Stock</button>
      <button class="settings-tab text-slate-500 hover:text-slate-800 py-3 px-3 border-b-2 border-transparent cursor-pointer whitespace-nowrap" data-settings-tab="rbac">Permisos RBAC</button>
    </div>

    <!-- Full-Screen Scrollable Content Area -->
    <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8fafc]">
      <div class="max-w-6xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-xs p-6" data-settings-body>
        <!-- Populated via JS -->
      </div>
    </div>

    <!-- Full-Screen Workstation Footer -->
    <footer class="px-6 py-3 bg-white border-t border-slate-200 flex justify-between items-center shrink-0">
      <button type="button" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition cursor-pointer flex items-center gap-1.5" data-action="close-settings-fullscreen">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Volver al Panel</span>
      </button>
      <button type="button" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs" data-action="save-settings">
        Guardar Ajustes
      </button>
    </footer>
  </div>

  {{-- AJUSTES DE HUB UNIVERSAL (PANTALLA COMPLETA - APLICA A TODOS LOS HUBS - CERO MODALES) --}}
  <div id="hub-settings-fullscreen" data-hub-settings-fullscreen class="fixed inset-0 z-[85] bg-[#f8fafc] flex flex-col w-screen h-screen overflow-hidden" style="display:none;" role="region" aria-label="Ajustes del Hub">
    <!-- Topbar Workstation Header -->
    <header class="px-6 py-3.5 border-b border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0 shadow-xs">
      <div class="flex items-center gap-3">
        <button type="button" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 border border-slate-200 shadow-2xs" data-action="close-hub-settings-fullscreen" title="Volver al Hub">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          <span>Volver al Hub</span>
        </button>
        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
        <div class="flex items-center gap-2.5">
          <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-200 shadow-2xs">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          </span>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base font-bold text-slate-900 m-0" data-hub-settings-title>Ajustes &amp; Configuración del Hub</h2>
              <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-wider border border-indigo-200">Pantalla Completa</span>
            </div>
            <p class="text-xs text-slate-500 m-0" data-hub-settings-subtitle>Gobernanza operativa, parámetros de visualización, umbrales y reglas del módulo</p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs" data-action="save-hub-settings">
          Guardar Ajustes
        </button>
      </div>
    </header>

    <!-- Full-Width Horizontal Tabs Bar -->
    <div class="px-6 border-b border-slate-200 bg-white flex gap-2 text-xs font-bold overflow-x-auto no-scrollbar shrink-0 shadow-2xs" data-hub-settings-tabs>
      <!-- Populated via JS based on current hub -->
    </div>

    <!-- Full-Screen Scrollable Content Area -->
    <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8fafc]">
      <div class="max-w-6xl mx-auto space-y-6" data-hub-settings-body>
        <!-- Populated via JS -->
      </div>
    </div>

    <!-- Full-Screen Workstation Footer -->
    <footer class="px-6 py-3 bg-white border-t border-slate-200 flex justify-between items-center shrink-0">
      <button type="button" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition cursor-pointer flex items-center gap-1.5" data-action="close-hub-settings-fullscreen">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Volver al Hub</span>
      </button>
      <button type="button" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs" data-action="save-hub-settings">
        Guardar Ajustes
      </button>
    </footer>
  </div>

  {{-- MODAL INFORMATIVO DEL CONMUTADOR DE ENTORNO --}}
  <div class="modal-backdrop" data-env-modal style="display:none;">
    <div class="modal-dialog max-w-md">
      <div class="modal-header">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Entorno del Sistema</h3>
        <button class="close-icon-btn" data-action="close-env-modal"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="p-4">
        <p class="text-xs text-slate-600">Este panel opera actualmente en modo <strong>SANDBOX</strong>. La conmutacion real hacia Produccion (llaves de Stripe/Skydropx) se habilita en la Fase 4 de despliegue (ver docs/admin-panel-migration.md).</p>
      </div>
    </div>
  </div>

  <!-- DRAWER DESPLEGABLE DE CHAT ESTRATÉGICO CON COPILOTO IA (TASK-DSH-07) -->
  <div class="copilot-chat-container" data-copilot-chat-container style="display: none;">
    <div class="copilot-chat-backdrop" data-action="close-copilot-chat"></div>
    <aside class="copilot-chat-drawer shadow-2xl">
      <div class="copilot-chat-header">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 m-0 flex items-center gap-1.5">
              <span>Copiloto Estratégico IA</span>
              <span class="copilot-badge text-[10px]">RBAC</span>
            </h3>
            <span class="text-[11px] text-slate-500 font-medium" data-copilot-chat-role-label>Contexto: Super Admin</span>
          </div>
          <button class="section-help-btn" type="button" aria-label="Ayuda del Copiloto IA" data-help-title="Copiloto Estratégico IA" data-help-desc="Asistente contextual inteligente adaptado al rol activo. Procesa consultas en lenguaje natural y recomienda acciones directas con aislamiento de datos financieros para operarios.">?</button>
        </div>
        <button class="chat-close-btn" data-action="close-copilot-chat" aria-label="Cerrar chat con Copiloto IA">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="copilot-chat-body" data-copilot-chat-messages></div>
      <div class="copilot-chat-chips" data-copilot-prompt-chips></div>

      <form class="copilot-chat-input-wrap" data-copilot-chat-form>
        <input type="text" class="copilot-chat-input" placeholder="Pregunta algo al Copiloto IA..." data-copilot-chat-input required autocomplete="off">
        <button type="submit" class="button button-quiet bg-slate-900 text-white text-xs px-3 py-1.5 rounded-md flex items-center gap-1">
          <span>Enviar</span>
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
        </button>
      </form>
    </aside>
  </div>

  <!-- MODAL DINAMICO DE ACCIONES OPERATIVAS -->
  <div class="modal-backdrop" data-action-modal style="display: none;"></div>

  <div class="mobile-overlay" data-mobile-overlay></div>
  <div class="section-tooltip-popover" data-section-tooltip role="tooltip">
    <div class="tooltip-header" data-tooltip-title></div>
    <div class="tooltip-body" data-tooltip-body></div>
  </div>
  <div class="toast" data-toast role="status"></div>
  <div id="global-toast-container" class="toast-container fixed bottom-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full" aria-live="polite"></div>

  <script src="{{ asset('js/revops-panel/app.js') }}?v=20260928_v3"></script>
  @stack('scripts')
</body>
</html>
