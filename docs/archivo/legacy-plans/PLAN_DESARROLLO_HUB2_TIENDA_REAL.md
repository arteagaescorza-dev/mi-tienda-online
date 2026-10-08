# Plan Técnico de Arquitectura y Procedimiento de Desarrollo: Hub 2 (Tienda & CMS) & Conexión con Tienda Real

**Rama de Trabajo:** `feature/hub2-real-store-integration`  
**Autores / Roles:** Fullstack Senior | Senior UX/UI Designer | Senior QA Engineer  
**Objetivo:** Transformar el Hub 2 de un panel estático basado en caché a un motor dinámico desacoplado y reactivo, conectado bidireccionalmente con la tienda real PWA (Mobile-First) y el catálogo físico de Hub 4.

---

## 1. Diagnóstico del Estado Actual vs. Alcance Requerido

| Componente | Estado Actual | Estado Objetivo (Hub 2 Conectado) |
| :--- | :--- | :--- |
| **Persistencia CMS** | Archivos de caché temporales (`Cache::get/put`). Se pierden al limpiar caché. | Base de datos relacional DDL (tablas de secciones, páginas, blog, configuración visual y analítica). |
| **Estructura del Home** | Vista Blade rígida con secciones cableadas manualmente (`store.pwa.home`). | Motor de secciones dinámicas modulares (`CmsSection`) ordenables por Drag & Drop. |
| **Detalle de Producto (PDP)** | Se abre en un drawer/modal dentro del home (`autoOpenProductId`). | Página completa dedicada `/productos/{slug}` optimizada, sin drawers, con variantes en tiempo real de Hub 4. |
| **Menú y Páginas** | Rutas fijas hardcodeadas en layout. | Páginas CMS editables (`CmsPage`) con sincronización automática en el Navbar y footer. |
| **Blog & Revista Digital** | Inexistente. | Módulo CMS de artículos con categorías, tags, rich-text, autor y schema markup SEO. |
| **Filtros de Catálogo** | Arreglos estáticos de marcas. | Editor dinámico de facetas y taxonomías (Universos, Familias, Categorías, Subcategorías, Tags). |
| **Analítica & Tracking** | Sin inyección unificada. | Gestor de etiquetas (GTM, GA4, Meta Pixel y Meta Conversions API en backend). |

---

## 2. Arquitectura de Datos y Modelado DDL (Fullstack)

Se implementarán las siguientes migraciones y modelos Eloquent:

1. **`cms_storefront_configs`**:
   - `id`, `universe` (terra, marte, venus, tiresias), `primary_color`, `secondary_color`, `accent_color`, `background_color`, `typography_heading`, `typography_body`, `custom_css`, `custom_js`, `seo_meta` (JSON), timestamps.
2. **`cms_sections`**:
   - `id`, `universe`, `name`, `type` (`categories_grid`, `family_showcase`, `subcategories_slider`, `tags_cloud`, `custom_html_css_js`, `hero_carousel`, `brand_pillars`, `curated_products`, `editorial_banner`), `title`, `subtitle`, `query_rules` (JSON: filtros asociados a Hub 4), `custom_content` (HTML, CSS scoped, JS encapsulado), `sort_order`, `status` (`active`, `draft`, `archived`), timestamps.
3. **`cms_pages`**:
   - `id`, `slug`, `title`, `content_html`, `show_in_navbar`, `navbar_order`, `show_in_footer`, `seo_title`, `seo_description`, `seo_keywords`, `status`, timestamps.
4. **`cms_blog_posts`**:
   - `id`, `slug`, `title`, `excerpt`, `body`, `featured_image`, `category_id`, `tags` (JSON), `author_id`, `published_at`, `status` (`draft`, `published`, `archived`), `seo_meta` (JSON), timestamps.
5. **`cms_official_documents`**:
   - Centraliza: Guías oficiales de tallas (Torzo), Protocolos de cuidado textil, Avisos normativos y sanitarios (PROFECO). Modificables en CMS y consumidos por la PDP y secciones del home.
6. **`cms_tracking_settings`**:
   - `gtm_id`, `ga4_measurement_id`, `facebook_pixel_id`, `facebook_capi_token`, `facebook_test_code`, `is_active`.

---

## 3. Experiencia de Usuario e Interfaz (UX/UI Designer)

### 3.1. Hub 2 - Backoffice Canvas Builder
* **Layout Split-View:** Panel de configuración a la izquierda (selectores de taxonomías, editor de código con syntax highlighting, campos de color con selector HSL/Hex) y **Canvas de Previsualización en Vivo** a la derecha con conmutador de viewport (Móvil PWA 390px, Tablet 768px, Desktop 1200px).
* **Drag & Drop Intuitivo:** Implementación de drag handles con soporte táctil y de ratón (SortableJS), preview de arrastre con sombra volumétrica y guardado asíncrono debounced del orden de secciones.
* **Seguridad de Edición Custom (HTML/CSS/JS):** Aislamiento de estilos mediante contenedores con `data-section-id` para evitar fugas de CSS que rompan la consola administrativa o la tienda real.

### 3.2. Tienda Real - Mobile-First & PWA
* **PDP Full-Screen Dedicada (`/productos/{slug}`):**
  - Eliminación total de modales y drawers para la ficha técnica principal.
  - Galería táctil de imágenes en proporción 1:1 y 4:5 con zoom y soporte WebP optimizado.
  - Selector de variantes anatómicas (Tallas CH-3XL) con indicador de stock en tiempo real (Hub 4).
  - Bloques colapsables interactivos: Guía de Tallas Torzo interactiva, Cuidados de lavado y Aviso de precinto sanitario termosellado.
  - Sticky Bottom Bar para móviles: Botón directo "Agregar al Carrito" con precio visible y microinteracción háptica.
* **Sincronización del Navbar:** Menú responsive con enlaces generados dinámicamente desde `cms_pages` y `cms_sections`.

---

## 4. Estrategia de Calidad y Pruebas (Senior QA)

1. **Test Suite de Cohesión Hub 2 <-> Hub 4:**
   - Creación de sección por categoría en Hub 2 -> Verificación de que solo renderice productos activos de Hub 4 con stock disponible.
   - Modificación de colores del sitio -> Verificación de variables CSS inyectadas en `:root` en la tienda real.
2. **Test Suite de PDP & Checkout:**
   - Navegación directa a `/productos/{slug}` con carga limpia sin drawers.
   - Selección de variante y verificación del flujo hasta el carrito sin discrepancias de precio o stock.
3. **Auditoría PWA & Lighthouse:**
   - Cumplimiento de instalación standalone, offline fallback para páginas clave y Core Web Vitals (LCP < 2.5s, CLS < 0.1).
4. **Validación de Tracking:**
   - Disparo correcto de eventos `view_item`, `add_to_cart`, `initiate_checkout` en GA4, GTM y Facebook CAPI con `event_id` único para deduplicación.

---

## 5. Fases de Ejecución Propuestas

* **Fase 1: Capa de Persistencia & DDL Relacional (Migraciones y Modelos).**
* **Fase 2: Motor de Secciones Dinámicas y Drag & Drop en Hub 2.**
* **Fase 3: Renderizador Dinámico en Storefront PWA (Home & Colores).**
* **Fase 4: Transformación de PDP a Pantalla Completa Dedicada (`/productos/{slug}`).**
* **Fase 5: CMS de Páginas, Navbar Sincronizado y Blog / Revista Digital.**
* **Fase 6: Editor Dinámico de Filtros & Taxonomías.**
* **Fase 7: Suite de Tracking (GTM, GA4, Meta Pixel & CAPI) y Auditoría QA.**
