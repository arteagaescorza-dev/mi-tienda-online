# Plan Maestro de Implementacion: Prioridad 3 - Dashboard General & Action Center (Hub 1)
## Plataforma RevOps E-Commerce Multiverso & Suscripciones (Mi Tienda Online)

> **Contexto de Arquitectura:**  
> Este documento rige la integracion fullstack del **Hub 1: Dashboard General & Action Center** en la rama `feature/prioridad-3-dashboard-hub1`, integrando los estandares de [MASTER_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/MASTER_ROADMAP.md), [DASHBOARD_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/DASHBOARD_ROADMAP.md) y [PENDING_TASKS.md](file:///c:/laragon/www/mi-tienda-online/docs/PENDING_TASKS.md).  
> **Regla de Oro:** Se prohibe el uso de emoticones o emojis en codigo, vistas, comentarios, mensajes de commit y documentacion (Regla 1). La carpeta `import/` permanece estrictamente de solo lectura.

---

## 1. Vision Estrategica y Diagnostico de Brecha

El **Hub 1** funciona como el centro neurálgico y cabina de mando diario de la plataforma. Coordina en tiempo real a tres perfiles operativos esenciales:
1. **Dirección General / Finanzas / RevOps (Super Admin):** Visión 360°, embudo multiverso accionable, facturación vs MRR y telemetría de infraestructura.
2. **Personal de Almacén / Empaque / Bodega (`warehouse`, `packer`):** Foco 100% en flujo físico, empaque para corte DHL (14:00 hrs) y **aislamiento estricto de privacidad financiera (CERO simbolos de dinero o ventas)**.
3. **Atención a Clientes & Ventas (`sales`):** Rescate de carritos abandonados vía WhatsApp tokenizado y corrección predictiva de direcciones con riesgo postal.

### Matriz Comparativa de Paridad: Proyecto Actual vs Prototipo Maestro

| Componente | Estado Actual en Proyecto | Prototipo Maestro (`import/app.js` & Roadmaps) | Resolucion en Prioridad 3 |
| :--- | :--- | :--- | :--- |
| **Navegacion de Submodulos** | Botones de subnav decorativos sin alternancia reactiva. | Subnav reactivo con 3 pestañas: *Ejecutivo*, *Operativo* y *Atención & Ventas*. | Conectar reactividad en Alpine.js respetando el rol de usuario activo. |
| **Privacidad Financiera** | El operador de almacén ve ventas en `$`, tickets promedio y cifras financieras. | Aislamiento estricto: cero visibilidad de facturación o márgenes para roles de bodega. | Enmascarar métricas monetarias y sustituirlas por KPIs físicos operativos. |
| **Action Center & Tareas** | Tarjetas con enlaces estáticos hacia inventario. | 4 modales guiados con checklist y resolución en 1 clic (Empaque, Inbound, Surtido, Despacho). | Modales interactivos `max-w-4xl` con checklists de pasillo/rack y resolución atómica. |
| **Monitor SLA de Corte (`TASK-DSH-05`)** | Ausente. No existe noción de envejecimiento ni hora límite. | Semáforo de envejecimiento (DHL 14:00 hrs) con badges (*En tiempo*, *Atención*, *Riesgo corte*). | Cálculo de horas transcurridas desde el pago de la orden y ordenamiento inteligente. |
| **Bandeja de Ventas (`TASK-DSH-01`)** | Inexistente en el Dashboard (solo enlace a leads). | Bandeja con leads calientes, carritos abandonados recuperables y domicilios pausados. | Panel comercial completo con filtros temáticos y bitácora de turno. |
| **Rescate WhatsApp (`TASK-INT-05`)** | No disponible en Dashboard. | Modal con mensaje estructurado, enlace tokenizado y cupón dinámico `RECUPERA10`. | Generador de enlace directo a WhatsApp Web con persistencia en bitácora. |
| **Validador Sepomex (`TASK-DSH-06`)** | Sin validación de domicilios. | Validador postal predictivo (número exterior obligatorio, cruce CP y colonias). | Modal de corrección de dirección dudosa con liberación en 1 clic a almacén. |
| **Embudo Multiverso (`TASK-DSH-02`)** | Gráfica estática sin interactividad. | Selector multiverso (*Terra, Marte, Venus, Tiresias*) y micro-acciones en cada barra de caída. | Embudo accionable con botones de intervención comercial directa. |
| **Gráficas Analíticas SVG (`TASK-DSH-07`)** | Inexistente. | Gráficas SVG nativas: Facturación vs MRR (7d/14d/30d), Vitrinas y Cascada EBITDA. | Componentes visuales SVG nativos ultra-ligeros sin librerías externas pesadas. |
| **Telemetría de APIs (`TASK-DSH-03`)** | Lista estática de texto. | Semáforos pulsantes, latencias en vivo (Stripe, Torzo, DHL, Sepomex) y modal de diagnóstico. | Servicio de telemetría y modal detallado de microservicios con HMAC y webhooks. |
| **Copiloto IA Adaptativo (`TASK-DSH-07`)** | Tarjeta visual fija. | Asistente contextual por rol con drawer lateral deslizable de chat y chips predefinidos. | Widget adaptativo con aislamiento de privacidad por rol y drawer interactivo. |
| **Bitácora del Turno (*Shift Log*)** | Inexistente. | Registro cronológico inmutable de tareas resueltas con hora y operador. | Bitácora reactiva de turno integrada en la vista operativa y comercial. |

---

## 2. Estandares Transversales de Diseno y Reglas Maestras Aplicadas

1. **Regla 1 (Cero Emojis):** Absoluta exclusion de emojis en codigo, vistas, comentarios y commits.
2. **Regla 5 (Cero Cabeceras Redundantes):** Los kickers y titulos residen en el Navbar superior (`layouts/portal-panel.blade.php`); el contenido arranca de inmediato con el Subnav.
3. **Regla 8 (Tooltips de Seccion con '?'):** Todo panel, métrica y tarjeta cuenta con su botón `.section-help-btn` que despliega ayuda contextual.
4. **Regla 10 (Jerarquia Estructural Inamovible):**
   * *1er Nivel:* Barra unificada de navegación de submódulos (`subnavBar`).
   * *2do Nivel:* Tarjetas de métricas y KPIs contextuales correspondientes a la vista activa.
   * *3er Nivel:* Área operativa, paneles, embudos y bitácoras.
5. **Regla 12 (Blindaje Contractual Backend):** Todo endpoint y flujo cuenta con su respectiva ruta, controlador, modelo y validación en Laravel.

---

## 3. Desglose de Vertical Slices de Implementacion

### Slice 3.1: Subnav Unificado, Triple RBAC y Aislamiento de Privacidad en Dashboard [COMPLETADO]
* **Objetivo:** Establecer la arquitectura base reactiva de submódulos en [`resources/views/portal/dashboard.blade.php`](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/dashboard.blade.php), garantizando la alternancia entre *Dashboard Ejecutivo*, *Bandeja Operativa* y *Atención & Ventas*, aplicando la política estricta de aislamiento financiero para roles de bodega.
* **Backend (`StorefrontController::portalDashboard`):**
  * Detecta el rol del usuario autenticado o solicitado (`auth()->user()->role` o query parameter `role`).
  * Hidrata variables globales y estado para las 3 vistas:
    - KPIs financieros consolidados (ventas brutas, ticket promedio, carritos abandonados, pedidos pagados).
    - KPIs físicos para almacén (órdenes listas para empaque, carritos mixtos listos, arribos en tránsito, quiebres ROP).
    - KPIs comerciales para ventas (leads calientes, carritos abandonados recuperables, domicilios pausados).
  * Inyección segura de `window.serverDashboardData` en Blade con `JSON_UNESCAPED_UNICODE`.
* **Frontend (`dashboard.blade.php`):**
  * Subnav reactivo con Alpine.js (`activeTab`: `'executive'`, `'operative'`, `'sales'`).
  * Conmutación automática de pestaña según el rol (`warehouse` inicia en operativa con tabs financieras bloqueadas).
  * Renderizado condicional de KPIs superiores acorde a la vista activa (Regla 10).
  * Enmascaramiento financiero: en la vista operativa y para roles de bodega, todo valor monetario se sustituye por unidades físicas y se despliega el aviso normativo de privacidad.
* **Criterios de Aceptacion Superados:**
  * Vista adaptativa probada en `tests/Feature/RevOps/DashboardHub1ModuleTest.php` para perfiles `admin`, `warehouse` y `sales`.
  * Suite completa de pruebas de Laravel ampliada a 164 pruebas al 100% en verde (852 aserciones).

---

### Slice 3.2: Action Center Operativo, Checklists Guiadas y Monitor SLA (`TASK-DSH-04`, `TASK-DSH-05`, `TASK-INV-01`) [COMPLETADO]
* **Objetivo:** Dotar a la Bandeja Operativa de un centro de resolución de tareas en 1 clic con checklists físicas, semáforo de envejecimiento SLA y registro inmutable en bitácora de turno.
* **Backend:**
  * Migración `database/migrations/2026_09_27_000001_enhance_operational_tasks_and_shift_logs_tables.php` y modelos `OperationalTask` y `ShiftLog`.
  * Endpoints en `app/Http/Controllers/Api/RevOps/DashboardApiController.php`:
    - `GET /api/v1/dashboard/tasks` (listado dinámico con cálculo de urgencia SLA).
    - `POST /api/v1/dashboard/tasks/{id}/complete` (cierre atómico y registro cronológico inmutable en bitácora).
    - `GET /api/v1/dashboard/shift-logs` (historial cronológico de eventos del turno).
  * Hidratación en `StorefrontController::portalDashboard` inyectando tareas y bitácora en `window.serverDashboardData`.
* **Frontend:**
  * Banner superior de Corte Diario DHL (14:00 hrs) con cuenta regresiva dinámica en vivo y semáforo visual de 3 estados (Verde, Ámbar, Rojo pulsante).
  * Filtros por categoría reactivos: *Todas*, *Empaque*, *Recepción*, *Surtido*, *Expedición*.
  * Modales interactivos `max-w-4xl` a 2 columnas con checklists operativas y retroalimentación inmediata:
    1. **Picking & Packing (#MIX-1048):** Checklist con ubicación de pasillo/rack (`A-02`, `R-08`), artículos verificados y botón de sellado.
    2. **Recepción Inbound (#OC-2026-003):** Cotejo físico de curva de tallas y precinto.
    3. **Surtido Interno ROP:** Reubicación de anaquel A-01 desde rack R-04.
    4. **Despacho Courier:** Entrega a chofer DHL con firma/gafete y liberación de manifiesto.
  * Bitácora inmutable del turno (*Shift Log*) con inserción reactiva en tiempo real al completar tareas.
* **Criterios de Aceptacion Superados:**
  * Pruebas completas en `tests/Feature/RevOps/DashboardHub1ModuleTest.php` al 100% verde (3 pruebas, 110 aserciones).
  * Suite global de la plataforma ampliada a 166 pruebas al 100% verde (943 aserciones).
  * Cero emojis en toda la base de código.

---

### Slice 3.3: Bandeja de Atención & Ventas, Rescate WhatsApp y Validador Postal Sepomex (`TASK-DSH-01`, `TASK-DSH-06`, `TASK-INT-05`) [COMPLETADO]
* **Objetivo:** Implementar la vista comercial para asesores de atención al cliente y soporte, integrando recuperación de carritos por WhatsApp y validación predictiva de domicilios.
* **Backend:**
  * Endpoints en `DashboardApiController.php`:
    - `GET /api/v1/dashboard/sales/incidents` (listado de leads, carritos y direcciones dudosas).
    - `POST /api/v1/dashboard/sales/rescue-whatsapp` (registro de contacto tokenizado).
    - `POST /api/v1/dashboard/sales/validate-address` (validación de código postal y liberación de orden).
    - `GET /api/v1/dashboard/sales/sepomex-lookup` (catálogo y sugerencias Sepomex).
* **Frontend:**
  * 4 KPIs contextuales de atención con botones de ayuda `?`.
  * Filtros: *Todas*, *Leads & Consultas*, *Carritos Abandonados*, *Direcciones Detenidas*, *Suscriptores*.
  * **Modal de Recuperación WhatsApp (`TASK-INT-05`):** Plantilla oficial, enlace tokenizado prellenado (`/checkout?resume=TOKEN`), cupón `RECUPERA10` al 10% OFF y confirmación de envío.
  * **Modal Validador Sepomex (`TASK-DSH-06`):** Validación reactiva de CP, número exterior obligatorio, autocompletado de municipio/estado y liberación inmediata de la orden con badge *"Sepomex OK"*.
  * **Modal Ficha CRM Leads:** Seguimiento de scoring, canal preferido y notas de contacto.
  * Bitácora comercial inmutable de incidencias resueltas.
* **Entregables Verificados:**
  * Pruebas completas en `tests/Feature/RevOps/DashboardHub1ModuleTest.php` al 100% verde (7 pruebas, 203 aserciones).
  * Suite global de la plataforma ampliada a 170 pruebas al 100% verde (1,036 aserciones).
  * Cero emojis en toda la base de código.

---

### Slice 3.4: Cabina Analítica SVG, Embudo Multiverso Accionable y Telemetría de APIs (`TASK-DSH-02`, `TASK-DSH-03`, `TASK-DSH-07`)
* **Objetivo:** Equipar la Vista Ejecutiva con analítica visual avanzada en SVG nativo, embudo comercial con micro-acciones directas, monitoreo en vivo de microservicios y Copiloto IA contextual.
* **Backend:**
  * Endpoints en `DashboardApiController.php`:
    - `GET /api/v1/dashboard/analytics/charts` (datos temporales 7d/14d/30d para facturación vs MRR, cuotas de vitrinas y cascada EBITDA).
    - `GET /api/v1/dashboard/system-health` (telemetría de latencias y estado de conectores).
    - `POST /api/v1/dashboard/system-health/ping` (diagnóstico global en caliente).
* **Frontend:**
  * **3 Gráficas Analíticas SVG Nativas:**
    1. *Facturación vs MRR Recurrente:* Curva en degradado esmeralda, línea discontinua de MRR y selector de escala 7d/14d/30d.
    2. *Participación por Vitrina Multiverso:* Barras horizontales proporcionales (Terra, Marte, Venus, Tiresias).
    3. *Cascada de Margen EBITDA:* Desglose desde ingresos brutos deduciendo COGS, empaque, fletes, pasarelas e impuestos.
  * **Embudo Comercial Multiverso Accionable (`TASK-DSH-02`):** Selector de vitrinas y micro-botones de acción directa en cada nivel (recuperar abandonos, balance híbrido, auditoría 3DS, liberar a almacén, fidelizar membresías).
  * **Panel de Telemetría de APIs (`TASK-DSH-03`):** Semáforos verdes pulsantes, latencias en ms de Stripe, Mercado Pago, Torzo Scraper, DHL y Sepomex, botón *"Ejecutar Diagnóstico Global"* y modal detallado de microservicios con HMAC y webhooks.
  * **Widget y Drawer de Copiloto IA (`TASK-DSH-07`):** Tarjeta interactiva con recomendaciones adaptadas al rol activo y drawer lateral deslizable de chat estratégico.

---

### Slice 3.5: Suite Integral de Pruebas Automatizadas Hub 1 y Verificación General
* **Objetivo:** Garantizar la cobertura integral de pruebas unitarias y de integración para el Hub 1, validando la ausencia total de regresiones en toda la plataforma.
* **Archivo de Pruebas:** `tests/Feature/RevOps/DashboardHub1ModuleTest.php`
  * Pruebas clave a verificar:
    1. `test_portal_dashboard_renders_with_triple_rbac_and_hides_financials_for_warehouse_role`
    2. `test_can_fetch_operational_tasks_with_sla_status_and_complete_task`
    3. `test_can_rescue_abandoned_cart_via_whatsapp_with_tokenized_link`
    4. `test_can_validate_predictive_address_and_release_order_to_warehouse`
    5. `test_can_fetch_executive_analytics_charts_and_multiverse_funnel`
    6. `test_can_ping_system_health_telemetry_connectors`
* **Criterio de Aceptación:**
  * Ejecución exitosa de `php artisan test` con el 100% de pruebas en verde ($\ge 175$ pruebas).
  * Cero emojis en toda la base de código y vistas.
  * Merge limpio de `feature/prioridad-3-dashboard-hub1` a `master` y etiquetado del hito `v1.3-prioridad-3-dashboard-hub1`.
