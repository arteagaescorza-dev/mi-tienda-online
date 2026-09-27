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

### Slice 5.2: Master Log de Auditoria Omnicanal Inmutable (`TASK-DSH-09`)
* **Objetivo:** Registrar de forma inmutable todas las operaciones criticas ejecutadas en la plataforma para trazabilidad forense y gobernanza de superadministradores.
* **Backend:**
  * Migracion `create_system_audit_logs_table`:
    - Campos: `id`, `user_id`, `user_name`, `role`, `action` (create, update, delete, status_override, price_change, spei_unblock, rma_verdict), `module` (inventory, orders, finance, logistics, users, settings), `entity_type`, `entity_id`, `reference_code`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at`.
  * Modelo `App\Models\SystemAuditLog` con casts JSON e inmutabilidad garantizada (metodos `update()` y `delete()` bloqueados con excepcion).
  * Servicio `App\Services\Governance\AuditTrailService`:
    - `logAction(string $action, string $module, string $referenceCode, ?array $oldValues, ?array $newValues, string $description)`.
    - Integracion automatica mediante eventos de dominio y decoradores de servicios.
* **Frontend:**
  * Panel de Auditoria en vista de Gobernanza:
    - Tabla cronologica de eventos con buscador reactivo, filtros por modulo, rol y fecha.
    - Visor modal de diff JSON (antes vs despues) para auditorias de cambio de precio y stock.
* **Criterios de Aceptacion:**
  * Pruebas de inmutabilidad: cualquier intento de alterar o eliminar un log arroja `DomainException`.
  * Pruebas de persistencia correcta con captura de IP y diffs de atributos.

---

### Slice 5.3: Sistema Global de Notificaciones, Toasts y Deep Links de Tareas Operativas (`TASK-UX-03`, `TASK-DSH-04`)
* **Objetivo:** Informar al usuario en tiempo real mediante toasts acumulativos y una campanita interactiva en cabecera con saltos directos en 1 clic a los registros de trabajo.
* **Backend:**
  * Endpoints RESTful en `routes/api.php`:
    - `GET /api/v1/governance/notifications`: feed de tareas y alertas activas para el rol autenticado.
    - `POST /api/v1/governance/notifications/{id}/mark-read`: marcado de notificacion como atendida.
  * Servicio `App\Services\Governance\NotificationDispatcherService`:
    - Generacion y despacho de notificaciones operativas vinculadas a deep links internos (`/portal/inventario?sku=...`, `/portal/logistica?guia=...`, `/portal/finanzas?oc=...`).
* **Frontend:**
  * Componente flotante de Toasts en `resources/views/layouts/app.blade.php`:
    - Contenedor con `z-index: 9999`, stack acumulativo, animacion de entrada/salida y cierre automatico tras 6 segundos.
  * Campanita interactiva en Navbar:
    - Badge con contador numerico reactivo de tareas pendientes del rol.
    - Popover con listado categorizado (Urgente, Normal, Informativo) y botones de accion directa ("Atender Folio en 1 Clic").
* **Criterios de Aceptacion:**
  * Despacho y renderizado correcto de toasts ante operaciones exitosas o advertencias.
  * El contador del navbar se actualiza y los deep links dirigen con precision al modulo y registro correspondiente.

---

### Slice 5.4: Mensajeria Interdepartamental Operativa Vinculada a Folios
* **Objetivo:** Proveer un canal de comunicacion formal entre almacén, compras, atencion y logistica, anclando cada nota a folios de negocio reales.
* **Backend:**
  * Migracion `create_interdepartmental_messages_table`:
    - Campos: `id`, `channel` (`almacen_compras`, `logistica_atencion`, `finanzas_auditoria`, `general`), `sender_id`, `sender_name`, `sender_role`, `target_role`, `message`, `reference_type` (order, po, rma, claim, sku), `reference_code`, `priority` (normal, high, urgent), `is_resolved`, `resolved_at`, `created_at`.
  * Modelo `App\Models\InterdepartmentalMessage`.
  * Servicio `App\Services\Governance\InterdepartmentalChatService`:
    - `sendMessage(string $channel, string $message, ?string $refType, ?string $refCode, string $priority)`.
    - `getChannelMessages(string $channel, int $limit = 50)`.
    - `resolveMessage(int $messageId)`.
  * Rutas API y Web para envio y consulta de mensajes.
* **Frontend:**
  * Tablero de Mensajeria en Vista de Gobernanza y cajon colapsable contextual:
    - Selector de canales con badge de mensajes sin resolver.
    - Timeline de mensajes con referencia cliqueable hacia la entidad vinculada.
    - Formulario agil de envio con seleccion de prioridad y folio de referencia.
* **Criterios de Aceptacion:**
  * Pruebas validando creacion de mensajes, filtrado por canal y asociacion correcta de folios.

---

### Slice 5.5: Centro de Gobernanza del Sistema, Bateria de Pruebas E2E y Cierre de Prioridad 5 (`TASK-SYS-01`, `TASK-SYS-ENV`)
* **Objetivo:** Consolidar la vista de pantalla completa de Gobernanza, verificar el conmutador visual de entornos, ejecutar la suite completa de pruebas y fusionar a `master`.
* **Backend & Frontend:**
  * Controlador `App\Http\Controllers\PortalGovernanceController` y vista `resources/views/portal/governance.blade.php`:
    - Integracion de las 5 pestañas de subnav:
      1. *Privacidad & Roles RBAC:* Matriz de roles y simulador de accesos.
      2. *Auditoria Master:* Trazabilidad de operaciones criticas y visor diff.
      3. *Centro de Notificaciones & Tareas:* Gestion integral de tareas operativas.
      4. *Mensajeria Interdepartamental:* Canales de comunicacion inter-areas.
      5. *Ajustes Globales & Entorno:* Conmutador Sandbox / Produccion, llaves y telemetria de servicios.
  * Logica de conmutador de entorno (`TASK-SYS-ENV`):
    - Indicador en barra superior ("MODO PRUEBAS / SANDBOX" vs "PRODUCCION ACTIVA").
    - Bloqueo de cargos reales y desvio a simuladores cuando el entorno este en sandbox.
* **Pruebas y Verificacion E2E:**
  * Creacion de `tests/Feature/Governance/OperationalGovernanceTest.php` cubriendo la totalidad de los flujos de gobernanza.
  * Verificacion de cero regresiones en la suite global ($\ge 215$ pruebas pasando al 100%).
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
