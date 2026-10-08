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

---

## 6. Fase 2: Perfeccionamiento de Identidad de Marca, Paleta Multitoken y Subnavegación (Hub 2)
**Fecha:** 30 de Septiembre de 2026  
**Objetivo:** Resolver los puntos críticos identificados en la personalización de vitrinas y la usabilidad de navegación en el panel de control.

### 6.1. Diagnóstico y Requerimientos de Usuario
1. **Subnavegación con Barra de Scroll Horizontal (Evidencia `media_1790790894274.png`):**
   - La barra de pestañas forzaba `overflow-x: auto; white-space: nowrap;`, generando una barra de desplazamiento horizontal deficiente en desktop.
   - **Solución:** Reorganización de la barra de pestañas en un diseño limpio de **dos filas** (Grid 4x2 responsivo), eliminando el scroll horizontal y permitiendo visualización directa de los 8 submódulos:
     - Fila 1: 1. Editor Visual Multiverso | 2. Banners & Marquesinas | 3. Reglas Comerciales & Fletes | 4. Contenido Editorial & Tallas.
     - Fila 2: 5. Páginas & Navbar | 6. Revista Digital | 7. Tracking & Analítica | 8. Filtros Catálogo.
2. **Paleta Cromática Insuficiente (Solo 2 colores en formulario):**
   - El formulario de vitrina solo presentaba Color de Acento y Color de Fondo, impidiendo controlar la jerarquía visual de la marca.
   - **Solución:** Implementación de una paleta cromática completa de **8 tokens semánticos**:
     - `primary_color`: Títulos H1, header desktop, botones oscuros/primarios.
     - `secondary_color`: Botones secundarios, bordes activos, badges complementarios.
     - `accent_color`: Botón CTA principal, enlaces activos, badges de oferta.
     - `background_color`: Fondo del lienzo global (`body`, `--brand-bg`).
     - `surface_color`: Fondo de tarjetas de producto y contenedores (`--brand-surface`).
     - `text_color`: Tipografía principal legible (`--brand-text`).
     - `muted_text_color`: Textos secundarios, SKU, IVA, descripciones (`--brand-muted`).
     - `border_color`: Bordes de inputs, tarjetas y separadores (`--brand-border`).
3. **Desacoplamiento de Campos Hero del Editor de Vitrina:**
   - En Tab 1 se duplicaban campos estáticos de Hero (`hero_title`, `hero_subtitle`, `hero_cta_text`, `hero_cta_url`), entrando en conflicto con el motor dinámico de secciones Drag & Drop donde el Hero (`hero_carousel` o `editorial_banner`) se gestiona como componente modular reordenable.
   - **Solución:** Eliminación de los campos Hero del formulario de Vitrina e incorporación de un aviso contextual que direcciona a la sección Drag & Drop inferior. El Previsualizador en Vivo se actualiza para reflejar la identidad de marca, header, paleta cromática, tarjetas y favicon/logos.
4. **Configuración de Favicon y Logos del Sitio:**
   - La vitrina carecía de campos para configurar la iconografía y activos gráficos de la marca.
   - **Solución:** Incorporación de campos dedicados con previsualización inmediata:
     - `logo_url`: Logo principal para la cabecera Desktop (SVG/PNG).
     - `logo_mobile_url`: Logo/isotipo para la cabecera Móvil y PWA icon.
     - `favicon_url`: Ícono de pestaña del navegador (.ico/.png/.svg).
   - Sincronización automática con `<link rel="icon">`, `<header>` desktop y `<header>` móvil en [resources/views/layouts/store-pwa.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/layouts/store-pwa.blade.php).

### 6.2. Implementación Técnica Realizada
1. **Migración de Base de Datos:**
   - Creada y ejecutada la migración `2026_09_30_120000_add_branding_and_palette_to_cms_storefront_configs.php` añadiendo `logo_url`, `logo_mobile_url`, `favicon_url`, `border_color` y `muted_text_color`.
2. **Modelos y Persistencia:**
   - Actualizado [app/Models/CmsStorefrontConfig.php](file:///c:/laragon/www/mi-tienda-online/app/Models/CmsStorefrontConfig.php) con defaults para los 4 universos y `$fillable` completo.
3. **Controladores:**
   - Actualizado [app/Http/Controllers/PortalStoreController.php](file:///c:/laragon/www/mi-tienda-online/app/Http/Controllers/PortalStoreController.php) con soporte para los 8 tokens cromáticos, logos, favicon y carga física de archivos a `public/uploads/branding/`.
   - Actualizado [app/Http/Controllers/StorefrontController.php](file:///c:/laragon/www/mi-tienda-online/app/Http/Controllers/StorefrontController.php) inyectando la paleta y branding a `$currentStorefront` y `universeMeta`.
4. **Vistas e Interfaz:**
   - [resources/views/portal/store.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/portal/store.blade.php):
     - Barra de navegación de submódulos en Grid 4x2 responsivo sin scroll horizontal.
     - Formulario de Vitrina con 8 tokens cromáticos (pickers + hex inputs), activos gráficos (logos desktop/móvil y favicon con preview de thumbnail y carga de archivos) y aviso de desacoplamiento de Hero.
     - Marco de previsualización en vivo sincronizado en tiempo real.
     - Indicador KPI de Paleta Multitoken con muestra visual de los 8 swatches activos.
   - [resources/views/layouts/store-pwa.blade.php](file:///c:/laragon/www/mi-tienda-online/resources/views/layouts/store-pwa.blade.php):
     - Inyección dinámica en `:root` de `--brand-primary`, `--brand-secondary`, `--brand-accent`, `--brand-bg`, `--brand-surface`, `--brand-text`, `--brand-muted` y `--brand-border`.
     - Inyección de `<link rel="icon">` y `<link rel="apple-touch-icon">`.
     - Renderizado de logos con fallback SVG.
5. **Pruebas Automatizadas:**
   - Implementado test `test_updating_vitrina_with_full_palette_and_branding_assets` en [tests/Feature/Hub2AdminStoreControllerTest.php](file:///c:/laragon/www/mi-tienda-online/tests/Feature/Hub2AdminStoreControllerTest.php) con `Storage::fake` y `UploadedFile::fake`.
   - 100% de la suite `artisan test --filter="Hub2AdminStoreControllerTest|StorefrontDynamicSectionsTest"` aprobada (17 tests, 75 aserciones).


