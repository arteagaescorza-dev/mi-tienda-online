# Plan Maestro de Implementacion: Prioridad 4 - Sinergias Transversales Automatizadas (Cross-Hub)
## Plataforma RevOps E-Commerce Multiverso & Suscripciones (Mi Tienda Online)

> **Contexto de Arquitectura:**  
> Este documento rige la implementacion fullstack de la **Prioridad 4: Sinergias Transversales Automatizadas (Cross-Hub)** en la rama `feature/prioridad-4-sinergias-crosshub`, conectando de forma reactiva y atomica las operaciones de **Hub 6 (Logistica & Fulfillment)**, **Hub 4 (Inventario & Cadena de Suministro)**, **Hub 5 (Finanzas & Conciliacion)** y **Hub 7 (Convenios & B2B)**.  
> **Referencias Normativas:** [MASTER_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/MASTER_ROADMAP.md), [PENDING_TASKS.md](file:///c:/laragon/www/mi-tienda-online/docs/PENDING_TASKS.md), [BACKEND_INTEGRATION_SPEC.md](file:///c:/laragon/www/mi-tienda-online/docs/BACKEND_INTEGRATION_SPEC.md) y [ROLES_AND_WORKFLOWS_SPEC.md](file:///c:/laragon/www/mi-tienda-online/docs/ROLES_AND_WORKFLOWS_SPEC.md).  
> **Regla de Oro:** Se prohibe terminantemente el uso de emoticones o emojis en codigo, vistas, comentarios, mensajes de commit y documentacion (Regla 1). La carpeta `import/` permanece estrictamente de solo lectura.

---

## 1. Vision Estrategica y Diagnostico de Brecha

La operacion de un e-commerce omnicanal de ropa interior, cosmetica y suscripciones colapsa si los modulos funcionan como silos aislados. La **Prioridad 4** erradica las fricciones manuales y discrepancias contables mediante la automatizacion inquebrantable de 4 puentes criticos:

1. **Logistica $\leftrightarrow$ Inventario (Consumo de Insumos):** Al emitir una guia de envio, el inventario de la caja o bolsa seleccionada debe descontarse en Kardex de inmediato, alertando con ROP cuando el material de empaque este por agotarse.
2. **Logistica $\leftrightarrow$ Inventario $\leftrightarrow$ Finanzas/CRM (Garantias & RMA):** El dictamen de una devolucion debe obedecer la normativa sanitaria PROFECO (ropa intima usada JAMAS regresa al stock comercializable; se clasifica como merma destructiva) mientras emite reposiciones o saldo a favor sin errores.
3. **Logistica $\leftrightarrow$ Finanzas (Auditoria de Fletes):** Cruce sistematico entre las guias registradas en el manifiesto diario y las facturas de transportistas (DHL, Estafeta, FedEx) para identificar cobros indebidos por sobrepeso volumetrico IATA.
4. **Compras $\leftrightarrow$ Almacen $\leftrightarrow$ Finanzas (Cotejo de 3 Vias Inbound):** Blindaje contable entre la Orden de Compra (OC), el conteo ciego recibido en rampa y la factura del proveedor antes de autorizar la dispersion SPEI.

### Matriz Comparativa: Estado Actual vs Estado Objetivo Prioridad 4

| Componente Cross-Hub | Estado Actual en Proyecto | Prototipo Maestro & Reglas de Negocio | Resolucion en Prioridad 4 |
| :--- | :--- | :--- | :--- |
| **Deduccion de Empaque (`TASK-INT-13`)** | Se selecciona la caja (`BOX-M02`), pero no genera asiento en Kardex de insumos. | Toda emision de guia deduce 1 unidad de `package_types` y registra movimiento en Kardex. | Deduccion atomica en `InventoryMovement` con tipo `CONSUMO_EMPAQUE_ENVIO` y alerta ROP. |
| **Resolucion RMA (`TASK-INT-09`, `TASK-LOG-04B`)** | La pantalla de RMA cambia estado visual pero no genera orden de reemplazo ni ajuste contable. | Distincion sanitaria PROFECO: precinto sellado reingresa a Kardex; prenda abierta va a merma. Genera `#ORD-XXXX-RMA` o saldo en monedero. | Servicio `RmaResolutionService` fullstack con generador de orden de reemplazo o credito y asiento en Kardex. |
| **Conciliacion Courier (`TASK-INT-08`)** | Carga de CSV de paqueteria simulada en frontend. | Cruce de peso IATA (L x W x H / 5000) vs peso facturado. Deteccion de discrepancias y generador de reclamacion. | Algoritmo de conciliacion con categorizacion de incidencias (Sobrepeso, Guia no reconocida, Tarifa fuera de contrato). |
| **Cotejo 3 Vias Inbound (`TASK-INT-07`)** | Rampa registra faltantes, pero compras y finanzas deben conciliar manualmente. | Cruce automatico: Si `Rampa != OC`, emision de nota de debito y retencion de pago en CxP. | Servicio de liquidacion con calculo de IVA 16%, retencion SAT y ajuste de saldo en Cuentas por Pagar. |
| **Convenios B2B a CxP (`TASK-INT-19`)** | Liquidacion de taquilla/vouchers genera registro en aliados pero no impacta CxP central. | Toda liquidacion de taquilla con saldo deudor se integra a Cuentas por Pagar (`vendor_bills`). | Creacion automatica de `VendorBill` vinculado a la sesion de liquidacion de aliado. |

---

## 2. Estandares Transversales de Diseno y Reglas Maestras Aplicadas

1. **Regla 1 (Cero Emojis):** Exclusion absoluta de emojis en codigo, vistas, comentarios, base de datos y commits.
2. **Regla 5 (Cero Cabeceras Redundantes):** Los titulos y kickers residen en el Navbar superior; el contenido inicia de inmediato con el Subnav.
3. **Regla 8 (Tooltips de Seccion con '?'):** Todo panel, tabla y metrica cuenta con su boton `.section-help-btn` para orientacion contextual.
4. **Regla 10 (Jerarquia Estructural Inamovible):**
   * *1er Nivel:* Barra de navegacion de submodulos (`subnavBar`).
   * *2do Nivel:* Tarjetas de metricas y KPIs de impacto transversal.
   * *3er Nivel:* Paneles de trabajo, tablas comparativas y modales de accion.
5. **Regla 12 (Blindaje Contractual Backend):** Toda operacion transversal cuenta con FormRequest, transacciones de base de datos (`DB::transaction`) y pruebas automatizadas PHPUnit.

---

## 3. Desglose de Vertical Slices de Implementacion

### Slice 4.1: Deduccion Automatica de Cajas y Empaque en Kardex (`TASK-INT-13`, `TASK-LOG-06`) [COMPLETADO]
* **Objetivo:** Conectar la emision de etiquetas y cierre de manifiestos en Hub 6 con el Kardex de inventario en Hub 4.
* **Backend:**
  * Migracion `2026_09_27_000003_create_packaging_movements_and_enhance_logistics_tables.php`.
  * Modelo `App\Models\PackagingMovement` y ampliacion de `PackageType` y `Shipment`.
  * Servicio `App\Services\RevOps\PackagingFulfillmentService`:
    - Deduccion atomica e idempotente (`fulfillShipmentPackaging`, `fulfillBatch`).
    - Registro inmutable en Kardex de empaque con tipo `CONSUMO_EMPAQUE_ENVIO` y `ENTRADA_REABASTECIMIENTO`.
    - Disparo reactivo de alertas ROP (`OperationalTask`) para compras y almacen cuando `stock_available <= reorder_point`.
    - Resolucion de alertas ROP al reabastecer stock.
  * Ruta y controlador `POST /admin/logistica/envios/{shipment}/empacar` y deduccion automatica en `closeManifest`.
* **Frontend:**
  * En [resources/views/portal/logistics.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/logistics.blade.php):
    - En Mesa de Expedicion: Badge "Kardex OK" para bultos con empaque deducido y boton en 1 clic "Deducir Insumo".
    - En Cajas & Empaques: Badges ROP pulsantes ("ALERTA ROP"), campos editables de Stock y ROP.
    - Tabla completa de auditoria: Kardex de Movimientos de Insumos de Empaque.
* **Criterios de Aceptacion Superados:**
  * 5 pruebas unitarias/funcionales en `tests/Feature/RevOps/CrossHubPackagingTest.php` al 100% verde (26 aserciones).
  * Suite global de la plataforma ampliada a 184 pruebas al 100% verde (1,213 aserciones).
  * Cero emojis en toda la base de codigo y vistas.

---

### Slice 4.2: Motor de Resoluciones RMA y Garantias con Aislamiento Sanitario PROFECO (`TASK-INT-09`, `TASK-LOG-04B`) [COMPLETADO]
* **Objetivo:** Automatizar la resolucion de devoluciones dictaminadas en Logistica, emitiendo reemplazos o monedero digital y protegiendo el stock con aislamiento sanitario.
* **Backend:**
  * Migracion `2026_09_27_000004_create_rma_resolutions_and_enhance_rma_tables.php`:
    - Creacion de tabla inmutable `rma_resolutions` (`rma_request_id`, `resolution_type`, `replacement_order_id`, `replacement_order_number`, `store_credit_code`, `store_credit_amount`, `whatsapp_message_sent`, `whatsapp_payload`, `resolved_by_user_id`, `notes`).
    - Enriquecimiento de `rma_requests` con trazabilidad directa de reemplazo, cupon y fecha de resolucion.
    - Enriquecimiento de `orders` con `order_type` y vinculo `rma_request_id`.
  * Modelo `App\Models\RmaResolution` y ampliacion de relaciones en `RmaRequest` y `Order`.
  * Servicio de dominio `App\Services\RevOps\RmaResolutionService`:
    - `inspectAndResolve`: Orquestador de inspeccion con aplicacion inquebrantable de la normativa PROFECO/COFEPRIS.
    - Protocolo sanitario inquebrantable: Si `has_hygiene_seal_intact === true`, se autoriza reingreso a inventario vendible (`ENTRADA_DEVOLUCION_RMA`); si `has_hygiene_seal_intact === false`, se bloquea de forma estricta todo reingreso vendible y se asienta baja como merma sanitaria (`SALIDA_MERMA_RMA`).
    - `issueReplacementOrder`: Genera orden `#ORD-XXXX-RMA` a costo $0.00 MXN en cola de empaque, descuenta variante de reemplazo en Kardex (`SALIDA_REEMPLAZO_RMA`), crea envio `Shipment` y despacha tarea prioritaria `OperationalTask` de almacen.
    - `issueStoreCredit`: Emite cupon digital de un solo uso `CR-RMA-XXXX` en marketing CRM con 90 dias de vigencia y URL tokenizada para checkout directo.
    - `rejectRma`: Formaliza rechazo con fundamentacion regulatoria COFEPRIS para prendas intimas.
    - `whatsapp_payload`: Formateador automatico de plantilla para notificacion al cliente.
  * Controladores y rutas:
    - Web: `PortalLogisticsController::inspectRma` delegando a `RmaResolutionService`.
    - API: `LogisticsApiController::inspectRma`, `resolveRma`, `issueReplacementOrder`, `issueStoreCredit`.
    - Rutas registradas en `routes/api.php` y `routes/web.php`.
* **Frontend:**
  * En [resources/views/portal/logistics.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/logistics.blade.php):
    - Tabla de Garantias & RMA: Columna "Estado & Resolucion" con badges de Orden de Reemplazo (`#ORD-XXXX-RMA`), Cupon de Monedero (`CR-RMA-XXXX ($XXX MXN)`) o Merma Sanitaria PROFECO, ademas de boton de contacto WhatsApp 1 clic.
    - Modal pericial ergonomico `max-w-2xl` con checklist sanitario, conmutador reactivo entre Orden de Reemplazo y Monedero Digital, y preview en tiempo real del mensaje estructurado de WhatsApp.
* **Criterios de Aceptacion Superados:**
  * 5 pruebas unitarias/funcionales en `tests/Feature/RevOps/CrossHubRmaResolutionTest.php` al 100% verde (47 aserciones).
  * Suite global de la plataforma ampliada a 189 pruebas al 100% verde (1,260 aserciones).
  * Cero emojis en toda la base de codigo y vistas.

---

### Slice 4.3: Conciliacion Automatizada de Facturas Courier vs Manifiestos (`TASK-INT-08`) [COMPLETADO]
* **Objetivo:** Detectar cobros excesivos por cubicaje y discrepancias entre las facturas emitidas por paqueterias (DHL, Estafeta, FedEx, Skydropx, 99minutos) y los manifiestos de expedicion de rampa.
* **Backend:**
  * Migracion ejecutada: `2026_09_27_000005_enhance_shipping_reconciliations_tables.php` ampliando `shipping_reconciliations`, `shipping_reconciliation_items` y `shipping_discrepancy_claims` (DDL 3.41).
  * Modelos sincronizados: `ShippingReconciliation`, `ShippingReconciliationItem`, `ShippingDiscrepancyClaim`, `Shipment`.
  * Servicio de Dominio: `App\Services\Finance\ShippingInvoiceAuditorService`:
    - Calculo de peso volumetrico estandar IATA: `(largo_cm * ancho_cm * alto_cm) / 5000`.
    - `auditCarrierInvoiceReport`: cruce algoritmico de facturas courier contra rampa con semaforo (>10% sobreprecio -> `discrepancy` / `SOBREPESO_INDEBIDO`).
    - `parseCsvContent`: normalizador flexible de encabezados (`guia`, `peso_kg`, `costo_facturado`, `pedido`).
    - `fileCarrierClaimDossier`: generacion de expediente formal `CLAIM-COU-YYYYMM-XXXX`, actualizacion de partidas a `claim_filed` y despacho de `OperationalTask` de retencion preventiva en Cuentas por Pagar.
    - `calculateExpectedRate`: formula contractual por carrier y kilogramos facturables.
  * Controladores y rutas:
    - Web: `StorefrontController::portalFinance`, `uploadShippingInvoiceReport`, `fileShippingClaim` con rutas registradas en `routes/web.php`.
    - API: `FinanceApiController::uploadShippingReconciliationReport`, `reconcileShippingAudit`, `fileShippingDiscrepancyClaim` en `routes/api.php`.
* **Frontend:**
  * En [resources/views/portal/finance.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/finance.blade.php):
    - Subnav: Pestaña 6 "Auditoria de Paqueterias" con badge reactivo de discrepancias.
    - Panel 6: 4 tarjetas KPI (Guias Auditadas, Discrepancias / Sobrepeso, Importe Cobrado de Mas, Expedientes de Reclamacion / Retenido en CxP).
    - Formulario de ingesta CSV y boton "Cargar Ejemplo de Prueba".
    - Tabla comparativa de lotes auditados con semaforos de sobrepeso e inspectores de diferencias.
    - Modal de expedientes de reclamacion formal para retencion preventiva en CxP.
* **Criterios de Aceptacion Superados:**
  * 5 pruebas unitarias/funcionales en `tests/Feature/RevOps/CrossHubCarrierAuditTest.php` al 100% verde (34 aserciones).
  * Suite global de la plataforma ampliada a 194 pruebas al 100% verde (1,294 aserciones).
  * Cero emojis en toda la base de codigo y vistas.

---

### Slice 4.4: Cotejo de 3 Vias Inbound, Cuentas por Pagar y Retenciones SAT (`TASK-INT-07`, `TASK-INT-19`, `TASK-INT-20`) [COMPLETADO]
* **Objetivo:** Blindar la tesoreria cruzando la Orden de Compra original con el conteo fisico verificado en rampa y la factura del proveedor antes del pago.
* **Backend:**
  * Migracion ejecutada: `database/migrations/2026_09_27_000006_create_three_way_matchings_and_tax_ledger_tables.php` creando las tablas `three_way_matchings`, `tax_ledger_entries`, `vendor_shipments`, y agregando llaves de bloqueo y enlace en `vendor_bills`.
  * Modelos sincronizados:
    - `App\Models\ThreeWayMatching`: maquina de estados de cotejo (`matched`, `discrepancy_held`, `adjusted_cleared`), relaciones con `PurchaseOrder` y `VendorBill`, metodos de verificacion `isBlocked()`, `isMatched()`.
    - `App\Models\TaxLedgerEntry`: registro granular de retenciones fiscales mexicanas.
    - `App\Models\VendorShipment`: enlace logistico de envios de proveedor a rampa.
    - `App\Models\VendorBill`: enlace a cotejo de 3 vias y bandera booleana `is_spei_blocked`.
  * Servicios de Dominio:
    - `App\Services\Finance\ThreeWayMatchingService`:
      - `evaluateInboundMatch()`: cotejo algoritmico triangular (OC $\leftrightarrow$ Rampa $\leftrightarrow$ Factura Proveedor). Detecta variaciones en unidades aceptables (`warehouse_received_qty - warehouse_damaged_qty`) y precio unitario. Bloquea SPEI (`spei_payout_blocked = true`), calcula nota de debito sugerida y despacha `OperationalTask` (`finance_matching_hold`).
      - `issueDebitNote()`: emision y aplicacion de nota de debito compensatoria, recalculo de importe neto pagable, levantamiento de bloqueo SPEI y cierre de tarea operativa.
      - `authorizeSpeiPayout()`: validacion de inviolabilidad de pago; arroja `DomainException` si existe retencion activa. Al aprobarse, marca la factura como `paid` y genera la referencia SPEI.
      - `syncAllyClearingAsVendorBill()`: centralizacion de saldos de convenios B2B (`TASK-INT-19`) en Cuentas por Pagar.
    - `App\Services\Finance\TaxCalculationEngineService`:
      - `computeOrderTaxes()`: motor fiscal SAT (Base Gravable `PVP / 1.16`, 16% IVA Trasladado, 1% ISR retencion Plataformas Tecnologicas Art. 113-A, 8% IVA retencion Art. 18-J).
      - `getPeriodTaxSummary()`: agregaciones contables de ingresos brutos, IVA neto por pagar al SAT y retenciones acumuladas.
  * Controladores y rutas:
    - Web: `StorefrontController::portalFinance`, `verifyThreeWayMatching`, `issueDebitNoteWeb`, `authorizeSpeiPayoutWeb` registrados en `routes/web.php`.
    - API: `FinanceApiController::getInboundMatchings`, `verifyThreeWayMatching`, `issueDebitNote`, `authorizeSpeiPayout`, `getTaxSummary` registrados en `routes/api.php`.
* **Frontend:**
  * En [resources/views/portal/finance.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/finance.blade.php):
    - Subnav: Pestaña 5 "Cuentas por Pagar & Cotejo 3 Vias" con badge semaforo de discrepancias activas.
    - Motor Fiscal SAT: Tira de desglose de Base Gravable (PVP / 1.16), IVA 16%, Retenciones 1% ISR y 8% IVA, e IVA Neto al SAT.
    - 4 Tarjetas KPI: Inbound Auditados, Cotejos Perfectos (3 Vias OK), Retenciones SPEI Activas, Saldo Retenido en Discrepancia.
    - Formulario de cotejo de 3 vias con precarga de datos de ejemplo (`loadSampleThreeWayMatching`).
    - Tabla sincronizada a 3 columnas: Orden de Compra $\leftrightarrow$ Recepcion Fisica Rampa $\leftrightarrow$ Factura Proveedor con semaforos de estatus.
    - Modales interactivos para emision de Notas de Debito y Autorizacion de Dispersion SPEI.
* **Criterios de Aceptacion Superados:**
  * 7 pruebas unitarias/funcionales en `tests/Feature/RevOps/CrossHubThreeWayMatchingTest.php` al 100% verde (69 aserciones).
  * Suite global de la plataforma ampliada a 201 pruebas al 100% verde (1,363 aserciones).
  * Cero emojis en toda la base de codigo y vistas.

---

### Slice 4.5: Bateria de Pruebas Automatizadas E2E, Verificacion Visual y Cierre de Prioridad 4 [COMPLETADO]
* **Objetivo:** Consolidar la suite de pruebas automatizadas, verificar la navegacion en portal y cerrar el hito con tag de version.
* **Resultados de Ejecucion E2E:**
  * Archivo de pruebas `tests/Feature/RevOps/CrossHubSynergiesTest.php` completado y verificado (6/6 pruebas pasando al 100%, 43 aserciones):
    - `test_flow_1_packaging_consumption_and_rop_procurement_trigger`: validacion de descuento automatico en Kardex de empaque y disparo de alerta ROP a compras ante rebase de umbral de seguridad.
    - `test_flow_2_rma_sanitary_quarantine_prevents_biohazard_restock_and_compensates`: blindaje sanitario COFEPRIS/PROFECO impidiendo el retorno a stock comercial de prendas intimas abiertas, registrando baja como merma sanitaria.
    - `test_flow_3_carrier_overweight_audit_and_cxp_hold_dossier`: conciliacion volumetrica IATA contra facturas courier, generacion de expediente formal de reclamo y retencion preventiva en cuentas por pagar de finanzas.
    - `test_flow_4_inbound_threeway_matching_and_spei_clearing`: cotejo triangular Orden de Compra $\leftrightarrow$ Conteo Rampa $\leftrightarrow$ Factura de Proveedor, con bloqueo automatico estricto de dispersion SPEI ante discrepancias no solventadas.
    - `test_flow_4_part_b_debit_note_unblocks_spei_and_calculates_taxes`: emision y aplicacion de Nota de Debito compensatoria, levantamiento de bloqueo SPEI, calculo de retenciones fiscales mexicanas (Base PVP/1.16, IVA 16%, ISR 1%, IVA retencion 8%) y autorizacion exitosa de dispersion SPEI.
    - `test_full_integrated_cross_hub_workflow_orchestration`: integracion de punta a punta cubriendo abastecimiento, logistica de empaque, auditoria courier y carga limpia de vistas en los portales web de Logistica y Finanzas.
* **Metricas Globales de la Suite:**
  * **207 pruebas automatizadas pasando al 100% (0 fallos, 0 errores, 1,406 aserciones)** en toda la aplicacion Laravel.
  * Auditoria estricta de **cero emojis** confirmada al 100% en todos los archivos de codigo, migraciones, vistas Blade y documentacion.
  * Fusion de la rama `feature/prioridad-4-sinergias-crosshub` a `master` y creacion del tag `v1.4-prioridad-4-sinergias-crosshub`.

---

## 4. Matriz de Control de Dependencias y Modelos Eloquent

| Tarea | Modelos Clave | Tablas Principales | Controladores / Servicios |
| :--- | :--- | :--- | :--- |
| `TASK-INT-13` | `PackageType`, `InventoryMovement`, `Order` | `package_types`, `inventory_movements`, `orders` | `PortalLogisticsController`, `InventoryMovementService` |
| `TASK-INT-09` | `RmaRequest`, `Order`, `InventoryMovement`, `User` | `rma_requests`, `orders`, `inventory_movements`, `store_credits` | `RmaResolutionService`, `PortalLogisticsController` |
| `TASK-INT-08` | `Shipment`, `CarrierInvoiceAudit`, `PackageType` | `shipments`, `carrier_invoice_audits`, `manifests` | `PortalFinanceController`, `CarrierAuditService` |
| `TASK-INT-07` | `PurchaseOrder`, `VendorBill`, `InboundReceipt` | `purchase_orders`, `vendor_bills`, `inbound_receipts` | `ThreeWayMatchingService`, `PortalFinanceController` |
| `TASK-INT-19` | `AllyClearingSettlement`, `VendorBill`, `Ally` | `ally_clearing_settlements`, `vendor_bills`, `allies` | `PortalAlliesController`, `PortalFinanceController` |

---

## 5. Criterio de Exito Global de la Prioridad 4

1. **Automatizacion Real:** La expedicion de pedidos y devoluciones actualizan el Kardex y la contabilidad sin intervencion manual.
2. **Cumplimiento Normativo Sanitario:** Cero prendas intimas devueltas con empaque abierto reingresan al stock vendible.
3. **Control Financiero Auditado:** Imposibilidad de liberar dispersiones SPEI a proveedores o transportistas con discrepancias no compensadas.
4. **Suite 100% Verde:** Minimo 185 pruebas pasando sin fallos ni advertencias en Laravel.
5. **Cero Emojis:** Absoluto cumplimiento de la Regla 1 en todos los archivos modificados.
