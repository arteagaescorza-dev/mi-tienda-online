# Plan de Implementación: Módulo de Compras (Hub 4 - Inventario & Cadena de Suministro)

## 1. Visión General
Reactivar, unificar e integrar completamente la suite de **Compras** dentro de **Hub 4 (Inventario & Cadena de Suministro)** en `resources/views/portal/inventory.blade.php`, conectando sus 4 sub-pestañas operativas, sus 6 modales ejecutivos y de emisión mercantil, la generación e impresión de órdenes de compra (PO) en PDF oficial y la sincronización con el backend Laravel (`InventoryApiController` y `StorefrontController`).

---

## 2. Alcance y Especificación de Vistas

### 2.1. Selector Principal de Navegación en Hub 4
- Habilitar en el encabezado de navegación la pestaña:
  - `Catálogo Operativo`
  - `Proveedores & Scraping`
  - `Compras (X)` *(Pestaña Activa con badge reactivo de órdenes/lotes pendientes)*

---

### 2.2. Sub-pestañas Operativas de Compras

#### Sub-pestaña 1: Planeación de Compras (Bandeja MOQ)
- **KPI Cards Superiores:**
  - *Órdenes en Tránsito:* Conteo total (desglose "en camino" y "por confirmar").
  - *Lotes Planificados:* Conteo de lotes en cola acumulando volumen hacia MOQ.
  - *Inversión Estimada:* Monto calculado a costo unitario neto en MXN.
  - *Lead Time Promedio:* Tiempo promedio de entrega en días.
- **Acciones y Vistas de Cabecera:**
  - Selector de vista: `[Por Proveedor (Árbol)]` vs `[Lista Plana]`.
  - Acciones maestras: `[Generar PO Oficial (PDF)]` y `[Exportar WhatsApp]`.
- **Estructura del Árbol por Fabricante / Proveedor:**
  - Encabezado con datos del taller: nombre, lead time, contacto y términos de pago (ej. *Crédito 60 días*).
  - Barra de progreso reactiva del Pedido Mínimo (`Progreso MOQ: X% (uds acumuladas / meta MOQ)`).
  - Subtotal acumulado del fabricante y botón de acción `[Generar PO (PDF)]`.
  - **Tabla de Lotes:**
    - Folio lote (`FP-YYYY-XX`).
    - Producto & SKU.
    - Unidades con input editable directo.
    - Curva de tallas editable con botón `[Editar]` para abrir el modal de distribución.
    - Costo unitario neto editable.
    - Subtotal en tiempo real (`Unidades × Costo`).
    - Prioridad editable (`ALTA`, `MEDIA`, `BAJA`).
    - Acción `[Quitar]`.

#### Sub-pestaña 2: Órdenes Emitidas & Seguimiento Fletero (Inbound Tracking)
- **KPI Cards Superiores:**
  - *Órdenes en Tránsito:* Conteo de POs activas con transportista.
  - *Piezas en Embarque:* Unidades físicas comprometidas.
  - *Inversión Inbound:* Monto total registrado en Cuentas por Pagar (Hub 5).
  - *Muelle de Descarga:* Ubicación física en CEDIS (*ej. Rampa 02 Inbound - CEDIS Patio Naucalpan*).
- **Tabla de Telemetría Inbound:**
  - Folio oficial de la orden (`OC-YYYY-XXX`).
  - Proveedor, transportista (*FedEx Express, DHL Express, Estafeta*) y código de guía con botón para copiar.
  - Prendas, cantidad y desglose de curvas de tallas (con badge `Pedido Mixto` si aplica).
  - Total facturado ($ MXN).
  - Fecha de emisión y Fecha Estimada de Arribo (ETA).
  - Estado del flete (*En Camino*, *Pendiente*, *Recibido en Rampa*).
  - Acciones: `[Reimprimir / Ver PDF]` y `[Rastrear Guía]`.

#### Sub-pestaña 3: Solicitudes On-Demand (Carritos de Clientes)
- **Filtros por Estado:** `Todas`, `Por Verificar`, `En Compra`, `Agotadas`.
- **Temporizador SLA:** Contador regresivo con objetivo estricto (< 2 horas).
- **Tabla de Solicitudes:**
  - Folio de solicitud (`REQ-YYYY-XXX`).
  - Cliente y teléfono de contacto directo.
  - Origen del carrito (*Carrito Solicitud Directa*, *Carrito Mixto Línea On-Demand*).
  - Prenda, talla y piezas solicitadas.
  - Taller/Proveedor asignado.
  - Tiempo SLA transcurrido.
  - Estado de verificación (*Por Verificar*, *En Compra Planificada*, *Agotado*).
  - Acciones rápidas: `[+ A Compras]` (empuja a Bandeja MOQ), `[Agotado]`, `[WhatsApp]`.

#### Sub-pestaña 4: Kardex de Auditoría de Compras
- **Buscador & Filtros:** Búsqueda predictiva por folio, SKU o taller; selector por tipo de evento.
- **Tabla de Bitácora Inmutable:**
  - Folio del movimiento, fecha y hora con enlace a reimpresión de PO.
  - Badge de evento: *Emisión de PO*, *On-Demand a PO*, *Ajuste de Unidades*, *Recepción en Rampa*.
  - Proveedor/Taller involucrado.
  - Referencia de producto y SKU.
  - Variación de unidades (+ / -).
  - Monto económico afectado.
  - Operador responsable (*ej. Elena Compras, Carlos Almacén*).
  - Detalle y notas de auditoría.

---

## 3. Especificación de Modales y Documentos Oficiales

### Modal 1: Expediente Formal de Orden de Compra (PO)
- **Identidad Corporativa:** Membrete oficial de *Mi Tienda Online S.A. de C.V.*, RFC, domicilio fiscal y contacto.
- **Folio y Estado:** Folio oficial (`PO-YYYY-XXXX`), fecha de emisión y estado (`Emitida / Aprobada`).
- **Módulo de Compensaciones Previas:**
  - Auditoría de notas de crédito pendientes por faltantes en muelle o defectos de entregas anteriores.
  - Checkbox para aplicar deducción automática al saldo de la orden.
- **Bloques Bilaterales:**
  - Datos de fabricante/proveedor y términos de pago acordados.
  - Condiciones de entrega en muelle, horarios y requerimiento obligatorio de remisión física para cotejo ciego.
- **Tabla de Partidas Textil:** SKU, descripción, desglose por curva de tallas, unidades, costo unitario pactado y subtotales.

### Modal 2: Plantilla Imprimible y Generación de PDF Oficial
- Plantilla optimizada a 1 página estandarizada.
- Cláusulas comerciales y de aceptación:
  1. Cotejo a ciegas en rampa de almacén.
  2. Tratamiento de mermas textiles y notas de crédito.
  3. Confirmación obligatoria de guía fletera previa a recolección.
- Resumen contable: Total piezas, Subtotal gravable neto, IVA trasladado (16%) y Total de la Orden de Compra.
- Firmas duales:
  - *Coordinación de Abastecimiento RevOps (Elena Compras)*.
  - *Acuse de Recibo y Aceptación de Taller / Fabricante*.

### Modal 3: Despacho de Orden de Compra por Correo
- Formulario de transmisión formal al taller:
  - Destinatario principal (correo del taller).
  - Con copia (CC) a compras y logística.
  - Asunto mercantil estandarizado.
  - Cuerpo del mensaje con instrucciones de confirmación de ETA y solicitud de guía.
  - Tarjeta de archivo adjunto generado (`Orden_de_Compra_PO-XXXX.pdf`) con estado de vinculación a Extranet B2B.
  - Botón de envío: `Confirmar y Enviar Correo al Fabricante`.

### Modales Auxiliares
- **Modal de Curva de Tallas:** Ajuste dinámico de cantidades por talla (*CH, M, G, XL, 2XL*) validando el balance de unidades totales del lote.
- **Modal de Asignación de Guía Fletera:** Captura de paquetería, guía y fecha compromiso (ETA) al marcar orden en camino.
- **Generador de Mensaje WhatsApp:** Texto limpio estructurado con unidades y tallas para envío rápido por chat al taller.

---

## 4. Fases de Ejecución

1. **Fase 1: Estructura Alpine.js y Sub-navegación:**
   - Habilitar `activeMainTab = 'compras'` en `inventory.blade.php`.
   - Implementar el submenú horizontal de 4 pestañas (`moq`, `tracking`, `ondemand`, `kardex`).
2. **Fase 2: Vistas Operativas:**
   - Construir las 4 sub-pestañas con sus KPI cards, tablas y estados interactivos.
3. **Fase 3: Modales y Motor de PDF:**
   - Implementar el expediente formal de PO, la plantilla imprimible a 1 página y el modal de despacho por correo.
   - Implementar los modales de edición de curva de tallas y asignación de guía fletera.
4. **Fase 4: Backend e Integración:**
   - Vincular endpoints existentes de `InventoryApiController` (`getPurchaseOrders`, `storePurchaseOrder`, `dispatchPurchaseOrder`, `receivePurchaseOrder`, `getPurchaseKardex`).
   - Sincronizar payload del controlador `StorefrontController.php`.
5. **Fase 5: Pruebas y Validación:**
   - Ejecución de la suite completa de pruebas con PHPUnit para garantizar 100% de aserciones en verde y cero regresiones.

