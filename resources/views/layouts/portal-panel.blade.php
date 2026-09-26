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
<body>
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
          <span class="topbar-badge" data-notif-badge>3</span>
        </button>
        <div class="notif-popover" data-notif-popover style="display:none;">
          <div class="notif-header">
            <div>
              <h3 class="notif-title">Centro de Notificaciones</h3>
              <span class="notif-subtitle" data-notif-status-text>3 alertas operativas pendientes</span>
            </div>
            <button class="notif-mark-read" data-action="mark-all-read">Marcar leidas</button>
          </div>
          <div class="notif-tabs">
            <button class="notif-tab active" data-notif-tab="all">Todas (3)</button>
            <button class="notif-tab" data-notif-tab="critical">Criticas (2)</button>
            <button class="notif-tab" data-notif-tab="info">Operativas (1)</button>
          </div>
          {{-- MOCK Fase 3: sustituir por notificaciones reales del backend --}}
          <div class="notif-list">
            <div class="notif-item unread" data-notif-category="critical">
              <div class="notif-icon-col"><span class="notif-alert-dot red"></span></div>
              <div class="notif-content-col">
                <div class="notif-item-title">Alerta ROP critica de inventario</div>
                <p class="notif-item-desc">Hay referencias con stock disponible por debajo del punto de reorden.</p>
                <div class="notif-item-footer">
                  <span class="notif-time">Hace 8 min</span>
                  <a class="button button-light notif-action-btn" href="{{ route('portal.inventory') }}">Ver Inventario</a>
                </div>
              </div>
            </div>
            <div class="notif-item unread" data-notif-category="critical">
              <div class="notif-icon-col"><span class="notif-alert-dot emerald"></span></div>
              <div class="notif-content-col">
                <div class="notif-item-title">Pedido listo para empaque</div>
                <p class="notif-item-desc">Un pedido con mercancia local y de proveedor quedo desbloqueado.</p>
                <div class="notif-item-footer">
                  <span class="notif-time">Hace 22 min</span>
                  <a class="button button-light notif-action-btn" href="{{ route('portal.dashboard') }}">Ver Dashboard</a>
                </div>
              </div>
            </div>
            <div class="notif-item unread" data-notif-category="info">
              <div class="notif-icon-col"><span class="notif-alert-dot amber"></span></div>
              <div class="notif-content-col">
                <div class="notif-item-title">Sincronizacion de proveedor con discrepancias</div>
                <p class="notif-item-desc">Un proveedor reporto cambios de costo o disponibilidad recientes.</p>
                <div class="notif-item-footer">
                  <span class="notif-time">Hace 35 min</span>
                  <a class="button button-light notif-action-btn" href="{{ route('portal.suppliers.colors') }}">Ver Proveedores</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button class="topbar-icon-btn" data-action="toggle-chat" aria-label="Chat entre areas" title="Chat inter-areas">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        <span class="topbar-badge chat-badge">2</span>
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
          <a class="module-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" href="{{ route('portal.dashboard') }}" title="Hub 1: Dashboard General">
            <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            <span>1. Dashboard General</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.store') ? 'active' : '' }}" href="{{ route('portal.store') }}" title="Hub 2: Tienda &amp; CMS Multiverso">
            <svg viewBox="0 0 24 24"><path d="m4 8 8-4 8 4-8 4-8-4Zm0 0v8l8 4 8-4V8M12 12v8"/><circle cx="12" cy="12" r="2"/></svg>
            <span>2. Tienda &amp; CMS</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.marketing') || request()->routeIs('leads.*') || request()->routeIs('portal.scoring') ? 'active' : '' }}" href="{{ route('portal.marketing') }}" title="Hub 3: Marketing &amp; CRM 360°">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>3. Marketing &amp; CRM</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.inventory') ? 'active' : '' }}" href="{{ route('portal.inventory') }}" title="Hub 4: Inventario &amp; Cadena de Suministro">
            <svg viewBox="0 0 24 24"><path d="m12 3 8 4v10l-8 4-8-4V7l8-4ZM4 7l8 4 8-4M12 11v10"/></svg>
            <span>4. Inventario &amp; Compras</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.finance') ? 'active' : '' }}" href="{{ route('portal.finance') }}" title="Hub 5: Finanzas &amp; Conciliacion">
            <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
            <span>5. Finanzas &amp; Conciliacion</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.logistics') ? 'active' : '' }}" href="{{ route('portal.logistics') }}" title="Hub 6: Logística &amp; Post-Venta">
            <svg viewBox="0 0 24 24"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14v10Z"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
            <span>6. Logística &amp; Envíos</span>
          </a>
          <a class="module-link {{ request()->routeIs('portal.allies') ? 'active' : '' }}" href="{{ route('portal.allies') }}" title="Hub 7: Convenios &amp; Aliados">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="12" y1="1" x2="12" y2="7"/></svg>
            <span>7. Convenios &amp; Aliados</span>
          </a>
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

    <main class="main">
      @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
      @endif
      @yield('content')
    </main>
  </div>

  {{-- CHAT INTER-AREAS --}}
  <div class="chat-drawer-container" data-chat-container style="display:none;">
    <div class="chat-backdrop" data-action="close-chat"></div>
    <aside class="chat-drawer">
      <div class="chat-header">
        <div class="chat-header-title">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <div><h3>Comunicacion Inter-Areas</h3><span class="chat-status-sub"><span class="status-indicator-online"></span> Demostracion local</span></div>
        </div>
        <button class="chat-close-btn" data-action="close-chat" aria-label="Cerrar chat"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="chat-channels-bar">
        <button class="chat-channel-btn active" data-channel="almacen"># almacen-compras</button>
        <button class="chat-channel-btn" data-channel="ventas"># atencion-ventas</button>
        <button class="chat-channel-btn" data-channel="logistica"># logistica-guias</button>
        <button class="chat-channel-btn" data-channel="general"># revops-general</button>
      </div>
      <div class="chat-messages-wrap" data-chat-messages></div>
      <div class="chat-context-chips">
        <span class="chip-label">Etiquetar:</span>
        <button type="button" class="chat-chip" data-chip="@SKU:">+ SKU</button>
        <button type="button" class="chat-chip" data-chip="@Pedido:">+ Pedido</button>
      </div>
      <form class="chat-input-bar" data-chat-form>
        <input type="text" class="chat-input" placeholder="Escribe un mensaje..." data-chat-input required autocomplete="off">
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

  {{-- MODAL DE AJUSTES DEL SISTEMA --}}
  <div class="modal-backdrop" data-settings-modal style="display:none;">
    <div class="modal-dialog max-w-2xl">
      <div class="modal-header">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Ajustes del Sistema</h3>
        <button class="close-icon-btn" data-action="close-settings-modal"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="settings-tabs-bar">
        <button class="settings-tab active" data-settings-tab="multiverse">Multiverso Terra</button>
        <button class="settings-tab" data-settings-tab="integrations">APIs &amp; Scraper</button>
        <button class="settings-tab" data-settings-tab="inventory-rules">Reglas de Stock</button>
        <button class="settings-tab" data-settings-tab="rbac">Permisos RBAC</button>
      </div>
      <div class="p-5" data-settings-body></div>
      <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
        <button class="button button-light" data-action="close-settings-modal">Cancelar</button>
        <button class="button button-quiet" data-action="save-settings">Guardar Ajustes</button>
      </div>
    </div>
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

  <script src="{{ asset('js/revops-panel/app.js') }}?v=20260926"></script>
  @stack('scripts')
</body>
</html>
