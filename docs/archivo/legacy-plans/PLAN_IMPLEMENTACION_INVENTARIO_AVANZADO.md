# Plan Maestro de Implementacion: Tareas Pendientes de Inventario Avanzado & Ficha SKU
## Plataforma RevOps E-Commerce Multiverso & Suscripciones (Mi Tienda Online) - Hub 4

> **Contexto de Arquitectura:**  
> Este documento rige la implementacion fullstack de las tareas pendientes de inventario en la rama `feature/inventario-avanzado-catalogo-sku`. Cubre de punta a punta la configuracion dinamica de tallas y volumenes (`TASK-INV-14`), el alta ergonomica de producto a pantalla completa con auto-SKU, copiloto IA y deteccion de duplicados (`TASK-INV-15`), y el modal panoramico centrado de edicion de SKU (`TASK-INV-18`) con reactividad en tiempo real de existencias, margenes y CPP.  
> **Referencias Normativas:** [MASTER_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/MASTER_ROADMAP.md), [PENDING_TASKS.md](file:///c:/laragon/www/mi-tienda-online/docs/PENDING_TASKS.md), [BACKEND_INTEGRATION_SPEC.md](file:///c:/laragon/www/mi-tienda-online/docs/BACKEND_INTEGRATION_SPEC.md) e [INVENTORY_ROADMAP.md](file:///c:/laragon/www/mi-tienda-online/docs/INVENTORY_ROADMAP.md).  
> **Regla de Oro:** Se prohibe terminantemente el uso de emoticones o emojis en codigo, vistas, comentarios, mensajes de commit y documentacion (Regla 1). La carpeta `import/` permanece estrictamente de solo lectura.

---

## 1. Vision Estrategica y Diagnostico Operativo

El Hub 4 (Inventario y Cadena de Suministro) ya cuenta con una solida infraestructura de base de datos (Kardex inmutable, lotes sanitarios, slotting fisico, calculo de CPP, recetas BOM y curaduria de scraping). No obstante, la experiencia de alta, edicion y modelado de variantes aun presenta tres cuellos de botella criticos reportados en el backlog:

1. **Rigidez en Curvas de Tallas y Presentaciones (`TASK-INV-14`):**
   - La curva de tallas textiles permanece fija en codigo (`CH, M, G, XL, 2XL, 3XL`), impidiendo registrar tallas especiales (`XS`, `4XL`, `5XL`), tallas numericas de cintura (`28, 30, 32, 34...`), copas de lenceria (`32B, 34C...`) o articulos `Unitalla`.
   - En perfumeria y cosmetica liquida, los volumenes estan limitados a `10ml, 50ml, 100ml`, impidiendo formatos comerciales comunes (`15ml, 30ml, 75ml, 120ml, 200ml`).
   - *Solucion:* Crear un gestor dinamico de plantillas de curvas de tallas (`apparel_size_curves`, `apparel_sizes`) y formatos de volumen (`fragrance_volumes`) accesible desde Ajustes y desde el formulario de producto.

2. **Captura Manual de SKU, Falta de Copiloto y Riesgo de Duplicados (`TASK-INV-15`):**
   - El sistema exige capturar el SKU a mano y bloquea con error si se omite, en lugar de sugerir una nomenclatura estandarizada por categoria, marca y variante.
   - La redaccion de fichas de producto no aprovecha el contexto de telas, silueta y swatches para inferir descripciones comerciales y etiquetas SEO ocultas para la tienda D2C.
   - No existe un validador preventivo que coteje si un producto que se intenta dar de alta ya existe en el catalogo (por similitud de nombre, marca o atributos).
   - El drawer lateral actual comprime los campos provocando scroll innecesario.
   - *Solucion:* Rediseñar el drawer a ancho completo (`w-full` / `max-w-full`), integrar generador automatico de SKU inteligente con bloqueo opcional, copiloto IA para descripcion y tags SEO, y detector de duplicados con modal interactivo de resolucion.

3. **Compresion y Falta de Reactividad en la Edicion de SKU (`TASK-INV-18`):**
   - Al pulsar un SKU en el catalogo se abre un panel lateral estrecho (`max-w-4xl`) donde las tarjetas de existencias no reaccionan al editar cantidades, la calculadora de margen ignora el descuento porcentual, faltan tallas extendidas y el guardado intercepta con modales de compra no solicitados.
   - *Solucion:* Reemplazar el drawer lateral por un **Modal Panoramico Centrado (`max-w-7xl` / 95vw)** en 3 columnas ergonómicas con reactividad instantanea en KPIs de stock, calculadora dinamica de PVP efectivo / margen / CPP, y guardado directo con toast de confirmacion.

---

## 2. Matriz Comparativa: Estado Actual vs Estado Objetivo

| Componente | Estado Actual | Estado Objetivo (Fase Inventario Avanzado) |
| :--- | :--- | :--- |
| **Curvas de Tallas Textiles** | Lista estatica en JavaScript (`CH-3XL`). | Tablas `apparel_size_curves` y `apparel_sizes` con plantillas predefinidas (Americana, Numerica, Copas, Unitalla) y CRUD interactivo. |
| **Formatos de Perfumeria** | Fijos en `10ml, 50ml, 100ml`. | Tabla `fragrance_volumes` con administracion de capacidades, unidades de medida y nombres comerciales. |
| **Generador de SKU** | Captura manual propensa a error humano. | Servicio `SkuGeneratorService` con nomenclatura categorizada (`TXT-`, `CSM-`, `FRG-`, `ACC-` + Marca + Consecutivo + Variante). |
| **Copiloto IA de Producto** | Redaccion manual sin optimizacion SEO. | Endpoint `/api/v1/inventory/products/generate-description` que compila atributos tecnicos y sugiere tags de busqueda para el Storefront. |
| **Deteccion de Duplicados** | Inexistente (se detecta solo por colision de SKU). | Algoritmo de similitud (`DuplicateDetectionService`) con modal de validacion comparativa antes de guardar. |
| **Drawer de Alta de Producto** | Lateral comprimido (`max-w-4xl`) con scroll. | Layout panorámico full-width de 3 paneles balanceados. |
| **Modal de Edicion de SKU** | Drawer estrecho con inputs no reactivos y guardado obstructivo. | Modal centrado `max-w-7xl` a 3 columnas con reactividad completa en stock/margenes y guardado directo atómico. |

---

## 3. Estandares Transversales de Diseno y Reglas Maestras Aplicadas

1. **Regla 1 (Cero Emojis):** Exclusion estricta de cualquier emoji en codigo PHP, vistas Blade, scripts JS, migraciones, base de datos y commits.
2. **Regla 5 (Cero Cabeceras Redundantes):** Titulos limpios e integrados al navbar o barras superiores; inicio directo en el contenido util.
3. **Regla 8 (Tooltips de Seccion con '?'):** Iconos de interrogacion contextual `.section-help-btn` en matrices de variantes, calculadoras de margen y reglas de SKU.
4. **Regla 10 (Jerarquia Estructural Inamovible):**
   * *1er Nivel:* Subnav y conmutadores de modulo.
   * *2do Nivel:* Tarjetas KPI reactivas (Existencias fisicas, apartadas, disponibles, margen proyectado).
   * *3er Nivel:* Matrices interactivas de variantes, tablas y formularios de accion.
5. **Regla 15 (Ergonomia Visual y Amplitud):** Erradicar dialogos comprimidos mediante modales panoramicos (`max-w-7xl`) y drawers full-width organizados en columnas claras.

---

## 4. Desglose de Vertical Slices de Implementacion

### Slice 1: Gestor Configurable de Tallas Textiles y Volúmenes de Perfumería (`TASK-INV-14`)
* **Objetivo:** Permitir la creacion, edicion y uso flexible de plantillas de tallas textiles y presentaciones de volumen para cosmetica y perfumeria.
* **Backend:**
  * Migracion y Modelos Eloquent:
    - `apparel_size_curves`: Identificador de plantilla (`name`, `slug`, `category_scope`, `description`, `is_default`, `is_active`).
    - `apparel_sizes`: Elementos individuales (`curve_id`, `code`, `label`, `sort_order`, `waist_min_cm`, `waist_max_cm`, `is_active`).
    - `fragrance_volumes`: Formatos de perfumeria (`value`, `unit`, `label`, `sort_order`, `is_active`).
  * Seeders / Inicializadores: Plantillas estandar (Curva Americana `XS-4XL`, Curva Numerica `28-40`, Curva Lenceria Copas `32B-38D`, Unitalla) y volumenes estandar (`10ml, 15ml, 30ml, 50ml, 75ml, 100ml, 120ml, 200ml`).
  * Servicio de Dominio: `VariantDimensionService` para gestionar curvas activas y formatear matrices.
  * Endpoints RESTful:
    - `GET /api/v1/inventory/settings/size-curves`
    - `POST /api/v1/inventory/settings/size-curves`
    - `PUT /api/v1/inventory/settings/size-curves/{id}`
    - `DELETE /api/v1/inventory/settings/size-curves/{id}`
    - `GET /api/v1/inventory/settings/fragrance-volumes`
    - `POST /api/v1/inventory/settings/fragrance-volumes`
    - `PUT /api/v1/inventory/settings/fragrance-volumes/{id}`
    - `DELETE /api/v1/inventory/settings/fragrance-volumes/{id}`
* **Frontend:**
  * Modales interactivos de gestion en Ajustes de Inventario: `sizesManagerModal` y `volumesManagerModal`.
  * Selector reactivo de plantilla de tallas dentro del modal de alta de producto que puebla instantaneamente la matriz de variantes.
* **Pruebas Automatizadas:** `tests/Feature/Inventory/VariantDimensionsTest.php` cubriendo validacion de esquemas, CRUD de curvas y asignacion a matrices.

---

### Slice 2: Motor Inteligente de Auto-SKU y Detección de Duplicados (`TASK-INV-15` - Fase Backend)
* **Objetivo:** Automatizar la generacion de codigos SKU con nomenclatura empresarial y evitar la creacion de productos duplicados en el catalogo.
* **Backend:**
  * Servicio `SkuGeneratorService`:
    - Algoritmo estructurado: `[PREFIJO_CATEGORIA]-[SIGLAS_MARCA]-[CONSECUTIVO_4DIGITOS]-[CODIGO_VARIANTE]`.
    - Deteccion de colisiones en base de datos e incremento secuencial atómico.
    - Soporte para SKU padre (estilo) y SKUs hijos por variante (talla/tono/volumen).
  * Servicio `DuplicateDetectionService`:
    - Evaluacion cruzada: Similitud textual (Levenshtein / Soundex de nombre), coincidencia de marca/proveedor, composicion de tela o volumen.
    - Determinacion de score de similitud (0 a 100%) y clasificacion de riesgo (`none`, `warning`, `duplicate_match`).
  * Endpoints RESTful:
    - `POST /api/v1/inventory/products/generate-sku` (recibe categoria, marca, nombre y variante opcional).
    - `POST /api/v1/inventory/products/check-duplicate` (recibe datos del borrador y retorna coincidencias con porcentaje de similitud).
* **Pruebas Automatizadas:** `tests/Feature/Inventory/SkuAndDuplicateEngineTest.php` validando la unicosidad de SKUs, algoritmos de consecutivo y deteccion de similitud.

---

### Slice 3: Drawer de Producto Full-Width con Copiloto IA de Descripción y Tags SEO (`TASK-INV-15` - Fase Frontend)
* **Objetivo:** Desplegar una experiencia ergonomica a pantalla completa para el alta de productos, integrando asistencia de IA y resolucion visual de duplicados.
* **Backend:**
  * Servicio `ProductAiCopilotService`:
    - Ensamble de prompt con atributos tecnicos: categoria, marca, telas (`apparel_fabrics`), colores (`apparel_colors`), silueta y publico objetivo.
    - Generacion de titulo optimizado, descripcion comercial persuasiva (2 párrafos), ficha de especificaciones tecnicas y lista de tags invisibles para el motor de busqueda D2C.
  * Endpoint RESTful:
    - `POST /api/v1/inventory/products/generate-description`
* **Frontend:**
  * Rediseño del Drawer de Producto:
    - Modal / Drawer ampliado a Full-Width (`w-full` o selector conmutador a pantalla completa) estructurado en 3 paneles:
      1. *Identidad, Marca y Categoria* (con generador auto-SKU interactivo, candado de edicion manual y boton para regenerar).
      2. *Atributos Textiles & Cosméticos* (con selectores de telas, colores tornasol, plantilla de curva de tallas y swatches).
      3. *Contenido Comercial & Copiloto IA* (boton "Generar Descripcion con IA", preview en vivo y tags SEO).
  * Modal comparativo de duplicados (`duplicateAlertModal`):
    - Si la deteccion arroja coincidencia superior al umbral, despliega comparador lado a lado con 3 opciones: `Continuar como Producto Nuevo`, `Sumar Existencias al Existente` o `Descartar Borrador`.
* **Pruebas Automatizadas:** `tests/Feature/Inventory/ProductCreationDrawerTest.php` verificando la integracion del copiloto y el flujo de guardado.

---

### Slice 4: Modal Panorámico Centrado de Detalle y Edición de SKU (`max-w-7xl`) & Reactividad Integral (`TASK-INV-18`)
* **Objetivo:** Erradicar el drawer lateral comprimido de edicion de SKU y sustituirlo por un modal panoramico centrado de 3 columnas con reactividad completa en vivo y guardado directo.
* **Frontend (Blade & JS):**
  * Modal Centrado Panoramico `#skuPanoramicModal` (`max-w-7xl` / 95vw con `backdrop-blur-sm`):
    - *Columna 1 (Identidad & Vitrina Web):* Publicacion en tienda, selector de vitrinas, marca, variante de diseño, telas, colores y descripcion comercial.
    - *Columna 2 (Existencias & Ubicacion):* 4 KPIs reactivos en caliente (`Fisico en Rack`, `Comprometido`, `Retenido en Locks`, `Disponible Venta`), matriz completa de tallas con disponibilidad individual, slotting fisico (`Pasillo - Rack - Nivel / Gaveta`) y control sanitario limpio sin duplicidades de codigo.
    - *Columna 3 (Metricas RevOps & Reorden):* Calculadora ultra-reactiva en vivo:
      - PVP Base vs Descuento % $\rightarrow$ PVP Efectivo recalculado en el input.
      - Utilidad neta unitaria en tiempo real.
      - Margen real sobre CPP (%) y margen marginal sobre costo con semaforo dinamico (Verde $\ge 40\%$, Ambar $25-39\%$, Rojo $< 25\%$).
      - Plan de reorden con botones de incremento rapido (`+30`, `+60`, `+120`) e inversion monetaria requerida proyectada.
  * Reactividad Instantanea & Guardado Directo:
    - Cualquier cambio en cantidades o precios actualiza los KPIs y margenes de inmediato.
    - Boton "Guardar Cambios" persiste los datos directamente mediante peticion atómica y emite un toast de confirmacion global, sin intermediar modales imprevistos de compras.
* **Backend:**
  * Endpoints RESTful:
    - `GET /api/v1/inventory/products/{sku}/detail`
    - `PUT /api/v1/inventory/products/{sku}/update-complete`
* **Pruebas Automatizadas:** `tests/Feature/Inventory/SkuPanoramicModalTest.php` evaluando la carga de datos por SKU, recálculo de margenes y persistencia atómica.

---

### Slice 5: Verificación Integral End-to-End, Sincronización de Catálogo y Cierre
* **Objetivo:** Certificar que la creacion de productos, la edicion panoramica y el catálogo operativo operan en armonia sin regresiones en las 238 pruebas previas.
* **Acciones:**
  * Ejecucion de la suite completa de pruebas: `artisan test`.
  * Verificacion estricta de cero emojis en codigo, vistas y commits.
  * Actualizacion del backlog en `docs/PENDING_TASKS.md` marcando `TASK-INV-14`, `TASK-INV-15` y `TASK-INV-18` como `Completada (Fullstack End-to-End)`.
  * Merge a `master` y creacion del tag `v1.6-prioridad-inventario-avanzado`.

---

## 5. Estrategia de Ramas y Commits

* **Rama de Trabajo:** `feature/inventario-avanzado-catalogo-sku`
* **Formato de Commits:** Commits atomicos por slice siguiendo la convencion:
  - `feat(inventory): implementar gestor de curvas de tallas y volumenes (slice 1)`
  - `feat(inventory): implementar generador de auto-sku y deteccion de duplicados (slice 2)`
  - `feat(inventory): implementar drawer full-width con copiloto ia y tags seo (slice 3)`
  - `feat(inventory): implementar modal panoramico centrado max-w-7xl y reactividad de sku (slice 4)`
  - `test(inventory): verificar integracion completa e2e y sincronizar backlog (slice 5)`
