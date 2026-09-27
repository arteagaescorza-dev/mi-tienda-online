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

### Slice 4.2: Motor de Resoluciones RMA y Garantias con Aislamiento Sanitario PROFECO (`TASK-INT-09`, `TASK-LOG-04B`)
* **Objetivo:** Automatizar la resolucion de devoluciones dictaminadas en Logistica, emitiendo reemplazos o monedero digital y protegiendo el stock con aislamiento sanitario.
* **Backend:**
  * Modelo `RmaRequest` y servicio `App\Services\RevOps\RmaResolutionService`.
  * Endpoint `POST /api/v1/logistics/rma/{id}/resolve`.
  * Reglas sanitarias inviolables:
    - Si `sanitary_seal_intact === true`: Se autoriza reingreso comercial a `inventories` (`ENTRADA_DEVOLUCION_RMA`).
    - Si `sanitary_seal_intact === false`: Bloqueo estricto; se cataloga como merma destructiva (`SALIDA_MERMA_RMA`), sin permitir reingreso a bodega vendible.
  * Acciones de compensacion:
    - Generacion de orden de reemplazo con prefijo `#ORD-XXXX-RMA` en estado `ready_to_pack`.
    - O emision de credito digital (`store_credits`) vinculado al correo del cliente.
* **Frontend:**
  * Modal interactivo de resolucion en la pestaña "Gestor de Garantias & RMA" de Logistica.
  * Formateador de mensaje estructurado para WhatsApp con folio RMA, resolucion y guia de retorno o codigo de credito.
* **Criterios de Aceptacion:**
  * Pruebas verificando que nunca se incremente inventario vendible si la prenda de ropa interior tiene precinto roto.

---

### Slice 4.3: Conciliacion Automatizada de Facturas Courier vs Manifiestos (`TASK-INT-08`)
* **Objetivo:** Detectar cobros excesivos por cubicaje y discrepancias entre las facturas emitidas por paqueterias (DHL, Estafeta, etc.) y los manifiestos de expedicion de rampa.
* **Backend:**
  * Endpoint `POST /api/v1/finance/shipping-audit/reconcile`.
  * Algoritmo de auditoria:
    - Calculo de peso volumetrico IATA: `(largo_cm * ancho_cm * alto_cm) / 5000`.
    - Comparacion contra el peso facturado por el courier.
    - Categorizacion automatica: `CONCILIADO`, `SOBREPESO_INDEBIDO`, `GUIA_NO_RECONOCIDA`, `TARIFA_EXCEDIDA`.
    - Registro de reclamacion formal para retencion de pago.
* **Frontend:**
  * En [resources/views/portal/finance.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/finance.blade.php) (Pestaña "Auditoria de Paqueterias"):
    - Carga de archivo CSV o dataset de guia facturada.
    - Tabla comparativa con badges de semaforo de discrepancia.
    - Boton "Generar Expediente de Reclamacion" que exporta el reporte para retencion a la paqueteria.
* **Criterios de Aceptacion:**
  * Pruebas comprobando la deteccion de sobrepesos y calculo exacto del importe reclamable.

---

### Slice 4.4: Cotejo de 3 Vias Inbound, Cuentas por Pagar y Retenciones SAT (`TASK-INT-07`, `TASK-INT-19`, `TASK-INT-20`)
* **Objetivo:** Blindar la tesoreria cruzando la Orden de Compra original con el conteo fisico verificado en rampa y la factura del proveedor antes del pago.
* **Backend:**
  * Endpoint `POST /api/v1/finance/three-way-matching/verify`.
  * Logica de cotejo de 3 vias:
    - OC original (`PurchaseOrder`) $\leftrightarrow$ Recepcion fisica en rampa (`WarehouseInboundReceipt`) $\leftrightarrow$ Factura proveedor (`VendorBill`).
    - Si existe discrepancia (faltantes o sobrecostos): emision automatica de Nota de Debito y ajuste al saldo de `vendor_bills`.
    - Aplicacion de retenciones fiscales mexicanas (IVA 16% trasladado vs acreditable, retencion ISR).
  * Integracion de liquidaciones B2B de convenios (`AllyClearingSettlement`) como cuentas por pagar centralizadas (`TASK-INT-19`).
* **Frontend:**
  * En [resources/views/portal/finance.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/finance.blade.php) (Pestaña "Cuentas por Pagar & SPEI"):
    - Visualizador de cotejo a 3 columnas con lineas sincronizadas.
    - Indicador de estado del cotejo: *Cotejo Perfecto (3 Vias OK)*, *Discrepancia en Cantidad*, *Discrepancia en Precio*.
    - Boton "Autorizar Dispersion SPEI" habilitado unicamente con cotejo solventado.
* **Criterios de Aceptacion:**
  * Pruebas validando que no se pueda autorizar un pago si existen discrepancias sin nota de debito compensatoria.

---

### Slice 4.5: Bateria de Pruebas Automatizadas E2E, Verificacion Visual y Cierre de Prioridad 4
* **Objetivo:** Consolidar la suite de pruebas automatizadas, verificar la navegacion en navegador y cerrar el hito con tag de version.
* **Alcance:**
  * Archivo de pruebas `tests/Feature/RevOps/CrossHubSynergiesTest.php` cubriendo los 4 flujos transversales.
  * Verificacion de cero regresiones en la suite global ($\ge 185$ pruebas pasando al 100%).
  * Revision estricta de cero emojis en codigo, vistas, comentarios y documentacion.
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
