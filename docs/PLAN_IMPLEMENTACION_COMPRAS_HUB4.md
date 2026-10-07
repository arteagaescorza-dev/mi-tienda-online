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

---

## 5. Gobernanza y Reglas de Negocio por Ciclos Operativos (Canon Regla 10 & Regla 12)

### 5.1. Matriz de Cobertura de Ciclos Operativos en Compras & Abastecimiento

| Ciclo Operativo | Alcance del Proceso | Estado | Reglas Formalizadas | Brechas Identificadas / Por Reglar |
| :--- | :--- | :--- | :--- | :--- |
| **Ciclo 1: Catálogo e Ingesta de Producto** | Curaduría 4:5 WebP, fidelidad textil con BD, taxonomías in situ y borradores | **Completado** | 12.1.1 &ndash; 12.1.6 | Ninguna. |
| **Ciclo 2: Gobernanza de Proveedores & Marcas** | Árbol Proveedor &rarr; Marcas hijas y tabuladores MOQ exclusivos independientes | **Completado** | 12.2.1 &ndash; 12.2.2 | Pendiente cálculo periódico de SLA por incumplimiento de entrega. |
| **Ciclo 3: Planeación de Compras & MOQ** | Árbol consolidado de compras, reactividad en caliente a tabuladores y fallback | **Completado** | 12.3.1 &ndash; 12.3.5 | Pendiente algoritmo de previsión estacional por DOH de inventario. |
| **Ciclo 4: Discrepancias & Acuerdos Mercantiles**| Pestaña de acuerdos, trazabilidad de mermas/faltantes y compensación en PO | **Completado** | 12.4.1 &ndash; 12.4.3 | Pendiente conciliación automatizada con CFDI de egreso SAT del taller. |
| **Ciclo 5: Expedición de PO & Kardex** | Snapshot inmutable en PDF, despacho electrónico por email y Kardex ACID | **Completado** | 12.5.1 &ndash; 12.5.3 | Pendiente portal extranet para acuse de fecha de recolección en taller. |
| **Ciclo 6: Recepción Física en Rampa / Muelle** | Inspección ciega, cotejo de bultos y generación de actas de discrepancia | *En Proceso* | En diseño normativo | Requiere reglamentar lectura de código de barras y acta de hechos. |
| **Ciclo 7: Liquidación Contable (AP / SAT)** | Cotejo de 3 vías (PO &harr; Remisión &harr; Factura XML 4.0) | *En Proceso* | En diseño normativo | Requiere formalizar circuito con Hub 5 (Finanzas y SAT). |

### 5.2. Reglas Clave Implementadas
1. **Árbol Consolidado Proveedor-Marcas (Regla 12.3.1):** En la Bandeja MOQ, todas las marcas filiales de un proveedor (ej. Torzo con Torzo Sexywear y Mazho; Dajo B2B con Dajo Underwear) se unifican en un único árbol de pedido, consolidando el volumen para alcanzar el MOQ del fabricante y emitiendo una sola Orden de Compra (PO).
2. **Reactividad Instantánea a Tabuladores MOQ (Regla 12.3.2):** La Bandeja MOQ actualiza en caliente los porcentajes de avance, escalas activas y costos unitarios siempre que se dé de alta, edite o elimine un tabulador en Ajustes, sin recargar el navegador.
3. **Tabulación Dinámica por Salto de Escala (Regla 12.3.3):** Al sobrepasar el umbral de una escala de volumen superior, el costo unitario de todas las partidas del fabricante se recalcula reactivamente según la matriz MOQ.
4. **Fallback Categórico Tolerante (Regla 12.3.4):** Si la prenda no coincide con una subcategoría específica, adopta la fila "General" o la fila única del tabulador del fabricante para garantizar costo tabulado continuo.
5. **Pestaña Permanente de Acuerdos Mercantiles y Notas de Crédito (Regla 12.4.1):** Sub-pestaña dedicada en Compras para auditar, consultar y editar convenios originados por incidencias físicas en rampa.
6. **Compensación Contable en PO (Regla 12.4.3):** Selector de acuerdos pendientes en la emisión de PO que deduce automáticamente el saldo a favor del total facturado e imprime la Cláusula 4 legal en el documento PDF.
7. **Erradicación Total de Datos Falsos (Regla 12.3.5):** El submódulo de Compras opera al 100% con datos reales persistidos en MySQL, erradicando arreglos mock de pruebas.

