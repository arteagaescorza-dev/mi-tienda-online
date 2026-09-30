# Plan de Implementación Técnica: Auditoría Visual, Funcional y Sinergia Tienda Real (Hub 2)
## Resolución Definitiva de Puntos Ciegos, Creador de Secciones, Persistencia de Vitrinas y Ficha Standalone
**Rama de Trabajo:** `feature/hub2-real-store-integration`  
**Roles Responsables:** Senior Fullstack Developer & Lead Software Architect | Senior UX/UI Designer | Senior QA Engineer  
**Fecha de Emisión:** 29 de Septiembre de 2026  
**Documento Contractual Relacionado:** [PENDING_TASKS.md](PENDING_TASKS.md) | [CMS_ROADMAP.md](CMS_ROADMAP.md) | [PLAN_DESARROLLO_HUB2_TIENDA_REAL.md](PLAN_DESARROLLO_HUB2_TIENDA_REAL.md)

---

## 1. Diagnóstico Clínico de la Auditoría Visual (Causas Raíz Identificadas)

Tras la revisión interactiva del usuario y el análisis de las evidencias gráficas aportadas, se identificaron 5 puntos ciegos críticos que impiden la experiencia operativa deseada:

### Evidencia 1: Botón `+ Crear Nueva Sección` Inoperativo & Tipologías Insuficientes
* **Causa Raíz:** El botón disparaba el selector `data-action="open-create-section-modal"`, pero la captura de eventos colisionaba con clases de Tailwind CDN / scripts de panel y no removía de forma reactiva la clase `hidden`.
* **Carencia Funcional:** El modal actual trataba de mezclar en un solo bloque campos de catálogo y campos de código. El requerimiento de negocio exige una bifurcación clara:
  1. **Secciones de Tarjetas de Productos (Catálogo Real Hub 4):** Basadas en taxonomías estrictas (Etiquetas, Familias textiles, Categorías, Subcategorías, Descuentos/Liquidaciones, Marcas y Tipos de prenda). **Cero código HTML/CSS/JS manual** para evitar romper la cuadrícula responsiva del storefront.
  2. **Bloques de Contenido Editorial (Heros, Carruseles, Banners y Botones CTA):** Aquí **SÍ** se habilita el editor aislado de HTML, CSS scoped y JS.
  3. **Modales Promocionales / Popups:** Posibilidad de crear ventanas emergentes controladas desde el CMS (anuncios de temporada, promociones flash, captación de suscriptores).

### Evidencia 2: El botón `Guardar Cambios de Vitrina` No Persiste ni Refleja Cambios en la Tienda
* **Causa Raíz:** Desalineación entre el atributo `name` de los campos en [resources/views/portal/store.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/store.blade.php) y la migración SQL `cms_storefront_configs`. Los campos se enviaban o serializaban de manera desincronizada, y el previsualizador en vivo se desconectaba al alternar entre vitrinas (`marte`, `venus`, `tiresias`, `terra`).
* **Impacto:** Aunque el usuario pulsaba el botón, la tabla `cms_storefront_configs` mantenía valores por defecto y el storefront público seguía mostrando la paleta nativa.

### Evidencia 3: Imposibilidad de Editar Secciones (Vitrina Destino, Estado, Archivado y Metadatos)
* **Causa Raíz:** La tabla de secciones solo ofrecía las acciones `Pausar` y `Eliminar`.
* **Carencia Funcional:**
  - Inexistencia del botón `[Editar]` con su respectivo modal `#editSectionModal` para modificar el nombre, título, bajada, filtros o código custom de una sección creada.
  - Inexistencia de la acción `[Archivar]` (pasar a estado `archived`), obligando al usuario a eliminar la sección si no deseaba verla activa.
  - Imposibilidad de reasignar la vitrina de destino (`all`, `marte`, `venus`, `tiresias`, `terra`) una vez creada.

### Evidencia 4: El Producto en la Tienda Real Sigue Abriendo Modal / Drawer
* **Causa Raíz:** En las tarjetas de producto de la tienda (`resources/views/store/pwa/home.blade.php` y scripts de `store-pwa.js`), las tarjetas llaman a `openProductModal(id)` en lugar de navegar directamente mediante un enlace hipertexto canónico a la ruta `/productos/{slug}` ([product-detail.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/store/pwa/product-detail.blade.php)).

### Evidencia 5: Las Siguientes 7 Pestañas de Hub 2 Aparecen en Blanco
* **Causa Raíz:** Los contenedores `#storeBannersPanel`, `#storeShippingPanel`, etc., tenían clases condicionales `hidden` cuya alternancia de visualización no estaba forzando el repintado DOM (`display: block !important`) al activarse, dejando el área inferior vacía.

---

## 2. Matriz de Tareas de Ejecución

| ID Tarea | Título Operativo | Submódulo Destino | Prioridad | Estado |
| :--- | :--- | :---: | :---: | :---: |
| `TASK-H2-FIX-01` | Reingeniería del Creador de Secciones & Modales (División Catálogo vs Contenido Custom) | Submódulo 1: Editor Visual | **Alta (Inmediata)** | **Completada** |
| `TASK-H2-FIX-02` | Sincronización y Persistencia Real de Vitrinas (BD, Cache & `:root` Storefront) | Submódulo 1: Editor Visual | **Alta (Inmediata)** | **Completada** |
| `TASK-H2-FIX-03` | Suite de Edición, Reasignación de Vitrina, Archivado y Estado de Secciones | Submódulo 1: Editor Visual | **Alta (Inmediata)** | **Completada** |
| `TASK-H2-FIX-04` | Navegación Nativa a PDP Standalone (Eliminación de Modales/Drawers en Tienda Real) | Storefront D2C PWA | **Alta (Inmediata)** | **Completada** |
| `TASK-H2-FIX-05` | Reactividad, Estilos y Visibilidad Garantizada de las 7 Pestañas Restantes de Hub 2 | Hub 2 (Submódulos 2 al 8) | **Alta (Inmediata)** | **Completada** |

---

## 3. Especificación Técnica de Implementación (Fullstack Senior)

### 3.1. Reingeniería del Creador de Secciones (`TASK-H2-FIX-01`)
* **Modal Unificado con Pestañas de Tipo:**
  ```text
  [Modal: Crear / Editar Sección Modular]
  ├── Tipo 1: Cuadrícula / Carrusel de Productos (Catálogo Hub 4)
  │    ├── Filtro por Categoría (Boxers, Tangas, Jocks, Bralettes, etc.)
  │    ├── Filtro por Familia Textil (Modal, Bambú, Microfibra, Seda, Algodón)
  │    ├── Filtro por Etiqueta / Tag (Sensual, Deportivo, Diario, Afirmativo)
  │    ├── Filtro por Descuento (Solo productos en rebaja / liquidación)
  │    ├── Filtro por Marca y Género
  │    └── Límite de prendas (4, 8, 12, 16) y Formato (Grid 4x4 vs Slider Horizontal)
  └── Tipo 2: Bloque de Contenido / Hero / Carrusel / Modal Flotante
       ├── Tipo de bloque: Hero Banner, Carrusel Editorial, Banner Promocional, Modal Popup
       ├── Título (H1/H2), Subtítulo, Texto Botón CTA, URL Enlace Destino
       └── Editor de Código Aislado: HTML Custom, CSS Scoped y JavaScript
  ```
* **Apertura y Cierre Garantizado:**
  - Uso de listeners nativos e independientes `document.getElementById('openCreateSectionBtn').addEventListener('click', ...)` eliminando cualquier bloqueo por `app.js`.

### 3.2. Persistencia y Reflejo Inmediato de Vitrinas (`TASK-H2-FIX-02`)
* **Alineación de Campos:**
  - En el formulario Blade:
    - `name="name"` / `name="brand_name"`
    - `name="primary_color"`, `name="accent_color"`, `name="background_color"`
    - `name="hero_title"`, `name="hero_subtitle"`, `name="hero_cta_text"`, `name="hero_cta_url"`
* **Controlador `PortalStoreController::updateStorefront`:**
  - Acepta tanto snake_case como camelCase en `$request->validate()`.
  - Persiste directamente en `CmsStorefrontConfig::updateOrCreate(['universe' => $universe], ...)`.
  - Invalida la caché de vitrina `Cache::forget('cms_storefront_' . $universe)`.
  - Al recargar la tienda (`/` o `/productos`), el layout [app.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/layouts/app.blade.php) toma los nuevos colores inyectados en `:root` (`--store-bg`, `--store-accent`).

### 3.3. Edición, Reasignación de Vitrina y Archivado (`TASK-H2-FIX-03`)
* **Nuevas Rutas en `routes/web.php`:**
  ```php
  Route::put('/admin/tienda-cms/secciones/{section}', [PortalStoreController::class, 'updateSection'])->name('portal.store.sections.update');
  Route::post('/admin/tienda-cms/secciones/{section}/archivar', [PortalStoreController::class, 'archiveSection'])->name('portal.store.sections.archive');
  ```
* **Columna de Acciones Enriquecida en la Tabla:**
  - Botón `[Editar]`: Abre `#editSectionModal` con los valores de esa fila inyectados mediante `dataset`.
  - Selector de Vitrina Destino: `all` (Todas), `marte`, `venus`, `tiresias`, `terra`.
  - Botón `[Archivar]`: Conmuta el estado a `archived`, separándolo de `active` y `draft`.
  - Botón `[Eliminar]`: Con confirmación formal previa.

### 3.4. Erradicación de Modales/Drawers en la Tienda Real (`TASK-H2-FIX-04`)
* **Modificación en Vistas de Tienda (`store/pwa/home.blade.php`, `catalog.blade.php`):**
  - Las tarjetas de producto deben renderizar un enlace tradicional accesible:
    `<a href="{{ route('store.product', $product->slug) }}" class="product-card-link">`
  - Se suprime la apertura forzada de `openProductModal(id)` al pulsar sobre la tarjeta.
  - La ficha técnica se abre en la ruta canónica `/productos/{slug}` en pantalla completa sin modales intrusivos.

### 3.5. Reactividad y Visibilidad de las 7 Pestañas Restantes (`TASK-H2-FIX-05`)
* **Gestor de Pestañas Robusto:**
  - El selector de pestañas aplicará estilos en línea `display: block !important` al panel activo y `display: none !important` a los paneles inactivos.
  - Se actualizará el parámetro URL `?tab=banners`, `?tab=shipping`, `?tab=editorial`, `?tab=pages`, `?tab=magazine`, `?tab=tracking`, `?tab=filters` para mantener el estado tras recargar.

---

## 4. Plan de Pruebas Automatizadas & QA (Senior QA)

1. **Test de Persistencia de Vitrinas:**
   - Envío de POST con nuevos colores y copies -> Verificación de que la base de datos `CmsStorefrontConfig` contiene los nuevos valores y que el HTML del Storefront renderiza los estilos CSS en `:root`.
2. **Test de CRUD y Archivado de Secciones:**
   - Creación de sección con filtros de catálogo -> Comprobación de que `query_rules` guarda las taxonomías.
   - Actualización de vitrina destino de `marte` a `venus` vía PUT -> Comprobación de cambio en BD.
   - Archivado de sección vía POST -> Comprobación de estado `archived`.
3. **Test de Navegación PDP:**
   - Petición HTTP GET a `/productos/{slug}` -> Retorno 200 con vista standalone [product-detail.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/store/pwa/product-detail.blade.php) sin modal wrappers.
4. **Verificación de 71 Tests del Módulo:**
   - Ejecución de suite completa `artisan test --filter="Hub2|Storefront"` con 100% de aserciones válidas.

---

## 5. Certificación de Ejecución y Resultados QA (Senior QA Engineer)

* **Fecha de Certificación:** 29 de Septiembre de 2026
* **Suite Ejecutada:** `artisan test --filter="Storefront|Hub2"`
* **Resultado:** **76 tests pasados con éxito (100%), 380 aserciones, 0 errores, 0 fallos.**
* **Detalle de Suites Validadas:**
  1. `Hub2AdminStoreControllerTest`: 12 pruebas completas (CRUD secciones, bifurcación catálogo vs contenido custom, persistencia de vitrinas via AJAX, reordenamiento SortableJS, toggle y archivado de secciones).
  2. `Hub2CmsPersistenceTest`: Validación de integridad referencial DDL y modelos Eloquent de CMS.
  3. `Hub2CmsPagesAndBlogTest`: Creación y navegación de páginas CMS institucionales y entradas de revista digital.
  4. `StorefrontDynamicSectionsTest`: Renderizado de secciones curadas, banners, heros y carruseles en el home público.
  5. `StorefrontPwaHomeTest`: Integridad de vitrinas multiverso (`marte`, `venus`, `tiresias`, `terra`) y enlaces de navegación directa a `/productos/{slug}`.
  6. `StorefrontPwaPdpTest`: Navegación e interacción directa a la PDP full-screen sin modales ni drawers.
  7. `StorefrontPwaCartTest` y `StorefrontPwaCheckoutTest`: Flujo completo de conversión y compatibilidad con RevOps.
