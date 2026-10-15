# Especificación Técnica: Hub 2 - Tienda & CMS Multiverso
## Plataforma RevOps E-Commerce Multiverso & ERP (Mi Tienda Online)

**Módulo:** Hub 2 (Gestor de Contenido & CMS de Vitrinas)  
**Versión de Especificación:** 3.1 (SSOT - Auditada con Código Real)  

---

## 1. Propósito & Alcance del Módulo

El **Hub 2** gestiona la presentación visual, narrativa editorial y experiencia del cliente en la tienda en línea (Storefront PWA). Permite la administración en tiempo real de:
1. **Conmutador de Vitrinas Multiverso:** Configuración individualizada para Marte, Venus, Tiresias y Terra.
2. **Editor de Paleta Cromática:** Definición de variables CSS (`:root`) aplicadas de forma instantánea a la tienda pública.
3. **Constructor Modular de Secciones (Drag & Drop):** Ordenamiento interactivo mediante `SortableJS` de banners hero, feeds de catálogo, carruseles y bloques editoriales.
4. **Submódulo Unificado de Contenido & Reglas:** Banners promocionales, reglas de flete courier, documentos oficiales y configuración del pie de página (`STORE_FOOTER_CONFIG`).

---

## 2. Modelo de Datos & Entidades Clave

```mermaid
erDiagram
    CMS_STOREFRONT_CONFIGS ||--o{ CMS_SECTIONS : displays
    CMS_SECTIONS ||--o{ CMS_PAGES : links
    CMS_OFFICIAL_DOCUMENTS ||--|| STOREFRONT : embeds
```

---

## 3. Lógica de Negocio, File Scope & Contratos de Datos

### 3.1. Inyección Dinámica de Tokens y Vitrinas
- **Estado:** `[IMPLEMENTADO]`
- **Contrato de Datos:**
  - **Modelo Eloquent:** `App\Models\CmsStorefrontConfig`
  - **Estructura MySQL:** 
    - `cms_storefront_configs` (`universe`, `primary_color`, `secondary_color`, `accent_color`, `background_color`, `surface_color`, `text_color`, `muted_text_color`, `border_color`)
- **File Scope:**
  - `app\Http\Controllers\PortalStoreController.php` (Método `updateStorefront`)
- **Flujos y Casos Límite:**
  - Las variables se inyectan dinámicamente en el `<style id="hub2DynamicTheme">` del layout PWA. El backend procesa las subidas multiparte (FormData) para logotipos SVG/PNG.

### 3.2. Constructor Modular de Secciones
- **Estado:** `[IMPLEMENTADO]`
- **Contrato de Datos:**
  - **Modelo Eloquent:** `App\Models\CmsSection`
  - **Estructura MySQL:** 
    - `cms_sections` (`type` enum: `'hero_carousel', 'catalog_section', 'editorial_banner', 'custom_html_css_js'`, `sort_order`, `status`, `settings` JSON, `universe`)
- **File Scope:**
  - `PortalStoreController@reorderSections`
  - `PortalStoreController@updateSection`
- **Flujos y Casos Límite:**
  - Soporte nativo para ordenamiento vía AJAX enviando un array de IDs (`{orderedIds: [1, 3, 2]}`).

### 3.3. Documentos Oficiales Inmutables y Footer
- **Estado:** `[IMPLEMENTADO]`
- **Contrato de Datos:**
  - **Modelo Eloquent:** `App\Models\CmsOfficialDocument`
  - **Estructura MySQL:** 
    - `cms_official_documents` (`code`, `payload` JSON)
- **File Scope:**
  - `PortalStoreController@updateDocument`
- **Flujos y Casos Límite:**
  - Almacena el payload JSON del `STORE_FOOTER_CONFIG` (que contiene `brand_summary`, `whatsapp_contact`, `show_revops_link`, etc.) o normativas como `SIZE_GUIDE_TORZO`. Se propaga globalmente a todas las vistas mediante View Composers.

---

## 4. Endpoints RESTful & Rutas Operativas (Realidad de Código)

| Método | Endpoint | Controlador | Propósito |
| :--- | :--- | :--- | :--- |
| `POST` | `/admin/tienda-cms/secciones/reordenar` | `PortalStoreController@reorderSections` | Actualiza el campo `sort_order` tras el evento de Drag & Drop. |
| `POST` | `/admin/tienda-cms/vitrina/actualizar` | `PortalStoreController@updateStorefront` | Guarda los tokens cromáticos y assets de un universo. |
| `POST` | `/admin/tienda-cms/documentos/{code}` | `PortalStoreController@updateDocument` | Actualiza el payload JSON de un documento oficial. |
| `POST` | `/admin/tienda-cms/secciones/{id}` | `PortalStoreController@updateSection` | Actualiza configuraciones específicas de un módulo. |

---

## 5. Reglas de Negocio Oficiales

1. **Límite máximo de secciones modulares activas simultáneas en el Home Feed PWA:**  
   *Regla de Negocio:* El límite estricto es de **8 secciones** (diseño ultra-rápido y minimalista). Esto protege la hidratación DOM y maximiza la puntuación SEO de Web Vitals en dispositivos móviles de gama baja. El backend debe rechazar la creación o activación de una 9na sección simultánea.

2. **Paridad Estructural Estricta y Previsualización en Tiempo Real en Secciones Modulares:**  
   *Regla de Negocio & UI/UX:* Los botones de editar de las secciones modulares del Home (`store.blade.php`) abren un modal (`editSectionModal`) que contiene exactamente la misma estructura de edición que el modal de creación (`createSectionModal`). Esto incluye:
   - Layout de 2 columnas de pantalla completa (`md:w-[700px]` para el formulario y columna interactiva de previsualización a la derecha).
   - Bifurcador de modo inmediato (Visual Estructurado vs Código Custom Scoped).
   - Bloque común de textos con tipografías Google Fonts independientes para títulos y subtítulos, colores hex y alineación con botones de icono (izquierda, centro, derecha).
   - Motor Hero con soporte para Hero Fullpage vertical (`settings[full_width]`), **Hero 100% Horizontal** independiente con switch (`settings[full_width_horizontal]`), altura personalizada, efecto Parallax Scrolling (`backgroundAttachment = 'fixed'`), selección de fondo (imagen con Alt SEO vs video MP4 con autoplay/loop/muted), y capa de degradado overlay interactivo (RGBA).
   - **Foco de Atención de la Imagen (Matriz de 9 Puntos):** Selector visual en cuadrícula de 9 celdas (`settings[image_focal_point]`) para fijar el anclaje `background-position` / `object-position` de la imagen o video (`left top`, `center top`, `right top`, `left center`, `center center`, `right center`, `left bottom`, `center bottom`, `right bottom`), asegurando que el sujeto permanezca enfocado al recortarse en vista mobile (375px) o ultrawide.
   - **Conmutación de Botones en Hero vs Clic Integral:** Toggle switch para botones (`settings[hero_has_buttons]`). Si está habilitado, permite configurar botones CTA primario y secundario (fondo, texto, bordes y sombras). Si está deshabilitado, los botones se ocultan y toda la superficie del Hero opera como un hipervínculo navegable integral (`settings[hero_click_url]`, clase `.hero-full-click-link`).
   - **Gestión de Espaciados Modulares (Márgenes Superior e Inferior):** Controles independientes (`settings[spacing_top]` y `settings[spacing_bottom]`) con input numérico, range slider (0-120px) y presets rápidos (`0px`, `12px`, `24px`, `36px`, `48px`). Por defecto, el Storefront elimina las brechas y espacios en blanco (`margin: 0px`, `padding: 0px`), permitiendo secciones adyacentes continuas y aplicando los espaciados personalizados de forma reactiva en Live Preview y en Storefront.
   - Motor de Carrusel dinámico con modo general o por slide y gestión dinámica de slides.
   - Motor de Pop-up y Bloque Personalizado / Código HTML inyectado.
   - Previsualización renderizada en tiempo real (`Live Preview`) reactiva instantáneamente a cualquier evento `input` o `change`, con alternador de vista Desktop y Mobile (375px) sin colisiones de scope entre modales.
   - Pre-población completa e inmediata de todos los campos al presionar "Editar" (`openEditSectionModal`), disparando de inmediato la previsualización renderizada.


## Security & Data Privacy (DoD Compliance)
- **CMS Form Validations**: Enforced UX rules, updating all `<textarea>` inputs in `store.blade.php` to include `resize: vertical !important;` styling.
- **UX/UI Compliance**: Verified that CMS Modals are immediate children of `#storeAppRoot` (depth = 2) to prevent z-index and overflow side-effects.
- **Sanitization**: Storefront endpoints properly sanitize input using FormRequests.

## 6. Seguridad y Arquitectura (Security by Design)
- **RBAC & Control de Acceso:** Todos los endpoints de gestión de CMS en `PortalStoreController` están blindados con `Gate::authorize('manage-cms')`, garantizando que sólo roles autorizados (como `cms_editor` o `store_manager`) puedan realizar modificaciones.
- **Validación por FormRequests:** Se eliminaron las validaciones en línea ("Fat Controllers"). Se han implementado Request Forms dedicados (ej. `CheckoutStoreRequest`, `UpdateStorefrontRequest`, `StoreSectionRequest`, etc.) para sanear y validar rígidamente los datos de entrada.
- **Purga de God Object:** Se erradicaron métodos "zombie" de Hubs B2B/Finanzas (Hub 4/Hub 5) que residían incorrectamente en `StorefrontController`, separando estrictamente la lógica D2C de las operaciones de back-office.
