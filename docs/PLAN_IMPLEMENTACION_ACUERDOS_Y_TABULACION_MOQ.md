# PLAN DE IMPLEMENTACIÓN TÉCNICA: ACUERDOS POR DISCREPANCIAS Y TABULACIÓN DINÁMICA DE MOQs (HUB 4)

**Identificador de Tarea:** `TASK-PUR-02`  
**Módulo:** Hub 4 - Inventario & Compras RevOps (`resources/views/portal/inventory.blade.php`, `public/js/revops-panel/inventory-module.js`)  
**Fecha:** Octubre 2026  
**Estatus:** APROBADO PARA EJECUCIÓN INMEDIATA  

---

## 1. Contexto y Nuevas Reglas de Negocio

El módulo de compras en Hub 4 se rige bajo dos nuevas reglas operativas formalizadas:

1. **Regla 1 (Pestaña Extra de Acuerdos por Discrepancias):**  
   El submódulo de compras debe contener una 5ª pestaña (`purchasesSubTab === 'agreements'`) donde residan de forma visible, auditable y editable todos los acuerdos mercantiles y notas de crédito derivados de discrepancias inbound (faltantes, mermas textiles, daños o penalizaciones logísticas). Las modificaciones deben ser editables generando trazabilidad inmutable y referenciación en el `PurchaseKardex`.

2. **Regla 2 (Tabulación Dinámica de MOQs por Proveedor en Árbol):**  
   Las órdenes de compra agrupadas por proveedor en vista de árbol deben tabular sus costos unitarios y metas de volumen directamente con los **Tabuladores de MOQ** dados de alta en el módulo de ajustes (`SupplierMoqSheet`). Al cambiar las cantidades de las prendas, el sistema debe resolver en tiempo real el tier alcanzado (Menudeo, Mayoreo 50+, etc.) y ajustar los costos unitarios tabulados de cada renglón.

---

## 2. Alcance de Implementación

### Componente A: Sub-Pestaña 5 - Directorio de Acuerdos por Discrepancias & Notas de Crédito
1. **Sub-navbar de Compras:**
   - Añadir el quinto selector de submódulo:
     - `1. Planeación MOQ`
     - `2. Seguimiento Fletero`
     - `3. Solicitudes On-Demand`
     - `4. Acuerdos & Notas de Crédito` (Nueva Pestaña)
     - `5. Kardex de Auditoría`
   - Contador dinámico de acuerdos activos/pendientes.

2. **Bandeja de Visualización & Filtros:**
   - Buscador por folio de referencia (`NC-TORZO-2026-001`, `NC-SILK-...`).
   - Filtro por Proveedor (Todos, Torzo B2B, Silk & Cotton, etc.).
   - Filtro por Estatus (`Todos`, `Pendiente de Aplicar`, `Compensada en PO`, `Anulada`).
   - Tarjetas KPI superiores:
     - Total Saldo a Favor Disponible ($ MXN).
     - Acuerdos Pendientes por Conciliar.
     - Acuerdos Compensados en Órdenes de Compra.
     - Total Unidades en Discrepancia (Faltantes / Dañadas).

3. **Tabla Cronológica de Acuerdos:**
   - Columnas: Folio Referencia, Proveedor, Causa / Tipo de Discrepancia, Piezas Afectadas (Faltantes / Dañadas), Importe Compensatorio ($ MXN), Estado (Badge interactivo), Aplicado en (PO-YYYY-XXXX o Pendiente), Fecha de Acta, Acciones (Editar, Detalle, Anular).

4. **Modal / Formulario de Alta y Edición de Acuerdo con Trazabilidad:**
   - Campos: Proveedor, Folio (autogenerado con prefijo o manual), Tipo de Discrepancia, Unidades Faltantes, Unidades Dañadas, Costo Unitario Base, Importe Líquido, Contacto que autoriza en taller, Minuta detallada / Causa de la resolución.
   - Modo Edición: Permite modificar importes o justificaciones de acuerdos pendientes.
   - Auditoría Automática: Cada guardado o edición dispara un asiento inmutable en `PurchaseKardex` con los folios involucrados y el delta del ajuste.

---

### Componente B: Tabulación Dinámica de MOQs por Proveedor en Árbol
1. **Conexión Reactiva con Tabuladores de Ajustes (`SupplierMoqSheet`):**
   - En `getPlannedOrdersBySupplier()` en Alpine.js:
     - Identificar el tabulador activo y predeterminado (`is_default === true` o coincidencia de proveedor) desde `moqSheets`.
     - Sumar las unidades totales del proveedor (`totalQty`).
     - Determinar el **Tier Activo** según los escalones configurados en el tabulador (ej. Escala 1: Menudeo, Escala 2: 50 prendas, Escala 3: 100 prendas).
     - Actualizar la meta de la barra de progreso MOQ del proveedor con el `min_qty` del tier meta siguiente.
2. **Cálculo Automático de Costos por Categoría/Prenda:**
   - Para cada producto en la lista del proveedor (ej. "Tanga Licra Roja", "Bikini..."):
     - Buscar la fila en `sheet.rows` cuya prenda/categoría coincida (ej. "Tanga").
     - Extraer el precio del tier alcanzado (ej. si `totalQty >= 50`, costo = $80 MXN; si `totalQty < 50`, costo = $180 MXN).
     - Si el usuario no ha fijado un costo manual bloqueado, aplicar el costo tabulado automáticamente.
     - Calcular `subtotal = qty * costUnit`.
3. **Indicador Visual de Tabulación:**
   - Mostrar badge en la fila: `[Tabulado MOQ: $80.00 MXN | Escala 50+ (-56%)]`.
   - Subtotal del fabricante y total de piezas sincronizados con la barra superior de progreso.

---

### Componente C: Capa Backend y Rutas API
1. **Controlador `InventoryApiController`:**
   - Actualizar `getCreditAgreements`:
     - Retornar acuerdos según filtro de estatus opcional o todos por defecto si se solicita en la bandeja de gestión.
   - Agregar método `updateCreditAgreement(Request $request, int $id)`:
     - Validar datos modificados.
     - Comprobar que no esté ya aplicado en una PO cerrada.
     - Registrar movimiento en `PurchaseKardex`: `DISCREPANCY_UPDATED` con detalle del cambio.
2. **Rutas en `routes/api.php`:**
   - `PUT /api/inventory/purchases/credit-agreements/{id}` &rarr; `InventoryApiController@updateCreditAgreement`.
3. **Controlador `StorefrontController@portalInventory`:**
   - Pasar la colección completa de `portalCreditAgreements` (sin limitar a `status = 'pending'`) para alimentar la bitácora histórica completa en el navegador.

---

## 3. Plan de Verificación & Pruebas
1. **Prueba Unitaria / Feature PHPUnit:**
   - `SupplierCreditAgreementTest` / `InventoryHub4ModuleTest`:
     - Test de listado con todos los estatus.
     - Test de actualización de acuerdo y verificación de creación de asiento en `PurchaseKardex`.
     - Test de restricción: no editar acuerdos con estatus `applied`.
2. **Prueba E2E en Navegador / Lógica Alpine.js:**
   - Verificar la sub-pestaña `purchasesSubTab === 'agreements'`.
   - Modificar las cantidades de un producto en el árbol de Torzo y verificar que el costo unitario cambie automáticamente según los tabuladores de ajustes.
3. **Control de Calidad:**
   - Zero errores de sintaxis en Blade y JS.
   - Versionado de script a `inventory-module.js?v=20261007_v5`.
