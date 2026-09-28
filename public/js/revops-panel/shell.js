// ==========================================================================
// REVOPS PANEL - SHELL JS (topbar, sidebar, notificaciones, chat, roles, ajustes)
// Adaptado de C:\Users\Mickey\Documents\export\app.js (prototipo UI-first).
// Fase 1 (UI unicamente): el selector de roles y el panel de ajustes son una
// demostracion visual en cliente, no estan conectados al RBAC real de Laravel
// todavia (ver TASK-RBAC-02 en docs/PENDING_TASKS.md).
// ==========================================================================
(function () {
  function $(selector, root) { return (root || document).querySelector(selector); }
  function $all(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }

  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = $('[data-sidebar]');
    var overlay = $('[data-mobile-overlay]');
    var toastEl = $('[data-toast]');

    function closeMobile() {
      if (sidebar) sidebar.classList.remove('mobile-open');
      if (overlay) overlay.classList.remove('show');
    }

    function showToast(message) {
      if (!toastEl) return;
      toastEl.textContent = message;
      toastEl.classList.add('show');
      window.clearTimeout(showToast._t);
      showToast._t = window.setTimeout(function () {
        toastEl.classList.remove('show');
      }, 3200);
    }
    window.RevopsPanel = window.RevopsPanel || {};
    window.RevopsPanel.showToast = showToast;

    // 1. COLAPSAR / EXPANDIR SIDEBAR
    var collapseBtn = $('[data-action="collapse"]');
    if (collapseBtn) {
      collapseBtn.addEventListener('click', function () {
        document.body.classList.toggle('collapsed');
      });
    }

    // 2. MENU MOVIL
    var menuBtn = $('[data-action="toggle-menu"]');
    if (menuBtn && sidebar && overlay) {
      menuBtn.addEventListener('click', function () {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('show');
      });
      overlay.addEventListener('click', closeMobile);
    }

    // 3. NOTIFICACIONES (POPOVER)
    var notifPopover = $('[data-notif-popover]');
    var notifBtn = $('[data-action="toggle-notifications"]');
    var notifBadge = $('[data-notif-badge]');
    if (notifBtn && notifPopover) {
      notifBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isHidden = notifPopover.style.display === 'none' || !notifPopover.style.display;
        notifPopover.style.display = isHidden ? 'flex' : 'none';
        notifBtn.classList.toggle('active', isHidden);
      });
      document.addEventListener('click', function (e) {
        if (!notifPopover.contains(e.target) && !notifBtn.contains(e.target)) {
          notifPopover.style.display = 'none';
          notifBtn.classList.remove('active');
        }
      });
      $all('[data-notif-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
          $all('[data-notif-tab]').forEach(function (t) { t.classList.remove('active'); });
          tab.classList.add('active');
          var cat = tab.dataset.notifTab;
          $all('.notif-item').forEach(function (item) {
            item.style.display = (cat === 'all' || item.dataset.notifCategory === cat) ? 'flex' : 'none';
          });
        });
      });
      var markReadBtn = $('[data-action="mark-all-read"]');
      if (markReadBtn) {
        markReadBtn.addEventListener('click', function () {
          $all('.notif-item').forEach(function (item) {
            item.classList.remove('unread');
            item.classList.add('read');
          });
          if (notifBadge) notifBadge.style.display = 'none';
          var statusText = $('[data-notif-status-text]');
          if (statusText) statusText.textContent = 'Todas las alertas al dia';
          showToast('Todas las notificaciones marcadas como leidas');
        });
      }
    }

    // 4. CHAT INTER-AREAS (DRAWER)
    var chatContainer = $('[data-chat-container]');
    var chatBtn = $('[data-action="toggle-chat"]');
    var chatMessagesWrap = $('[data-chat-messages]');
    var chatInput = $('[data-chat-input]');
    var chatForm = $('[data-chat-form]');
    var chatState = {
      activeChannel: 'almacen',
      channels: {
        almacen: [{ sender: 'Carlos Almacen', time: 'Hace 12 min', text: 'Recepcion de Torzo B2B lista para cotejo.', isMine: false }],
        ventas: [{ sender: 'Ana Atencion', time: 'Hace 25 min', text: 'Cliente pregunta por guia de pedido #ORD-4491.', isMine: false }],
        logistica: [{ sender: 'Logistica', time: 'Hace 40 min', text: 'Manifiesto DHL de las 14:00 hrs confirmado.', isMine: false }],
        general: [{ sender: 'RevOps', time: 'Hoy', text: 'Canal general para coordinacion entre areas.', isMine: false }]
      }
    };

    function renderChatMessages(channel) {
      if (!chatMessagesWrap) return;
      var messages = chatState.channels[channel] || [];
      chatMessagesWrap.innerHTML = messages.map(function (m) {
        return '' +
          '<div class="chat-msg-row ' + (m.isMine ? 'mine' : '') + '">' +
          '<div class="chat-msg-avatar">' + m.sender.slice(0, 2).toUpperCase() + '</div>' +
          '<div class="chat-msg-body">' +
          '<div class="chat-msg-meta"><strong>' + m.sender + '</strong><span>' + m.time + '</span></div>' +
          '<div class="chat-msg-bubble">' + m.text + '</div>' +
          '</div></div>';
      }).join('');
      chatMessagesWrap.scrollTop = chatMessagesWrap.scrollHeight;
    }

    if (chatBtn && chatContainer) {
      chatBtn.addEventListener('click', function () {
        chatContainer.style.display = 'flex';
        renderChatMessages(chatState.activeChannel);
        if (chatInput) chatInput.focus();
      });
      $all('[data-action="close-chat"]').forEach(function (btn) {
        btn.addEventListener('click', function () { chatContainer.style.display = 'none'; });
      });
      $all('[data-channel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          $all('[data-channel]').forEach(function (b) { b.classList.remove('active'); });
          btn.classList.add('active');
          chatState.activeChannel = btn.dataset.channel;
          renderChatMessages(chatState.activeChannel);
        });
      });
      $all('[data-chip]').forEach(function (chip) {
        chip.addEventListener('click', function () {
          if (chatInput) {
            chatInput.value += (chatInput.value ? ' ' : '') + chip.dataset.chip + ' ';
            chatInput.focus();
          }
        });
      });
      if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
          e.preventDefault();
          var text = chatInput.value.trim();
          if (!text) return;
          chatState.channels[chatState.activeChannel].push({ sender: 'Yo', time: 'Justo ahora', text: text, isMine: true });
          chatInput.value = '';
          renderChatMessages(chatState.activeChannel);
        });
      }
    }

    // 5. SELECTOR DE ROLES (DEMOSTRACION VISUAL RBAC)
    var rolesModal = $('[data-roles-modal]');
    var rolesList = $('[data-roles-list]');
    var userRolesBtn = $('[data-action="open-user-roles"]');
    var ROLES = [
      { id: 'admin', name: 'Super Administrador', avatar: 'SA', description: 'Acceso total a finanzas, analitica y operaciones.', executive: true },
      { id: 'warehouse', name: 'Empleado de Almacen', avatar: 'EA', description: 'Vista operativa de empaque y stock. Cero cifras financieras.', executive: false },
      { id: 'sales', name: 'Atencion & Ventas', avatar: 'AV', description: 'Enfoque en leads, prospectos y consultas de clientes.', executive: false },
      { id: 'finance', name: 'RevOps & Finanzas', avatar: 'RF', description: 'Control de pasarelas, conciliacion y margenes.', executive: true }
    ];

    function renderRolesList() {
      if (!rolesList) return;
      rolesList.innerHTML = ROLES.map(function (r) {
        return '' +
          '<div class="p-3 border rounded-lg cursor-pointer transition-all flex items-center justify-between border-slate-200 hover:border-slate-400 bg-white" data-select-role="' + r.id + '">' +
          '<div class="flex items-center gap-3">' +
          '<div class="w-8 h-8 rounded-full bg-slate-900 text-white font-bold text-xs grid place-items-center">' + r.avatar + '</div>' +
          '<div><div class="text-xs font-bold text-slate-900">' + r.name + '</div>' +
          '<div class="text-[11px] text-slate-500">' + r.description + '</div></div></div></div>';
      }).join('');
      $all('[data-select-role]', rolesList).forEach(function (el) {
        el.addEventListener('click', function () {
          var role = ROLES.filter(function (r) { return r.id === el.dataset.selectRole; })[0];
          if (!role) return;
          var displayName = $('[data-user-display-name]');
          var displayRole = $('[data-user-display-role]');
          var avatarBadge = $('[data-user-avatar-badge]');
          if (displayName) displayName.textContent = role.name;
          if (displayRole) displayRole.textContent = role.executive ? 'Vista Ejecutiva (demo)' : 'Vista Operativa (demo)';
          if (avatarBadge) avatarBadge.textContent = role.avatar;
          if (rolesModal) rolesModal.style.display = 'none';
          showToast('Vista de demostracion cambiada a: ' + role.name);
        });
      });
    }
    if (userRolesBtn && rolesModal) {
      userRolesBtn.addEventListener('click', function () {
        rolesModal.style.display = 'flex';
        renderRolesList();
      });
      $all('[data-action="close-roles-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () { rolesModal.style.display = 'none'; });
      });
    }

    // 6. AJUSTES DEL SISTEMA (PANTALLA COMPLETA - CERO MODALES)
    var settingsFullscreen = $('[data-settings-fullscreen]') || $('#system-settings-fullscreen') || $('[data-settings-modal]');
    var settingsBtn = $('[data-action="open-system-settings"]');
    var settingsBody = $('[data-settings-body]');
    var settingsPanels = {
      multiverse: '<p class="text-xs text-slate-600">Activacion de vitrinas del multiverso Terra (Marte, Venus, Tiresias/Afrodita). Disponible al conectar el Hub 2 (Tienda & CMS).</p>',
      integrations: '<p class="text-xs text-slate-600">Configuracion del scraper Torzo Bot y APIs de proveedores. Disponible al conectar el Hub 4 (Inventario).</p>',
      'inventory-rules': '<p class="text-xs text-slate-600">Parametros globales de ROP, stock de seguridad y margen minimo. Disponible al conectar el Hub 4 (Inventario).</p>',
      rbac: '<p class="text-xs text-slate-600">Matriz de acceso por rol. Disponible al implementar TASK-RBAC-02.</p>'
    };
    if (settingsBtn && settingsFullscreen && settingsBody) {
      settingsBtn.addEventListener('click', function () {
        settingsFullscreen.style.display = 'flex';
        settingsBody.innerHTML = settingsPanels.multiverse;
      });
      $all('[data-action="close-settings-modal"], [data-action="close-settings-fullscreen"]').forEach(function (btn) {
        btn.addEventListener('click', function () { settingsFullscreen.style.display = 'none'; });
      });
      $all('[data-settings-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
          $all('[data-settings-tab]').forEach(function (t) { t.classList.remove('active'); });
          tab.classList.add('active');
          settingsBody.innerHTML = settingsPanels[tab.dataset.settingsTab] || settingsPanels.multiverse;
        });
      });
      var saveBtn = $('[data-action="save-settings"]');
      if (saveBtn) {
        saveBtn.addEventListener('click', function () {
          settingsFullscreen.style.display = 'none';
          showToast('Ajustes del sistema guardados (demo)');
        });
      }
    }

    // 7. CONMUTADOR DE ENTORNO (INFORMATIVO EN FASE 1)
    var envBtn = $('[data-action="open-env-modal"]');
    var envModal = $('[data-env-modal]');
    if (envBtn && envModal) {
      envBtn.addEventListener('click', function () { envModal.style.display = 'flex'; });
      $all('[data-action="close-env-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () { envModal.style.display = 'none'; });
      });
    }

    // 8. TOOLTIPS DE SECCION (REGLA 8: BOTON '?')
    var tooltip = $('[data-section-tooltip]');
    if (tooltip) {
      var tooltipTitle = $('[data-tooltip-title]', tooltip);
      var tooltipBody = $('[data-tooltip-body]', tooltip);
      document.addEventListener('click', function (e) {
        var btn = e.target.closest('.section-help-btn');
        if (!btn) {
          tooltip.classList.remove('show');
          return;
        }
        e.stopPropagation();
        if (tooltipTitle) tooltipTitle.textContent = btn.dataset.helpTitle || 'Ayuda';
        if (tooltipBody) tooltipBody.textContent = btn.dataset.helpDesc || '';
        var rect = btn.getBoundingClientRect();
        tooltip.style.left = Math.max(12, rect.left - 140) + 'px';
        tooltip.style.top = (rect.bottom + 8) + 'px';
        tooltip.classList.add('show', 'arrow-top');
      });
    }
  });
})();
