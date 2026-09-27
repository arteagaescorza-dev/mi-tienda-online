# Plan Maestro de Implementacion: Prioridad 5 - Shell Operativo, Gobernanza Global y Restriccion Estricta RBAC
## Plataforma RevOps E-Commerce Multiverso & Suscripciones (Mi Tienda Online)

> **Contexto de Arquitectura:**  
> Este documento rige la implementacion fullstack de la **Prioridad 5: Shell Operativo, Gobernanza Global y Restriccion Estricta RBAC** en la rama `feature/prioridad-5-gobernanza-rbac`, garantizando la seguridad perimetral, la privacidad financiera irrestricta por rol, la auditoria inmutable de eventos de administracion y la comunicacion sincronizada entre areas operativas.  
> **Referencias Normativas:** [MASTER_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/MASTER_ROADMAP.md), [PENDING_TASKS.md](file:///c:/laragon/www/mi-tienda-online/docs/PENDING_TASKS.md), [BACKEND_INTEGRATION_SPEC.md](file:///c:/laragon/www/mi-tienda-online/docs/BACKEND_INTEGRATION_SPEC.md) y [ROLES_AND_WORKFLOWS_SPEC.md](file:///c:/laragon/www/mi-tienda-online/docs/ROLES_AND_WORKFLOWS_SPEC.md).  
> **Regla de Oro:** Se prohibe terminantemente el uso de emoticones o emojis en codigo, vistas, comentarios, mensajes de commit y documentacion (Regla 1). La carpeta `import/` permanece estrictamente de solo lectura.

---

## 1. Vision Estrategica y Diagnostico de Brecha

El crecimiento escalable de la plataforma exige que cada operador acceda unicamente a la informacion indispensable para su labor, blindando la confidencialidad financiera de la empresa y asegurando la trazabilidad de cualquier accion de alto impacto. La **Prioridad 5** resuelve los siguientes pilares de gobernanza:

1. **Aislamiento de Privacidad Financiera (`TASK-RBAC-03`):** Los colaboradores de bodega (`warehouse`), compras (`purchases`), logistica (`logistics`) y atencion a clientes (`sales`) deben tener **cero visibilidad** sobre margenes netos, costos unitarios de adquisicion, ingresos globales de facturacion y retenciones fiscales del SAT. Dicha informacion se reserva exclusivamente para los roles `admin`, `superadmin` y `finance`.
2. **Segregacion de Tareas Operativas (`TASK-RBAC-04`):** El feed y bandeja de tareas del sistema (`OperationalTask`) deben presentarse prefiltrados segun el rol activo, evitando que el personal de rampa se distraiga con alertas fiscales o que finanzas reciba avisos de empaque.
3. **Master Log de Auditoria Omnicanal (`TASK-DSH-09`):** Toda accion critica (modificacion de precios, forzado manual de estatus de ordenes, ajustes de existencias en Kardex, levantamiento de bloqueos SPEI o creacion de roles) debe quedar registrada de forma inmutable en `system_audit_logs` con IP, usuario y diff de atributos.
4. **Notificaciones y Toasts Globales con Deep Links (`TASK-UX-03`, `TASK-DSH-04`):** Sistema de notificaciones en tiempo real y toasts de alta prioridad con saltos directos en un clic a los folios correspondientes (pedidos, cajas ROP, garantias RMA, conciliaciones).
5. **Mensajeria Interdepartamental Operativa (`interdepartmental_messages`):** Canal formal y auditable de comunicacion operativa entre almacén, compras, atencion y logistica, vinculado a referencias concretas (`order_number`, `po_code`, `rma_number`, `claim_number`).
6. **Centro de Gobernanza y Ajustes Globales (`TASK-SYS-01`, `TASK-SYS-ENV`):** Pantalla completa de configuracion y gobernanza dentro de `<main>` (Regla 15 de `MASTER_ROADMAP.md`), incluyendo conmutador visual de entornos (Sandbox / Staging vs Produccion).

### Matriz Comparativa: Estado Actual vs Estado Objetivo Prioridad 5

| Componente de Gobernanza | Estado Actual | Regla de Negocio / Especificacion | Resolucion en Prioridad 5 |
| :--- | :--- | :--- | :--- |
| **Privacidad Financiera (`TASK-RBAC-03`)** | Algunos endpoints exponen campos de costos y margenes a cualquier usuario autenticado en el portal. | Personal de almacen, empaque y atencion jamas debe conocer margenes netos o ingresos brutos. | Middleware `EnforceFinancialPrivacy` y helpers Blade que ofuscan o excluyen llaves sensibles en JSON y vistas. |
| **Bandeja de Tareas por Rol (`TASK-RBAC-04`)** | La bandeja de tareas muestra registros globales sin segregacion estricta en todas las vistas. | Cada colaborador ve unicamente las tareas asignadas a su departamento funcional. | Filtrado por scopes de Eloquent segun `assigned_role` y `department` del usuario autenticado. |
| **Auditoria Inmutable (`TASK-DSH-09`)** | Existen movimientos de Kardex pero no un registro maestro de cambios en usuarios, precios y estatus. | Todo evento de superadministracion debe registrarse con snapshot anterior y posterior. | Modelo `SystemAuditLog` y servicio `AuditTrailService` con listeners sobre eventos del dominio. |
| **Toasts y Feed de Tareas (`TASK-UX-03`)** | Los mensajes de sesion usan alerts sencillos que desaparecen al recargar. | Toasts flotantes con z-index superior, stack acumulativo y campanita en navbar con deep links en 1 clic. | Componente global Blade de toasts reactivos y popover de tareas con enlaces directos a folios. |
| **Mensajeria Interdepartamental** | La comunicacion de incidencias se realiza de manera verbal o dispersa. | Canal estructurado con hilos contextuales por folio operativo (`#almacen-compras`, `#atencion-ventas`). | Tablero de mensajes operativos con persistencia en `interdepartmental_messages`. |
| **Centro de Gobernanza (`TASK-SYS-01`)** | Ajustes del sistema dispersos en modales locales de inventario y finanzas. | Centro integral de gobernanza a pantalla completa dentro de `<main>` (Regla 15). | Vista `portal.governance` con matriz de roles, monitor de salud y conmutador visual de entorno Sandbox. |

---

## 2. Estandares Transversales de Diseno y Reglas Maestras Aplicadas

1. **Regla 1 (Cero Emojis):** Exclusion absoluta de emojis en codigo, vistas, comentarios, base de datos y commits.
2. **Regla 5 (Cero Cabeceras Redundantes):** Los titulos y breadcrumbs residen en el Navbar superior; el contenido inicia directamente con el Subnav de gobernanza.
3. **Regla 8 (Tooltips de Seccion con '?'):** Todo panel de permisos, log de auditoria y canal de mensajeria cuenta con boton `.section-help-btn` de orientacion contextual.
4. **Regla 10 (Jerarquia Estructural Inamovible):**
   * *1er Nivel:* Subnav de modulos de gobernanza (Roles & Accesos, Auditoria Master, Notificaciones & Tareas, Mensajeria, Ajustes Globales).
   * *2do Nivel:* Tarjetas KPI de estado perimetral (Usuarios Activos, Tareas en Cola, Eventos Auditados Hoy, Estado del Entorno).
   * *3er Nivel:* Tablas de auditoria, consolas interactivas de permisos y modales de accion.
5. **Regla 15 (Ajustes de Sistema en Pantalla Completa):** La seccion de gobernanza y ajustes se despliega como pantalla completa dentro del elemento `<main>` del layout principal, nunca en modales estrechos o ventanas flotantes.

---

## 3. Desglose de Vertical Slices de Implementacion

### Slice 5.1: Middleware de Privacidad Financiera y Sanitizacion de Respuestas por Rol (`TASK-RBAC-03`, `TASK-RBAC-04`) [COMPLETADO]
* **Objetivo:** Blindar de forma estricta los datos financieros, garantizando que roles operativos no puedan visualizar margenes, costos ni balances fiscales en API o frontend.
* **Backend:**
  * Middleware `App\Http\Middleware\EnforceFinancialPrivacy`:
    - Bloqueo estricto con HTTP 403 Forbidden en API o redireccion con mensaje de error en Web ante intentos de acceso a rutas financieras (`/admin/finanzas*`, `/api/v1/finance*`) por parte de roles restringidos (`warehouse`, `purchases`, `logistics`, `sales`, `support`, `operator`, `picker`, `packer`, `customer`).
    - Sanitizacion recursiva automatica en respuestas JSON: elimina llaves sensibles de margenes e impuestos (`margin_net`, `financial_margin_amount`, `profit_margin_pct`, `financial_costs`, `gross_revenue`, `sat_taxes`, `taxable_base`, `net_iva_payable`, `platform_retention_isr_1`, `platform_retention_iva_8`, `iva_transferred_16`, `overcharge_amount_mxn`).
    - Para roles de piso (`warehouse`, `logistics`, `sales`, etc.) elimina adicionalmente llaves de costos unitarios (`unit_cost`, `cost`, `cost_amount`, `debit_note_amount`, `payable_net_amount`).
  * Middleware `App\Http\Middleware\EnsureUserHasRole`:
    - Ampliado para reconocer todos los roles operativos de la organizacion y respetar la variable de sesion `active_role` para conmutaciones de perfil en vivo.
  * Modelo `App\Models\User`:
    - `canAccessFinancialData()`: determina autorizacion para roles administrativos y financieros (`admin`, `superadmin`, `finance`, `finanzas`, `all_roles`, `portal`).
    - `getActiveRoleAttribute()`: resuelve rol activo en sesion o base de datos.
    - `canAccessHub(string $hub)`: valida matriz de accesos por modulo segun rol.
    - `isOperativeStaff()`: detecta roles operativos de piso.
  * Modelo `App\Models\OperationalTask`:
    - Scope `forUserRole(?string $role)`: filtra automaticamente el feed de tareas para el departamento correspondiente (almacen, compras, logistica, finanzas, ventas).
  * Directivas Blade y Gates en `App\Providers\AppServiceProvider`:
    - `@canViewFinancials` ... `@else` ... `@endcanViewFinancials`.
    - Gates `view-financial-data` y `access-hub`.
  * Controlador y Rutas:
    - Metodo `switchRole` en `App\Http\Controllers\PortalAccessController`.
    - Ruta Web `POST /admin/roles/switch` y API `POST /api/v1/governance/roles/switch`.
    - Agrupacion de rutas financieras web y api bajo middleware `financial.privacy`.
* **Frontend:**
  * En `resources/views/layouts/portal-panel.blade.php`:
    - Pestaña de Finanzas condicionada con directiva Blade `@canViewFinancials`.
    - Renderizado de alertas `session('error')` en contenedor principal.
  * En `public/js/revops-panel/app.js`:
    - Sincronizacion reactiva via `fetch('/admin/roles/switch')` al conmutar perfil en el modal RBAC de demostracion.
* **Criterios de Aceptacion Superados:**
  * 8 pruebas unitarias y de integracion en `tests/Feature/Governance/FinancialPrivacyTest.php` al 100% verde (44 aserciones).
  * Suite global de la plataforma ampliada a 215 pruebas al 100% verde (1,450 aserciones).
  * Cero emojis en toda la base de codigo, vistas y comentarios.

---

### Slice 5.2: Master Log de Auditoria Omnicanal Inmutable (`TASK-DSH-09`) [COMPLETADO]
* **Objetivo:** Registrar de forma inmutable todas las operaciones criticas ejecutadas en la plataforma para trazabilidad forense y gobernanza de superadministradores.
* **Backend:**
  * Migracion ejecutada: `database/migrations/2026_09_27_000007_create_system_audit_logs_table.php` creando la tabla `system_audit_logs` con indices optimizados para `module`, `action`, `reference_code`, `user_id` y `created_at`.
  * Modelo Eloquent `App\Models\SystemAuditLog`:
    - Casts para `old_values` y `new_values` en formato JSON y marcas de tiempo inmutables (`UPDATED_AT = null`).
    - Blindaje inquebrantable de inmutabilidad: ganchos `static::updating` y `static::deleting` que lanzan `DomainException` si se intenta alterar o borrar un registro.
    - Relacion `user(): BelongsTo`.
    - Scopes de consulta: `forModule()`, `forAction()`, `forUser()`, `search()`.
    - Mutador `diff_summary`: calcula y expone el comparativo atributo por atributo de valores anteriores vs nuevos.
  * Servicio de Dominio `App\Services\Governance\AuditTrailService`:
    - Metodo principal `logAction()`: captura de usuario, rol, modulo, entidad, codigo de referencia, diffs JSON, IP del cliente y User-Agent.
    - Helpers especializados de dominio:
      - `logPriceChange`: registro de variaciones de PVP/costo en catalogos y variantes.
      - `logStockAdjustment`: registro de ajustes fisicos manuales con justificacion.
      - `logSpeiUnblock`: auditoria forense al autorizar pagos retenidos en Cuentas por Pagar.
      - `logStatusOverride`: sobrescritura manual de estados de pedidos.
      - `logRoleSwitch`: trazabilidad de conmutacion de roles operativos en sesion.
    - Metodos de consulta: `getAuditHistory()` con paginacion y `getAuditStats()` con metricas de agregacion diaria.
  * Endpoints y Rutas:
    - `GET /api/v1/governance/audit-logs`: consulta paginada, filtros reactivos y resumen estadistico, con autorizacion estricta exclusiva para perfiles administrativos (`admin`, `superadmin`, `all_roles`, `portal`).
    - `POST /api/v1/governance/audit-logs/custom`: registro de acciones administrativas manuales.
    - Integracion automatica en `PortalAccessController::switchRole` para auditar cambios de rol.
* **Criterios de Aceptacion Superados:**
  * 6 pruebas unitarias y de integracion en `tests/Feature/Governance/MasterAuditLogTest.php` al 100% verde (39 aserciones).
  * Suite global de la plataforma ampliada a 221 pruebas al 100% verde (1,489 aserciones).
  * Cero emojis en toda la base de codigo, vistas y comentarios.

---

### Slice 5.3: Sistema Global de Notificaciones, Toasts y Deep Links de Tareas Operativas (`TASK-UX-03`, `TASK-DSH-04`) [COMPLETADO]
* **Objetivo:** Informar al usuario en tiempo real mediante toasts acumulativos y una campanita interactiva en cabecera con saltos directos en 1 clic a los registros de trabajo.
* **Backend:**
  * Migracion ejecutada: `2026_09_27_000008_create_user_notifications_table.php` (`user_notifications` con indices compuestos y vinculo a tareas operativas).
  * Modelo `App\Models\UserNotification`:
    - Scopes de filtrado `unread`, `byCategory`, `byType` y `forUserOrRole` con aislamiento estricto de privacidad financiera.
    - Accesores `badge_color` y `time_ago`.
  * Servicio de Dominio `App\Services\Governance\NotificationDispatcherService`:
    - Generacion y despacho de notificaciones operativas vinculadas a deep links internos (`/portal/inventario?sku=...`, `/portal/logistica?guia=...`, `/portal/finanzas?folio=...`).
    - Despachadores especializados: `dispatchInventoryRopAlert`, `dispatchLogisticsBlockedAlert`, `dispatchFinanceHoldAlert`, `dispatchPurchasesDelayedAlert` y `dispatchFromOperationalTask`.
    - Metodos de consulta y atencion: `getFeedForUser`, `markAsRead`, `markAllAsRead`, `getUnreadCount`.
  * Endpoints RESTful en `routes/api.php` y `GovernanceApiController`:
    - `GET /api/v1/governance/notifications`: feed categorizado reactivo con conteo no leido y division de severidades.
    - `POST /api/v1/governance/notifications/{id}/mark-read`: marcado individual de atencion.
    - `POST /api/v1/governance/notifications/mark-all-read`: marcado masivo.
    - `POST /api/v1/governance/notifications/dispatch`: emision autorizada de notificaciones personalizadas.
* **Frontend:**
  * Componentes flotantes de Toasts acumulativos en `resources/views/layouts/portal-panel.blade.php` y `resources/views/layouts/app.blade.php`:
    - Contenedor `#global-toast-container` con `z-index: 9999`, stack acumulativo, animacion suave y cierre automatico tras 5 segundos con boton de descarte manual.
    - Implementacion mejorada de `showToast(message, type, duration)` en `public/js/revops-panel/app.js` y `public/js/site.js`.
  * Campanita interactiva y Popover dinamico en Navbar:
    - Badge reactivo `[data-notif-badge]` sincronizado con el conteo no leido del backend.
    - Popover con listado categorizado (Todas, Criticas, Operativas) y enlaces de accion directa ("Atender Folio en 1 Clic").
    - Marcado automatico como leida al presionar el salto directo o el boton de marcado masivo.
* **Criterios de Aceptacion Superados:**
  * 7 pruebas unitarias y de integracion en `tests/Feature/Governance/OperationalNotificationsTest.php` al 100% verde (28 aserciones).
  * Suite global de la plataforma expandida a 228 pruebas al 100% verde (1,517 aserciones).
  * Cero emojis en toda la base de codigo, vistas y comentarios.

---

### Slice 5.4: Mensajeria Interdepartamental Operativa Vinculada a Folios (`TASK-COM-01`) [COMPLETADO]
* **Objetivo:** Proveer un canal de comunicacion formal entre almacén, compras, atencion y logistica, anclando cada nota a folios de negocio reales.
* **Backend:**
  * Migracion ejecutada: `2026_09_27_000009_create_interdepartmental_messages_table.php` (`interdepartmental_messages` con indices en canal, estado de resolucion y folios de referencia).
  * Modelo `App\Models\InterdepartmentalMessage`:
    - Relacion `sender()` y scopes `channel()` y `unresolved()`.
    - Accesores `formatted_time`, `tag_badge` y `priority_badge_color`.
    - Metodo de resolucion formal `resolve($resolvedBy)`.
  * Servicio de Dominio `App\Services\Governance\InterdepartmentalChatService`:
    - Auto-deteccion inteligente de folios mediante regex (`@Pedido:`, `@SKU:`, `@OC:`, `@Guia:`, `@RMA:`).
    - Despacho reactivo de notificaciones operativas prioritarias ante mensajes de severidad urgente.
    - Metodos `sendMessage`, `getChannelMessages`, `resolveMessage`, `getChannelsSummary` y `seedBaselineMessagesIfEmpty`.
  * Endpoints RESTful en `routes/api.php` y `GovernanceApiController`:
    - `GET /api/v1/governance/chat/messages`: timeline ordenado con metadata de autoria y badges de folio.
    - `POST /api/v1/governance/chat/messages`: envio de notas formales con asociacion de folios.
    - `POST /api/v1/governance/chat/messages/{id}/resolve`: resolucion de notas operativas.
    - `GET /api/v1/governance/chat/summary`: resumen de notas pendientes por canal para badges de cabecera.
* **Frontend:**
  * Cajon deslizante de Chat en `resources/views/layouts/portal-panel.blade.php`:
    - Sincronizacion reactiva con la API de gobernanza.
    - Selector de canales con badge numerico de mensajes sin resolver.
    - Chips de contexto rapido (`@SKU:`, `@Pedido:`, `@OC:`, `@Guia:`) y selector de prioridad (Normal, Alta, Urgente).
    - Accion directa de "Resolver" integrada en cada burbuja de conversacion.
* **Criterios de Aceptacion Superados:**
  * 6 pruebas unitarias y de integracion en `tests/Feature/Governance/InterdepartmentalChatTest.php` al 100% verde (17 aserciones).
  * Suite global de la plataforma expandida a 234 pruebas al 100% verde (1,534 aserciones).
  * Cero emojis en toda la base de codigo, vistas y comentarios.

---

### Slice 5.5: Centro de Gobernanza del Sistema, Bateria de Pruebas E2E y Cierre de Prioridad 5 (`TASK-SYS-01`, `TASK-SYS-ENV`) [COMPLETADO]
* **Objetivo:** Consolidar la vista de pantalla completa de Gobernanza, verificar el conmutador visual de entornos, ejecutar la suite completa de pruebas y fusionar a `master`.
* **Backend & Frontend:**
  * Controlador `App\Http\Controllers\PortalGovernanceController` y vista `resources/views/portal/governance.blade.php`:
    - Integracion de las 5 pestañas de subnav:
      1. *Privacidad & Roles RBAC:* Matriz de roles y simulador interactivo de accesos.
      2. *Auditoria Master:* Trazabilidad de operaciones criticas, diffs inmutables y KPIs de auditoria.
      3. *Centro de Notificaciones & Tareas:* Gestion integral de tareas operativas y deep links.
      4. *Mensajeria Interdepartamental:* Canales de comunicacion inter-areas y resumenes de no resueltos.
      5. *Ajustes Globales & Entorno:* Conmutador de entornos Sandbox / Produccion por modulo y bitacora de auditoria.
  * Logica de conmutador de entorno (`TASK-SYS-ENV`):
    - Conmutador visual reactivo en pantalla completa con persistencia en `system_environment_configs`.
    - Registro inmutable en `environment_audit_logs` y `system_audit_logs`.
* **Pruebas y Verificacion E2E:**
  * Creacion de `tests/Feature/Governance/OperationalGovernanceTest.php` cubriendo la totalidad de los flujos de gobernanza (4/4 pruebas pasando).
  * Suite de gobernanza con 31 pruebas unitarias y de integracion al 100% verde (150 aserciones).
  * Verificacion de cero regresiones en la suite global: **238 pruebas pasando al 100% verde (1,556 aserciones)**.
  * Auditoria estricta de cero emojis en codigo, vistas y documentacion.
  * Fusion de la rama `feature/prioridad-5-gobernanza-rbac` a `master` y creacion del tag `v1.5-prioridad-5-gobernanza-rbac`.

---

## 4. Matriz de Modelos Eloquent y Tablas de Base de Datos

| Tarea | Modelos Clave | Tablas Principales | Servicios Principales |
| :--- | :--- | :--- | :--- |
| `TASK-RBAC-03` / `04` | `User`, `OperationalTask` | `users`, `operational_tasks` | `EnforceFinancialPrivacy`, `UserPermissionService` |
| `TASK-DSH-09` | `SystemAuditLog` | `system_audit_logs` | `AuditTrailService` |
| `TASK-UX-03` | `OperationalTask`, `User` | `operational_tasks`, `user_notifications` | `NotificationDispatcherService` |
| `TASK-COM-01` | `InterdepartmentalMessage` | `interdepartmental_messages` | `InterdepartmentalChatService` |
| `TASK-SYS-01` / `ENV` | `SystemSetting`, `User` | `system_settings` | `PortalGovernanceController` |

---

## 5. Criterios de Exito Global de la Prioridad 5

1. **Privacidad Financiera Inquebrantable:** Cero campos de costos, margenes netos o ingresos accesibles por colaboradores de bodega, logistica o atencion al cliente.
2. **Auditoria Forense Confiable:** Imposibilidad de alterar o borrar registros del log maestro de auditoria.
3. **Productividad con Deep Links:** El personal operativo salta en 1 clic desde cualquier notificacion directamente al pedido, caja ROP o devolucion correspondiente.
4. **Comunicacion Trazable:** Erradicacion de acuerdos informales; todas las notas de negociacion o discrepancia quedan vinculadas a sus respectivos folios.
5. **Suite 100% Verde:** Minimo 215 pruebas pasando sin fallos ni advertencias en Laravel.
6. **Cero Emojis:** Absoluto cumplimiento de la Regla 1 en todos los archivos modificados.
