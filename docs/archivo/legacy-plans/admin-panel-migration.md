# Plan de Migracion del Panel Administrativo (RevOps 6 Hubs)

## Proposito

Este documento gobierna la adaptacion del prototipo UI-first construido en
`C:\Users\Mickey\Documents\export` (arquitectura de 6 Hubs Operativos) hacia
el backend real de `mi-tienda-online`. Sustenta y ordena el trabajo en 4 fases
con una compuerta de aprobacion obligatoria entre la Fase 1 y la Fase 2.

> Referencia funcional del prototipo: `MASTER_ROADMAP.md`, `PENDING_TASKS.md`,
> `INVENTORY_ROADMAP.md`, `DASHBOARD_ROADMAP.md`, `LOGISTICS_ROADMAP.md` y
> `BACKEND_INTEGRATION_SPEC.md`, todos dentro de `C:\Users\Mickey\Documents\export`.

## Gobernanza de 4 Fases

| Fase | Objetivo | Entra en vigor cuando |
| --- | --- | --- |
| **Fase 1** | Migrar unicamente la UI del panel (sin tocar esquema de base de datos) y dejar constancia documental de decisiones y brechas. | Inicio inmediato. |
| **Fase 2** | Crear y corregir tablas/migraciones/modelos del backend segun `BACKEND_INTEGRATION_SPEC.md`. | **Solo tras aprobacion formal de la Fase 1** (checklist de la Seccion 5). |
| **Fase 3** | Pruebas reales con datos y flujos conectados (no simulados). | Al cerrar Fase 2 con migraciones aplicadas y endpoints operativos. |
| **Fase 4** | Montaje en hosting (HostGator cPanel u otro definido). | Al cerrar Fase 3 con suite de pruebas verde. |

**Regla de bloqueo:** ninguna tarea de la Fase 2 (migraciones, columnas nuevas,
modelos, endpoints) se inicia mientras la Fase 1 no este aprobada explicitamente
por el usuario. Documentar la aprobacion (fecha y alcance) en la Seccion 5 de
este archivo antes de abrir la Fase 2.

## Diagnostico de Punto de Partida

| Elemento | Ubicacion | Estado |
| --- | --- | --- |
| Prototipo fuente de verdad (V2, 6 Hubs) | `C:\Users\Mickey\Documents\export` | Completo en UI para Hub 4 y Hub 1; parcial en Hub 6; Hub 2/3/5 aun no construidos en el propio prototipo. |
| Prototipo V1 (15 modulos planos) | `export/` dentro de este workspace | **Superado / obsoleto.** No debe usarse como referencia; se recomienda archivarlo cuando se confirme con el usuario. |
| Portal administrativo real actual | `resources/views/portal/*.blade.php` + `StorefrontController` + `routes/web.php` (`/admin/*`) | Funcional con datos reales de Eloquent (dashboard, inventario, finanzas simuladas, modulo generico para chatbot/atribucion/emailing/cupones/conciliacion/logistica/garantias). Usa layout unico `layouts/app.blade.php` y `public/css/crm.css` compartido con la tienda publica. |

### Riesgo tecnico critico detectado

`public/css/crm.css` (101 KB) ya define clases `.sidebar`, `.card`, `.main`,
`.button`, `.portal-*` para la tienda y el portal actuales. El `styles.css`
del prototipo (52 KB) reutiliza los mismos nombres de clase (`.topbar`,
`.sidebar`, `.card`, `.button`) con un diseno visual distinto. **Cargar ambos
archivos en el mismo layout rompe estilos.**

**Decision de arquitectura (Fase 1):** el panel de 6 Hubs se sirve desde un
layout Blade dedicado y aislado, no desde `layouts/app.blade.php`, con su
propia hoja de estilos namespaced. La tienda publica y el portal heredado
(mientras se migra modulo por modulo) siguen usando `crm.css` sin cambios.

## Mapa de Rutas: Hub -> Implementacion Actual -> Accion

| Hub | Ruta(s) actuales | Vista actual | Accion en Fase 1 |
| --- | --- | --- | --- |
| 1. Dashboard General | `portal.dashboard` (`/admin/dashboard`) | `portal/dashboard.blade.php` | Reemplazar contenido por Action Center (triple RBAC) del prototipo; conservar la misma ruta y controlador. |
| 2. Tienda & CMS | No existe | - | Crear vista minima de navegacion (placeholder UI) alineada a la IA del Hub; contenido funcional queda fuera de alcance (Fase 5 en el propio prototipo). |
| 3. Marketing & CRM | `leads.index`, `leads.board`, `portal.scoring`, `portal.module` (`chatbot`, `atribucion`, `emailing`, `cupones`) | `leads/*`, `portal/scoring.blade.php`, `portal/module.blade.php` | Unificar bajo una barra de pestanas de Hub 3 conservando las rutas existentes como destino de cada pestana. |
| 4. Inventario & Cadena de Suministro | `portal.inventory` (`/admin/inventario-catalogo`) | `portal/inventory.blade.php` | **Prioridad 1.** Reemplazar por `inventory.blade.php` del prototipo (ya en sintaxis Blade), conectando los datos reales existentes y marcando con comentarios los bloques que dependen de columnas aun no creadas. |
| 5. Finanzas & Conciliacion | `portal.finance` (`/admin/finanzas`), `portal.module('conciliacion')` | `portal/finance.blade.php`, `portal/module.blade.php` | Rediseno visual con datos simulados explicitos hasta Fase 2; unir conciliacion como pestana del mismo Hub. |
| 6. Logistica & Post-Venta | `portal.module('logistica')`, `portal.module('garantias')` | `portal/module.blade.php` (generico) | Crear controlador y vista dedicados (`portal.logistics`) con las 4 pestanas del prototipo (Expedicion, Guias, Torre de Control, RMA). |

Las rutas y nombres existentes **no se renombran** en Fase 1 para no romper
pruebas ni enlaces; unicamente se agregan rutas nuevas donde no existian.

## Fase 1: Tareas Prioritarias de Integracion de UI

Orden de ejecucion (de mayor a menor prioridad, siguiendo el mismo orden de
madurez que ya tiene el propio prototipo: Hub 4 y Hub 1 estan 100% completos,
Hub 6 parcial, Hub 5/3/2 pendientes):

1. **Shell del panel (fundacional).**
   - Crear `resources/views/layouts/portal-panel.blade.php`: topbar con
     kicker/titulo de Hub, campanita de notificaciones, chat desplegable,
     conmutador de entorno Sandbox/Produccion, sidebar con los 6 Hubs y
     footer de avatar/rol + ajustes.
   - Crear `public/css/revops-panel.css` (adaptado de `styles.css` del
     prototipo, con prefijo/namespace propio, ej. `.rv-*` o envoltura
     `.revops-panel`) para eliminar colision con `crm.css`.
   - Portar `app.js` del prototipo a `public/js/revops-panel/app.js`,
     removiendo llamadas a endpoints inexistentes y dejando el estado
     inicial de Alpine con datos mock claramente comentados.
   - Mantener el middleware `role:portal` existente sobre todas las rutas.

2. **Hub 4 - Inventario (mayor prioridad de contenido).**
   - Sustituir `portal/inventory.blade.php` por la version adaptada del
     prototipo (`inventory.blade.php` de 650 KB ya en Blade), conservando
     el binding a `$products`, `$summary`, `$syncRuns`, `$variantMappings`
     que ya provee `StorefrontController::portalInventory`.
   - Portar `inventory-module.js` a `public/js/revops-panel/inventory.js`.
   - Marcar explicitamente (comentario Blade) cada seccion visual que
     depende de columnas/tablas aun no existentes (CPP, lotes/caducidad,
     slotting, etiquetas termicas, BOM) para que quede clara la brecha de
     cara a la Fase 2.

3. **Hub 1 - Dashboard General (Action Center).**
   - Sustituir `portal/dashboard.blade.php` por la vista de triple RBAC
     (Operativo / Atencion & Ventas / Ejecutivo) del prototipo.
   - Conservar los KPIs reales ya calculados en
     `StorefrontController::portalDashboard` (ventas hoy, leads, carritos
     abandonados, ticket promedio) e insertar los KPIs adicionales del
     prototipo como mock explicito hasta Fase 2.

4. **Hub 6 - Logistica & Post-Venta.**
   - Crear `PortalLogisticsController` + ruta `portal.logistics`
     (`/admin/logistica`) con las pestanas Expedicion & Manifiestos,
     Guias Termicas, Torre de Control y Garantias/RMA.
   - Portar unicamente la UI ya completa en el prototipo (manifiestos,
     cajas/volumetria, escudo antifraude); las pestanas aun planificadas
     en el propio prototipo (guias termicas, torre de control, RMA) se
     migran como UI estatica/mock.

5. **Hub 5 - Finanzas & Conciliacion.**
   - Redisenar `portal/finance.blade.php` con la estructura visual del
     Hub 5 (aun no detallada a fondo en el prototipo origen); unificar la
     pestana de conciliacion que hoy vive en `portal.module('conciliacion')`.

6. **Hub 3 - Marketing & CRM.**
   - Unificar bajo una barra de pestanas de Hub 3 las pantallas ya
     existentes (`leads.index`, `leads.board`, `portal.scoring`) y los
     modulos genericos (`chatbot`, `atribucion`, `emailing`, `cupones`)
     sin cambiar sus rutas ni su logica actual.

7. **Hub 2 - Tienda & CMS.**
   - Prioridad mas baja: crear una vista de navegacion minima (placeholder)
     acorde a la IA del Hub, ya que el propio prototipo tampoco tiene esta
     seccion desarrollada (Fase 5 en `MASTER_ROADMAP.md`).

8. **Cierre y compuerta de aprobacion.**
   - Ejecutar `php artisan test` para confirmar que ninguna prueba existente
     se rompio por el cambio de layout/rutas.
   - Verificar visualmente cada Hub contra las 14 reglas transversales de
     `MASTER_ROADMAP.md` (cero emojis, drawers full-width, tooltips `?`,
     jerarquia subnav->KPIs->contenido, "Cargar mas", filtros por columna,
     radios 4/6/8/12px, WebP).
   - Registrar capturas de evidencia visual y actualizar la Seccion 5 con
     la aprobacion formal antes de abrir la Fase 2.

## Decisiones Tecnicas Adoptadas para Fase 1

- Se mantiene Tailwind CDN + Alpine.js CDN identico al prototipo (mismo
  enfoque UI-first ya usado en el checkout de `project-roadmap.md`), para
  maximizar velocidad de paridad visual. Migrar a un pipeline de Vite queda
  fuera de alcance de Fase 1 y se revisa solo si aparecen problemas de
  rendimiento o build.
- Los nombres de rutas existentes no cambian; solo se agregan rutas nuevas
  para Hubs sin cobertura previa (Hub 6, Hub 2).
- Ningun dato mostrado en Fase 1 requiere una migracion nueva: donde el
  prototipo asuma columnas que no existen todavia, se deja un valor mock
  explicitamente comentado (`{{-- MOCK Fase 2: campo pendiente en BD --}}`).

## Fase 2 (vista previa, no iniciar sin aprobacion de Fase 1)

Base contractual: `BACKEND_INTEGRATION_SPEC.md` del prototipo (migraciones,
modelos Eloquent, FormRequests y endpoints REST). Se debe generar un diff
contra las migraciones ya existentes en `database/migrations/` (products,
product_variants, inventories, suppliers, orders, etc.) para identificar
columnas y tablas realmente faltantes antes de escribir migraciones nuevas.

## Fase 3 (vista previa)

Sustituir todo dato mock insertado en Fase 1 por datos reales, y ampliar la
suite de pruebas (`tests/Feature`, `tests/Unit`) para cubrir cada Hub migrado.

## Fase 4 (vista previa)

Seguir la estrategia de despliegue ya descrita en `revops-architecture.md`
(HostGator cPanel, `QUEUE_CONNECTION=database`, cron, SFTP).

## Seccion 5: Registro de Aprobacion de Fase 1

| Fecha | Alcance aprobado | Aprobado por |
| --- | --- | --- |
| _pendiente_ | _pendiente_ | _pendiente_ |
