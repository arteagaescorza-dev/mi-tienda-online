# Reglas Oficiales del Proyecto (Project Rules & Governance Canon)
## Plataforma RevOps E-Commerce Multiverso & Suscripciones (Mi Tienda Online)

> **Propósito del Documento:**  
> Este documento compila, tipifica y declara de forma estricta e inmutable el **código de normas, reglas técnicas, directrices de arquitectura y estándares operativos** que rigen el desarrollo, refactorización y mantenimiento del proyecto *Mi Tienda Online*.  
> Toda decisión de ingeniería, diseño de interfaces, modelado relacional y ejecución en código fuente debe alinearse de manera obligatoria con las reglas aquí estipuladas.

---

## 1. Principios Fundamentales & Manifiesto de Ingeniería

### Regla 1.1: Enfoque Fullstack Senior End-to-End (Vertical Slice)
Queda estrictamente prohibido el desarrollo puramente cosmético, superficial o basado en maquetas estáticas desconectadas. Toda nueva funcionalidad, pantalla o ajuste debe desarrollarse como un **Vertical Slice completo (End-to-End)**:
$$\text{Migración MySQL} \longrightarrow \text{Modelos Eloquent} \longrightarrow \text{Servicios Transaccionales (ACID / Kardex)} \longrightarrow \text{Endpoints REST / FormRequests} \longrightarrow \text{UI PWA Reactiva} \longrightarrow \text{Pruebas Automatizadas}$$

* **Cero Datos Falsos en Producción:** Ningún botón, interruptor o selector puede quedar sin persistencia real en la base de datos o capa de caché operativa.
* **Respuesta Inmediata y Feedback Visual:** Toda acción del usuario en el panel administrativo o tienda PWA debe proveer confirmación háptica o visual (indicadores de carga, animaciones de éxito o toasts con stack acumulativo).

### Regla 1.2: Preservación de la Integridad del Código y Comentarios Existentes
* Se debe preservar de forma rigurosa la totalidad de comentarios, docstrings y anotaciones existentes en los archivos modificados, a menos que el usuario o un requerimiento técnico explícito ordene su reemplazo.
* No se deben alterar métodos o controladores preexistentes de otros submódulos sin verificar que la suite completa de pruebas unitarias y funcionales continúe ejecutándose sin errores.

### Regla 1.3: Sincronización Mandatoria con `BACKEND_INTEGRATION_SPEC.md`
Ninguna tarea de la plataforma se considera "Completada" sin que su arquitectura de persistencia, migración de base de datos, modelo Eloquent, endpoints RESTful con esquemas JSON de petición/respuesta y reglas de validación en FormRequests queden **100% formalizados y blindados en [`BACKEND_INTEGRATION_SPEC.md`](file:///c:/laragon/www/mi-tienda-online/docs/BACKEND_INTEGRATION_SPEC.md)**. Toda tarea pendiente debe mantener sincronizado de antemano su contrato de backend.

### Regla 1.4: Declaración Universal y Obligatoria de Codificación UTF-8
* **Estándar Inmutable UTF-8:** Todo el proyecto (código fuente, base de datos, comunicaciones HTTP y documentación técnica) se declara y opera bajo codificación **UTF-8 universal** (con cotejamiento MySQL `utf8mb4_unicode_ci` / conjunto de caracteres `utf8mb4`). Queda estrictamente prohibido el empleo de codificaciones legadas o regionales (ISO-8859-1, Windows-1252, Latin1).
* **Cabeceras HTTP y Vistas Blade:** Todas las plantillas Blade y documentos HTML deben declarar explícitamente `<meta charset="utf-8">`. Las respuestas del servidor deben emitir cabeceras `Content-Type: text/html; charset=UTF-8` o `application/json; charset=UTF-8`.
* **Capa de Persistencia y Conexión PDO:** Los archivos de configuración (`config/database.php`) y migraciones deben forzar `'charset' => 'utf8mb4'` y `'collation' => 'utf8mb4_unicode_ci'` para admitir caracteres acentuados en español, diacríticos y símbolos técnicos sin corrupción de datos.
* **Archivos Fuente:** Todo archivo `.php`, `.js`, `.css`, `.json`, `.md` y `.html` debe guardarse obligatoriamente en codificación UTF-8 sin marca de orden de bytes (BOM).
* **Sanitización Automática de Modelos:** Los modelos Eloquent (como `App\Models\Product`) implementan accesores y mutadores de saneamiento activo (`Product::sanitizeUtf8($value)`) para corregir en caliente cualquier entrada o registro histórico que presente doble codificación UTF-8 / Mojibake (`Ã³`, `Ã±`, `Ã¡`, etc.).

---

## 2. Reglas de Persistencia, Modelado y Contratos de Backend

### Regla 2.1: Transaccionalidad y Atomicidad en Kardex y Finanzas (ACID)
* Todo movimiento de mercancía (entradas de proveedor, ensambles de recetas BOM, apartados por checkout, despachos en mesa de expedición o retornos RMA) debe ejecutarse bajo transacciones de base de datos (`DB::transaction`).
* El Kardex transaccional de Hub 4 es la **fuente única e incontrovertible de verdad** para los inventarios. Ninguna pantalla o controlador puede alterar existencias mediante asignaciones directas sin asentar el registro histórico correspondiente en el Kardex.
* Se debe garantizar el cálculo riguroso del Costo Promedio Ponderado (CPP) por lote o variación.

---

## 3. Reglas de Arquitectura Multiverso & Pool Central de Inventario

### Regla 3.1: Desacoplamiento de Vitrinas frente al Stock Central (Single Inventory Pool)
* La plataforma opera un único almacén físico central y catálogo unificado en **Hub 4 (Inventario)** que abastece a cuatro vitrinas multiverso diferenciadas:
  1. **Marte:** Ropa interior masculina, atlética y jockstraps ergonómicos.
  2. **Venus:** Lencería de autor, bralettes y fórmulas de cuidado íntimo femenino.
  3. **Tiresias:** Ropa afirmativa, binders transpirables y prendas sin género.
  4. **Terra:** Moda unisex, accesorios de cuidado textil y membresías por suscripción.
* Ninguna vitrina opera con inventarios duplicados. La asignación a una vitrina se gestiona mediante reglas taxonómicas, marcas (`brand`), categorías y filtros dinámicos, nunca clonando SKUs en la base de datos.
* Si una prenda agota su stock en el Kardex central, se oculta o inhabilita automáticamente para la venta en todas las vitrinas donde esté presente.

### Regla 3.2: Desacoplamiento de Contenido Editorial
La configuración de marca, los 8 tokens cromáticos de estilo, los banners promocionales, los textos de presentación y las páginas dinámicas se configuran por vitrina en **Hub 2 (Tienda & CMS Multiverso)**, manteniendo independencia absoluta del inventario transaccional.

---

## 4. Reglas de Interconexión Cross-Hub & Flujos de Trabajo (Sin Roles)

### Regla 4.1: Especificación de Workflows Centrada en la Aplicación
* Los documentos de flujo y manuales de interconexión (como [`APPLICATION_WORKFLOW_SPEC.md`](file:///c:/laragon/www/mi-tienda-online/docs/APPLICATION_WORKFLOW_SPEC.md)) deben explicar de manera exhaustiva **cómo se interlazan las funcionalidades y los datos entre cada módulo**, prohibiendo explícitamente estructurar la documentación como descripción de puestos o roles de recursos humanos.
* Los 7 Hubs operativos se interconectan siguiendo flujos estandarizados:
  - **Hub 1 (Cabina Ejecutiva & Dashboard):** Orquestador de alertas, embudo comercial y salud del sistema.
  - **Hub 2 (Tienda & CMS Multiverso):** Configuración visual, plantillas modulares, blog y storefront PWA.
  - **Hub 3 (Marketing & CRM):** Telemetría CAPI, campañas UTM y carritos abandonados.
  - **Hub 4 (Inventario, Kardex & Catálogo):** Existencias físicas, slotting, recetas BOM y compras.
  - **Hub 5 (Finanzas & SAT):** Cascada de margen neto real, facturación CFDI 4.0 e impuestos.
  - **Hub 6 (Logística & Mesa de Expedición):** Paqueterías, manifiestos de salida, guías térmicas y RMA.
  - **Hub 7 (Convenios, Aliados & Extranet B2B):** Órdenes de compra a fabricantes y comisiones de aliados.

### Regla 4.2: Cascada de Margen Neto Real
Toda orden de compra debe calcular la rentabilidad real de la transacción aplicando deducciones sucesivas:
$$\text{GMV} - \text{Descuentos} - \text{Comisiones Pasarela} - \text{COGS Kardex} - \text{Costo Flete Courier} - \text{Empaque BOM} - \text{Impuestos} = \text{Margen Neto Real}$$
Bajo ninguna circunstancia se calculará el margen con costos teóricos o precios de lista desactualizados.

---

## 5. Reglas de Integridad DOM, Blade y Aislamiento de Componentes

### Regla 5.1: Aislamiento Estructural de Paneles y Modales (Regla Anti-Anidamiento)
* Todos los paneles de pestañas (`.tab-panel`) y todos los modales de control operativo (`#createSectionModal`, `#editSectionModal`, `#editSizeGuideModal`, etc.) deben estructurarse como **elementos hermanos directos e independientes** en el nivel jerárquico secundario (`depth = 2`) bajo el contenedor principal `#storeAppRoot`.
* **Prohibición de Anidamiento de Modales en Paneles Ocultos:** Bajo ninguna circunstancia un modal interactivo (`fixed inset-0 z-50`) puede quedar declarado dentro de un `.tab-panel` o dentro de una tarjeta de acordeón colapsable. La presencia de la regla CSS `.tab-panel.hidden { display: none !important; }` provoca la invisibilidad irreversible de cualquier elemento hijo sin importar su `z-index`.

### Regla 5.2: Balance Matemático y Cierre Estricto de Etiquetas Blade
* Cada archivo de plantilla Blade (`.blade.php`) debe mantener paridad absoluta entre aperturas `<div ...>` y cierres `</div>`.
* Cada bloque estructural o sección de acordeón debe etiquetar explícitamente su cierre mediante comentarios indicativos (ej. `</div> <!-- FIN SECCIÓN 1 ACORDEÓN -->`).
* Cualquier refactorización de vistas complejas debe validarse mediante herramientas de conteo y rastreo de profundidad sintáctica (scripts tipo `verify_div_tree.cjs`).

### Regla 5.3: Limpieza y Control de Estilos en Apertura de Modales
* Las funciones JavaScript de apertura de modales (ej. `window.openStoreModal(modalId)`) deben ejecutar obligatoriamente `modal.style.removeProperty('display')` previo a la asignación `modal.style.display = 'flex'`, erradicando estilos en línea residuales (`display: none;`) que interfieran con las clases de utilidad.
* La apertura de modales de creación debe restablecer de forma determinista el estado inicial limpio (ej. conmutación al modo predeterminado de catálogo).

---

## 6. Reglas de Interfaz de Usuario, CSS, PWA y Experiencia Móvil

### Regla 6.1: Sangrado de Pantalla Completa (Full-Width Breakout)
* Los bloques de contenido tipo Hero y carruseles principales deben disponer de la capacidad de expandirse al **100% del ancho del viewport (`100vw`)**, rompiendo de forma limpia los márgenes o límites del contenedor central de la tienda:
  ```css
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  ```
* Se debe impedir cualquier desborde horizontal indeseado aplicando `overflow-x: hidden` en el contenedor raíz del layout.

### Regla 6.2: Paridad y Aislamiento en Consultas de Medios (Media Queries)
* Toda regla de medios en hojas de estilo compartidas (ej. `@media (max-width: 768px)`) debe abrir y cerrar sus bloques con verificación estricta de paridad de llaves. Queda prohibido anidar declaraciones desktop dentro de media queries móviles o viceversa, ya que esto degrada la experiencia en pantallas pequeñas.

### Regla 6.3: Cache Busting Dinámico en Activos Críticos
* Para evitar que navegadores móviles y clientes PWA almacenen en caché estilos o scripts obsoletos, toda inclusión de recursos críticos en los layouts Blade debe acompañarse de un parámetro dinámico basado en la fecha de modificación del archivo:
  ```html
  <link rel="stylesheet" href="{{ asset('css/store-pwa.css') }}?v={{ filemtime(public_path('css/store-pwa.css')) }}">
  ```

### Regla 6.4: Ergonomía de Navegación y Subnavegaciones Sin Scroll Horizontal
* Las barras de subnavegación complejas (como el Subnav de Hub 2) deben distribuirse en cuadrículas compactas sin scroll horizontal (ej. 2 filas de 3 columnas en desktop que colapsen ordenadamente a 1 o 2 columnas en móvil).
* Los elementos de selección contextual frecuente (como el Conmutador de Vitrinas `.universe-selector-bar`) deben permanecer accesibles de manera transversal en el encabezado superior de todas las pestañas.

### Regla 6.5: Sobriedad Visual en Paneles Administrativos (Cero Emojis Informales)
* En interfaces operativas de backend y paneles ejecutivos, queda restringido el uso de emojis informales en cabeceras de módulos, tablas de datos y botones de acción. Se debe emplear exclusivamente iconografía vectorial SVG (Feather Icons, Heroicons o Lucide) y tipografía técnica/monoespaciada para claves, SKUs y métricas.

### Regla 6.6: Restricción Estricta de Redimensionamiento en Cajas de Texto (Solo Vertical, Jamás Horizontal)
* **Ninguna caja de texto (`<textarea>`, input multilínea o editor de código) puede expandirse de forma horizontal en ninguna pantalla o formulario del sistema.**
* Queda terminantemente prohibido el uso o comportamiento por omisión de `resize: both` o `resize: horizontal`.
* Todas las áreas de texto en el panel administrativo (`revops-panel.css`) y la tienda pública (`store-pwa.css`) deben aplicar obligatoriamente:
  ```css
  textarea {
    resize: vertical !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
  }
  ```
* **Justificación de UX/Layout:** La expansión horizontal rompe las columnas de formularios en cuadrículas, desborda los contenedores de modales estrechos y genera barras de scroll horizontal parásitas tanto en desktop como en dispositivos móviles. Solo se autoriza la elongación vertical para revisar textos extensos.

### Regla 6.7: Ficha Independiente de Producto (PDP) — Supresión de WhatsApp y Galería Modal con Swipe
* **Regla 6.7.1 (Supresión Estricta de WhatsApp en PDP):** Las páginas independientes de cada producto (`/productos/{slug}`) no deben mostrar bajo ninguna circunstancia botón, enlace o gatillo de WhatsApp (`.pdp-btn-whatsapp`, `#pdpWhatsappLink`, etc.). El embudo comercial de la PDP debe conducir directamente al flujo transaccional nativo de carrito (`addToCart`) y checkout D2C sin derivar la venta fuera de la plataforma.
* **Regla 6.7.2 (Apertura de Modales de Imagen y Swipe Táctil):** Las páginas independientes de producto deben permitir abrir un modal interactivo / visor lightbox a pantalla completa al hacer clic sobre las imágenes del producto (tanto en la tarjeta principal como en las miniaturas).
  - **Soporte de Gestos Swipe:** El visor modal debe admitir deslizamiento táctil horizontal fluido (`touchstart`, `touchmove`, `touchend` con prevención de scroll parásito en el eje Y), permitiendo al usuario cambiar de imagen con un simple gesto en dispositivos móviles.
  - **Interacción Multidispositivo:** Adicionalmente, el modal debe proveer controles por clics (flechas prev/next y barra de indicadores/dots), arrastre con ratón en escritorio (`mousedown`/`mousemove`), atajos de teclado (`ArrowLeft`, `ArrowRight`, `Escape`) y contador de progreso (`1 / N`).
  - **Aislamiento Estructural:** En estricto cumplimiento de la Regla 5.1, el modal `#pdpGalleryModal` debe ser hijo directo del contenedor de la vista (`depth = 2`), garantizando visibilidad `z-index: 99999` libre de anidamientos en paneles ocultos.

### Regla 6.8: Despliegue de Filtros del Storefront en Drawer Izquierdo (Off-Canvas)
* Los filtros de segmentación de catálogo en el storefront PWA (`#filterDrawer`) deben desplegarse invariablemente como un **drawer lateral deslizante desde el extremo izquierdo de la pantalla (`left: 0 !important; right: auto !important; transform: translateX(-100%);`)**.
* Al activarse (clase `.show`), transiciona de forma limpia a `transform: translateX(0) !important;` tanto en dispositivos móviles (`viewport < 768px`) como en pantallas de escritorio o tablets.
* **Prohibición de Layouts Invasivos:** Queda estrictamente prohibido implementar filtros en acordeones que empujen el grid de productos hacia abajo o sidebars estáticas fijas que reduzcan el área útil de la vitrina.
* **Interacción del Drawer:** Debe contar con backdrop oscuro semitransparente (`#drawerBackdrop`, `z-index: 10040`), botón de cierre rápido (X), botón de "Limpiar Filtros" y botón de acción principal para aplicar y cerrar ("Ver X Prendas").

### Regla 6.9: Reserva Temporal de Inventario y Cuenta Regresiva Exclusiva en Checkout
* **Activación Exclusiva en Checkout:** La cuenta regresiva del apartado temporal de productos (temporizador de 5 minutos / 300 segundos) y el bloqueo transaccional de stock para evitar sobreventa **se activan única y exclusivamente cuando el usuario inicia el flujo de checkout** (`openCheckoutModal()`). Queda prohibido disparar reservas de inventario o temporizadores de urgencia mientras el usuario navega por el catálogo o inspecciona pasivamente el carrito.
* **Preservación Inmutable del Carrito al Expirar:** Si el temporizador de 5 minutos llega a cero sin que la orden se concrete, la reserva transaccional se libera en el backend para permitir la compra por otros usuarios, pero los productos **permanecen en el carrito de compras del usuario**. Queda terminantemente prohibido vaciar o purgar automáticamente el carrito ante la expiración del temporizador.
* **Detección y Leyenda de Agotado/Descontinuado:** Si tras expirar el apartado temporal (o en visitas subsecuentes) la prenda se agota (`stock <= 0`) o es descontinuada del catálogo mientras permanece en el carrito del cliente, la interfaz del carrito y del checkout debe desplegar de forma inmediata y visible la leyenda:
  $$\textbf{"Ya no hay existencias"}$$
  (acompañada de badge visual en rojo `#dc2626`, bloqueo para proceder al checkout y notificación al usuario para retirar o sustituir la prenda).

### Regla 6.10: Supresión Definitiva de SPEI y Estandarización de Pasarelas de Pago
* **Cero SPEI en Storefront:** Queda formalmente retirado y prohibido el método de transferencia bancaria SPEI en la tienda pública, marquees, cintillos promocionales, pilares de confianza y embudo de checkout D2C.
* **Pasarelas Digitales Homologadas:** El storefront opera de forma estricta y transparente con dos modalidades de pago seguro:
  1. **Tarjetas de Débito y Crédito (Visa, Mastercard, American Express):** Procesadas en tiempo real mediante pasarela bancaria tokenizada con autorización instantánea y confirmación automática de pedido.
  2. **OXXO Pay:** Generación de referencia digital numérica de 14 dígitos en tiempo real con ventana de apartado físico de stock por 48 horas para liquidación en sucursales de conveniencia a nivel nacional.
### Regla 6.11: Navbar Sticky Visible Todo el Tiempo (Desktop & Móvil)
* **Adherencia Permanente en Scroll:** El storefront PWA cuenta con un encabezado/navbar (`.sticky-header-wrapper`) visible en todo momento tanto en pantallas de escritorio como en dispositivos móviles, anclado rígidamente al inicio del viewport:
  ```css
  .sticky-header-wrapper {
    position: sticky !important;
    top: 0 !important;
    z-index: 999 !important;
    background: rgba(255, 255, 255, 0.96) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
  }
  ```
* **Prohibición de Ocultamiento en Scroll:** Queda prohibido el uso de patrones tipo *auto-hide* o *headroom.js* que oculten el navbar al deslizar hacia abajo, asegurando que el acceso al carrito, catálogo, selector de universos y páginas institucionales sea instantáneo y continuo.

### Regla 6.12: Edición de Navbar y Cinta Banner Superior desde Hub 2 (Pestaña Páginas & Navbar)
* **Gestión Centralizada en Tab 4:** El navbar se edita desde **Hub 2 (Tienda & CMS), Pestaña Páginas & Navbar (`storePagesPanel`)**, desde el cual también se edita la **Cinta Banner superior** (marquesina informativa animada que corre por encima del navbar).
* **Parámetros de la Cinta Banner:** Permite conmutar el estado general (activar/pausar marquesina), editar el texto de los anuncios, asignar enlaces internos/externos opcionales y alternar la visibilidad individual de cada aviso.
* **Prohibición de Textos Estáticos Duplicados o Enlaces a WhatsApp:** La cinta banner debe alimentarse exclusivamente del repositorio dinámico de anuncios (`cms_announcements`), erradicando bloques estáticos hardcodeados y referencias a WhatsApp.

### Regla 6.13: Gestión Dual Desktop y Móvil de Páginas Institucionales Sincronizadas
* **Control Independiente de Visibilidad:** En la sección "Páginas Institucionales Sincronizadas en el Navbar", el administrador puede configurar y conmutar de manera independiente la presencia de cada página institucional en:
  1. **Versión Desktop:** Barra central de navegación del header (`show_in_navbar`).
  2. **Versión Móvil:** Barra táctil horizontal rápida bajo el header y bloque dedicado dentro del drawer de filtros (`show_in_mobile`).
  3. **Footer Corporativo:** Enlaces en la base del sitio (`show_in_footer`).
* **Ciclo de Vida y CRUD Integral:** Esta sección permite:
  - **Crear nuevas páginas:** Título, slug automático, contenido enriquecido, órdenes de visualización y metadatos SEO.
  - **Editar páginas existentes:** Mediante modal reactivo independiente (`#editPageModal`, ubicado en `depth = 2` bajo `#storeAppRoot` en estricto apego a la Regla 5.1).
  - **Activar y Pausar:** Conmutar al instante la disponibilidad pública de la página mediante endpoint dedicado (`toggle-status`).
  - **Archivar:** Pasar la página a estado histórico protegido sin eliminar el contenido (`archive`).
  - **Eliminar:** Purgar de forma permanente registros obsoletos mediante confirmación de seguridad.

### Regla 6.14: Optimización SEO Integral de Todo el Storefront
* **Metadatos Sociales y de Indexación:** Todo el storefront cuenta con optimización exhaustiva para motores de búsqueda y redes sociales:
  - Declaración `<meta name="robots" content="index, follow">` y canonical dinámico (`<link rel="canonical" href="...">`).
  - **OpenGraph Oficial Completo:** `og:site_name`, `og:type="website"`, `og:locale="es_MX"`, `og:title`, `og:description`, `og:url` y `og:image`.
  - **Twitter Cards Oficiales:** `twitter:card="summary_large_image"`, `twitter:title`, `twitter:description` y `twitter:image`.
* **Datos Estructurados Schema.org (JSON-LD):** Inyección de grafos JSON-LD estándar para:
  - `Organization` (nombre de marca, url, logo oficial).
  - `WebSite` con `SearchAction` dinámico apuntando al buscador de la tienda.
  - `Product` enriquecido en fichas de producto (PDP) con precios, monedas, disponibilidad `InStock`/`PreOrder` y SKU.
  - `WebPage` estructurado en páginas institucionales CMS.
* **Sitemap XML Dinámico (`/sitemap.xml`):** Generación automática en tiempo real de mapa del sitio bajo el estándar `http://www.sitemaps.org/schemas/sitemap/0.9`, incluyendo Home, catálogo de universos, páginas de explorar activas y productos visibles con sus frecuencias de cambio (`changefreq`) y ponderaciones de prioridad (`priority`).

### Regla 6.15: Navbar Canónico Permanente de 5 Botones (Desktop y Móvil)
* **Quinteta Canónica Obligatoria:** El navbar, tanto en versión de escritorio (`navbar-desktop`) como en el dock de navegación inferior móvil (`bottom-app-dock`), contiene estrictamente los siguientes 5 botones canónicos:
  1. **Inicio**
  2. **Explorar**
  3. **Catálogo**
  4. **Carrito** (con badge reactivo de contador de prendas)
  5. **Cuenta** (portal del comprador y mis pedidos)
* Queda terminantemente prohibido alterar, suprimir o reordenar estos 5 botones en cualquiera de las vitrinas o contextos del storefront.

### Regla 6.16: Inicio como Revista Digital Oficial (Erradicación de Blog Paralelo)
* **Revista Digital Unificada en Home:** La vitrina de inicio (`viewHome`), conducida por las secciones modulares administradas en Hub 2 (Editor Visual), constituye la **Revista Digital Oficial** de la marca.
* **Erradicación de Submódulos Residuales:** Queda erradicado cualquier submódulo o pestaña separada de Blog/Revista en el panel administrativo (Hub 2) y cualquier ruta residual. Todo el contenido editorial, lanzamientos de temporada y lookbooks se articulan como secciones modulares dentro del Home.

### Regla 6.17: Vista Explorar y Gestión en Hub 2 como "Páginas de Explorar" con Filtros de Catálogo
* **Página de Explorar (`/explorar`):** Renderiza una cuadrícula de tarjetas interactivas ("botones con imágenes") para el descubrimiento temático de universos, colecciones y curadurías de estilo.
* **Gestión Centralizada en Hub 2:** Se administra desde Hub 2, pestaña *Páginas de Explorar & Navbar*, en la sección formalmente renombrada a **"Páginas de Explorar"** (antes "Páginas institucionales").
* **Configuración de Filtros de Catálogo:** Cada tarjeta permite vincular:
  1. Imagen destacada (URL externa o subida directa de archivo a `public/uploads/pages/`).
  2. Título y descripción SEO.
  3. Filtros directos de catálogo (`filter_universe`, `filter_category`, `filter_tags`), permitiendo que el usuario salte con un clic al catálogo prefiltrado o lea el contenido editorial completo.

### Regla 6.18: Inmutabilidad Canónica del Catálogo
* El catálogo de productos y su infraestructura de vitrinas multiverso permanecen inmutables en su estructura base: navegación fluida por categorías, familias textiles, tallas y stock en tiempo real. Esta arquitectura se formaliza como canónica e inmutable en el proyecto.

### Regla 6.19: Carrito con Persistencia y Funcionalidad de "Guardar para Después"
* **Persistencia Aislada:** El carrito incorpora la funcionalidad de guardar prendas para después sin afectar el proceso de compra activo.
* **Operativa Reactiva:**
  - El botón "Guardar para después" (`data-action="save-for-later"`) traslada el ítem desde el carrito a un almacenamiento secundario persistido en `localStorage` (`multiverse_store_saved_for_later`).
  - Las prendas guardadas no computan en el subtotal, no intervienen en el cálculo de envío gratis ni consumen apartado de inventario en checkout.
  - El usuario puede restaurar cualquier ítem al carrito activo ("Mover al carrito") o eliminarlo definitivamente en cualquier momento.

### Regla 6.20: Iconografía Estricta en SVG Vectorial y Prohibición Absoluta de Emojis
* **Exclusividad SVG:** Todo el código del proyecto (Storefront, PWA, Hubs administrativos, modales, drawers y componentes de UI) emplea **exclusivamente íconos vectoriales SVG**.
* **Prohibición de Emojis en Código Base:** Queda terminantemente prohibido el uso de emojis tipográficos en archivos `.blade.php`, `.js`, `.css` o esquemas de base de datos.
* **Única Excepción:** Textos ingresados directamente por el usuario administrador o comprador final en campos de entrada libre (HTML custom, descripciones personalizadas).

### Regla 6.21: Wishlist Integrada en Drawer de Carrito y Botón de Deseos en Navbar Desktop
* **Ubicación Canónica de la Wishlist:** La lista de deseos ("Mis Deseos") reside integrada en el Drawer de Carrito (`#cartSheet`), accesible tanto cuando el carrito contiene productos como cuando está vacío. Cada ítem de la wishlist permite visualizar imagen, nombre, talla seleccionada y precio, ofreciendo acciones directas para "Al carrito" (añadiendo la prenda y descontándola de la wishlist) o "Eliminar".
* **Acceso Directo en Navbar Desktop:** En la versión de escritorio, junto a la quinteta canónica de navegación, reside el botón de Wishlist (`data-action="open-wishlist"` con ícono de corazón SVG y badge reactivo `#desktopWishlistCount`), el cual despliega el drawer de compras posicionando el visor directamente en la sección de deseos.

### Regla 6.22: Header Móvil Superior Limpio (Filtros Condicionales, Logo Minimalista, Cero Carrito Redundante)
* **Botón de Filtros Condicionado al Catálogo:** El botón de filtros (`#mobileFilterToggleBtn`) se muestra en la barra superior móvil **únicamente cuando se está visualizando la vista de catálogo** (`tabId === 'catalog'`). En las vistas Inicio, Explorar o Cuenta permanece oculto.
* **Logo Minimalista Sin Slogan:** El header móvil muestra exclusivamente el isotipo/logo oficial de la marca (`brand-logo-box`), suprimiendo subtítulos, kickers o slogans para maximizar el área táctil y la limpieza visual.
* **Wishlist con Badge:** Muestra el ícono de corazón SVG con contador reactivo `#mobileWishlistCount`.
* **Cero Carrito en Header Móvil:** Queda estrictamente prohibido colocar el botón de carrito en la barra superior móvil, ya que el carrito reside de manera canónica y ergonómica en el dock de navegación inferior (`bottom-app-dock`).

### Regla 6.23: Erradicación de la Barra Móvil de Páginas Institucionales
* La barra de scroll horizontal `.mobile-institutional-bar` que listaba guías de tallas, cuidados de prendas y páginas institucionales en el header queda totalmente erradicada del layout superior móvil. Toda la navegación institucional y editorial se canaliza a través de la sección **Explorar** del dock de navegación.

### Regla 6.24: Erradicación de la Barra Superior de Universos en Móvil
* La barra táctil horizontal `.universe-selector-bar` (con los botones MARTE, VENUS, TIRESIAS, TERRA) queda completamente eliminada del layout del storefront. La selección, navegación y filtrado por universos se gestiona dentro de las tarjetas temáticas de la vista **Explorar** y dentro del drawer lateral de filtros.

### Regla 6.25: Función de Búsqueda Conmutable Universal (Desktop y Móvil)
* **Botón de Búsqueda Conmutable:** Tanto en versión Desktop (`.nav-actions-desktop`) como en Móvil (`.navbar-mobile`) se ubica un botón de búsqueda con ícono de lupa SVG (`data-action="toggle-search"`).
* **Barra Desplegable Reactiva (`#storeSearchBar`):** Al conmutar el botón, se despliega una barra de búsqueda con campo de entrada en vivo (`#storeLiveSearchInput`), placeholder contextual, botón de limpieza rápida y botón de cerrar.
* **Filtrado en Tiempo Real:** Al escribir cualquier término, el storefront filtra dinámicamente los productos por nombre, categoría, descripción y SKU. Si el usuario no se encuentra en la pestaña de catálogo, el sistema conmuta automáticamente a la vista de Catálogo (`switchDockTab('catalog')`) para presentar los resultados instantáneamente.

### Regla 6.26: Tarjetas de Explorar como Botones de Imagen Única con Overlay y Degradados
* **Comportamiento Integral de Botón:** La visualización de la página de Explorar (`/explorar`), tanto en su versión móvil como desktop, se estructura con cada tarjeta funcionando íntegramente como un **botón interactivo**.
* **Composición Visual Unificada:** Cada botón/tarjeta presenta una **sola imagen completa de fondo** complementada con una capa de **overlay con capacidad de degradados configurables** (`overlay_gradient`), eliminando la separación clásica entre contenedor de imagen superior y cajón de texto inferior.
* **Textos y Acciones Sobrepuestas:** Los textos (título en tipografía de cabecera, descripción y badge de acción interactiva) se renderizan sobrepuestos en capas superiores (`z-index: 3`) sobre el degradado, garantizando contraste visual y legibilidad óptima.
* **Interacción Directa:** 
  - Si la página tiene configurados filtros de catálogo (`filter_universe` o `filter_category`), el clic sobre el botón ejecuta de forma instantánea el filtrado en caliente del catálogo y conmuta a la vista correspondiente. Si además cuenta con contenido editorial, se preserva un enlace complementario hacia `/pagina/{slug}`.
  - Si no posee filtros de catálogo, el botón conduce directamente a la lectura del documento o página institucional (`/pagina/{slug}`).
* **Nomenclatura y Gestión en Hub 2:** La administración de estas tarjetas reside en Hub 2 (Portal Store), pestaña titulada canónicamente **"3. Páginas de Explorar"** (suprimiendo definitivamente `"& navbar"`), en la sección **"Páginas de Explorar"**. Tanto el modal de creación (`createPageModal`) como el de edición (`editPageModal`) disponen del control para configurar el preset o gradiente de overlay (`overlay_gradient`).

### Regla 6.27: Proporción Estricta 4:5 en Grid de Botones de Explorar
* **Aspect Ratio 4:5 Mandatorio:** Todos los botones dentro de la página de Explorar se exhiben dentro de un grid con proporción fija 4:5 (`aspect-ratio: 4 / 5;`).
* **Diseño Responsivo:** 
  - En versión móvil: El grid se distribuye en 2 columnas equilibradas (`grid-template-columns: repeat(2, 1fr); gap: 12px;`), maximizando el aprovechamiento vertical del viewport táctil.
  - En versión desktop: El grid se adapta fluidamente mediante columnas automáticas (`grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;`).

### Regla 6.28: Ordenamiento Drag & Drop en Páginas de Explorar y Tabla Limpia (Sin Columnas Obsoletas)
* **Drag & Drop Mandatorio:** La sección **Páginas de Explorar** en Hub 2 opera 100% mediante reordenamiento **Drag & Drop** en tiempo real (`pagesSortableBody`).
* **Tabla Limpia:** Queda terminantemente prohibido mostrar columnas redundantes u obsoletas de visibilidad individual como `Desktop`, `Móvil` o `Footer` con números independientes (`#1, #2`), ya que las tarjetas de Explorar se ordenan como una secuencia única unificada.
* **Persistencia en Base de Datos:** La API persiste el orden secuencial de forma asíncrona mediante `POST /admin/tienda-cms/paginas/reorder` actualizando el campo `navbar_order`.

### Regla 6.29: Overlay, Degradados 100% Personalizables y Contraste Garantizado de Texto
* Las tarjetas de Explorar cuentan con controles completamente personalizables para el degradado del overlay:
  1. **Color de Inicio (Superior):** Selector cromático hexadecimal y slider de opacidad (0% a 100%).
  2. **Color de Fin (Inferior):** Selector cromático hexadecimal y slider de opacidad (0% a 100%).
  3. **Presets Rápidos:** Opciones preconfiguradas con combinaciones estéticas optimizadas (Noir, Vino, Índigo, Esmeralda, Ámbar, Alto Contraste).
  4. **Color del Texto de la Tarjeta (`text_color`):** Selector cromático para el texto, permitiendo garantizar contraste absoluto sobre cualquier fotografía o fondo para evitar que los títulos o descripciones se pierdan.
  5. **Live Preview en Tiempo Real:** Ambos modales de Hub 2 (creación y edición) renderizan una tarjeta miniatura interactiva en proporción 4:5 que simula la legibilidad y contraste con la configuración activa.

### Regla 6.30: Carrusel Automático de Imágenes y Conmutación Dinámica desde Catálogo
* Cada tarjeta de Explorar admite la incorporación de una o más imágenes:
  1. **Carrusel Automático:** Si se configuran dos o más imágenes personalizadas, estas circulan de manera automática en carrusel suave con animación crossfade cada 3.5 segundos, acompañadas de indicadores/dots sutiles.
  2. **Conmutación Dinámica desde Catálogo (`image_source_mode = 'catalog'`):** El usuario puede conmutar la fuente de imágenes para que el carrusel extraiga automáticamente las fotografías de las prendas activas en el catálogo que coincidan con los filtros de universo y categoría vinculados a la tarjeta.

### Regla 6.31: Soporte Integral de Dark Mode, Light Mode y Sistema en Todo el Proyecto
* Todo el proyecto cuenta con soporte oficial y nativo para tres modalidades de tema visual:
  1. **Modo Claro (`light`):** Paleta blanca minimalista de alto brillo.
  2. **Modo Oscuro (`dark`):** Paleta oscura de lujo contemporáneo (`#09090b` / `#121215`), superficies satinadas y contraste nocturno.
  3. **Modo Sistema (`system`):** Detección automática y sincronización reactiva en tiempo real con la preferencia del sistema operativo (`prefers-color-scheme`).
* **Ubicación Canónica de la Configuración:**
  - Dentro del **Drawer de Cuenta** (`#accountSheet`), reside la pestaña especializada **`Configuración`** (`#accountTabSettings`), donde se encuentra el selector de apariencia visual y el **Centro de Notificaciones Web Push PWA** (`#accountPushCenter`).
  - En el **Navbar de Cuenta** (tanto en desktop como en móvil), se integra un botón de conmutación rápida de tema (`data-action="toggle-app-theme-quick"`).
* **Paridad en Portal Administrativo:** El Portal Panel (RevOps) cuenta con sincronización inmediata del tema y botón conmutador en la barra de herramientas superior (`.top-actions`).
* **Prevención de Parpadeo (Anti-FOUC):** Todos los layouts implementan un script síncrono inline en el `<head>` antes de cualquier renderizado visual para aplicar la clase `.dark` o `.light` instantáneamente según `localStorage.getItem('app_theme')`.

### Regla 6.32: Contraste Riguroso y Accesibilidad Universal en Modo Dark y Light
* **Mandato de Contraste Absoluto:** Todo el soporte visual del proyecto (tanto en Modo Oscuro como en Modo Claro) debe garantizar rigurosamente el contraste nítido, legible y accesible (estándar WCAG AA o superior) en textos, bordes y la totalidad de los elementos de la interfaz.
* **Contraste Tipográfico:**
  - **Modo Claro (`light`):** El texto principal utiliza tonalidades oscuras puras (`#09090b` / `#000000`), el texto secundario `#3f3f46` y el texto atenuado o de metadatos `#52525b` (garantizando ratio mínimo de 4.5:1 contra fondos blancos o marfiles). Queda estrictamente prohibido utilizar grises claros o desteñidos (`#9ca3af`, `#a1a1aa`, `#8e8e93`) que comprometan la legibilidad sobre fondos claros.
  - **Modo Oscuro (`dark`):** El texto principal utiliza blanco brillante (`#f8fafc` / `#ffffff`), el texto secundario `#cbd5e1` (slate-300) y el texto atenuado `#94a3b8` (slate-400), garantizando un contraste superior a 5.5:1 contra superficies oscuras (`#09090b`, `#131317`, `#1e293b`). Queda terminantemente prohibido utilizar grises oscuros o apagados (`#52525b`, `#64748b`, `#71717a`) sobre fondos oscuros.
* **Delimitación y Contraste de Bordes:**
  - Todas las tarjetas (`.product-card`, `.cart-item-card`, `.panel`, `.card`), inputs, tablas, drawers y modales deben disponer de un borde perimetral explícito (`--border-subtle`, `--border-medium`, `--line`) que delimite su silueta y evite el "efecto pantalla plana" o la fundición de elementos con el fondo.
  - En Modo Oscuro, los bordes sutiles deben ser al menos `#2e2e3c` / `#334155` y los bordes medios `#49495c` / `#475569`.
* **Controles, Formularios y Estados Interactivos:**
  - Inputs, textareas y selectores deben poseer fondos contrastados (`--bg-tertiary` o `#0f172a` en dark; `#ffffff` en light), bordes claramente definidos, placeholders legibles (`#94a3b8` en dark; `#52525b` en light) y anillos de foco visibles (`outline` / `box-shadow` de 2px en `--border-focus`).
  - Badges de estado, pills de selección, botones primarios/secundarios y encabezados de tabla deben cumplir con combinaciones de alto contraste cromático y legibilidad instantánea.

### Regla 6.33: Ciclo de Vida y Curaduría de Productos Scrapeados (VS Code, Ingesta en Hub 3 y Badge "Drop Bajo Pedido")
* **Depuración e Indexación en Entorno de Desarrollo (VS Code):** Cada vez que se da de alta un nuevo catálogo scrapeado o dataset de proveedor externo, la limpieza, normalización taxonómica, saneamiento de codificación UTF-8 / Mojibake y depuración de datos sucede dentro del entorno de desarrollo de Visual Studio Code mediante scripts técnicos especializados antes de cualquier inyección.
* **Ingesta Oficial en Bandeja de Curaduría Previa (Hub 3 / Hub 4):** Una vez validado técnicamente el dataset y recibida la luz verde, la ingesta entra a la *Bandeja de Curaduría Previa de Scraping* (Staging Area). En esta bandeja el equipo operativo revisa, ajusta márgenes comerciales, asocia variantes y da de alta oficial la prenda dentro del inventario activo del sistema.
* **Badge Obligatorio "Drop Bajo Pedido":** Todos los productos originados de scraping o esquemas de dropshipping mayorista portan obligatoriamente el distintivo **"Drop Bajo Pedido"** tanto en el panel de inventario como en el storefront público (tarjetas del catálogo, grids modulares, vistas de inicio y fichas de producto PDP), distinguiéndolos nítidamente del "Stock Físico" de confección local o stock propio.
* **Alta Rápida de Colores y Telas en Modal de Curaduría:** Durante la curaduría en la bandeja de scraping:
  - El selector de **Color / Tornasol** dispone de un botón interactivo `+ Dar de alta color` para registrar un nuevo color o tornasol en caliente (con nombre, tipo sólido/tornasol y selector de color) y asignarlo de inmediato a la prenda; de lo contrario, se selecciona del catálogo de colores preexistentes.
  - El selector de **Tipo de Tela** dispone de un botón interactivo `+ Dar de alta tela` para registrar un nuevo textil/composición en caliente y asignarlo de inmediato a la prenda; de lo contrario, se selecciona del dropdown existente de fibras de la plataforma.

### Regla 6.34: Creación Jerárquica de Filtros desde Hub 4 (Ajustes > Categorías & Familia)
* **Creación Centralizada de Filtros:** Los administradores disponen de la capacidad de **crear nuevas categorías y familias taxonómicas** directamente desde los ajustes de Hub 4, pestaña **Categorías & Familias** (`activeSettingsTab === 'categories'`).
* **Estructura Jerárquica Taxonómica:** Cada categoría se crea y define asignando su nombre, nivel jerárquico (categoría raíz / familia principal, o subcategoría child asignada a un padre) y su descripción taxonómica para búsqueda y catalogación.
* **Operatividad Drag & Drop y Gestión Completa:** El árbol de categorías opera con reordenamiento visual intuitivo mediante **Drag & Drop** y dispone de acciones completas para crear, editar, activar/desactivar en e-commerce, archivar/restaurar y eliminar registros.
* **Sincronización con Storefront y Hub 2:**
  - Estas familias alimentan dinámicamente los filtros del catálogo de la tienda PWA.
  - La pestaña *Filtros de Catálogo* de Hub 2 se utiliza para organizar visualmente los filtros mediante **Drag & Drop** en tiempo real.
  - Asimismo, alimenta el selector de categorías al crear o editar tarjetas en *Páginas de Explorar* de Hub 2, permitiendo seleccionar con precisión qué filtro se le aplicará al botón de la sección Explorar. Al hacer clic en dicho botón dentro del storefront (`/explorar`), el catálogo se despliega automáticamente con ese filtro preaplicado.

### Regla 6.35: Filtros Facetados Extendidos en Storefront
* **Facetas Múltiples Obligatorias:** En el storefront (drawer de filtros `#filterDrawer` y motor de consulta en caliente), son formal y funcionalmente considerados filtros las siguientes facetas especializadas:
  1. **Categorías & Familias:** Píldoras y árbol interactivo alimentado dinámicamente desde Hub 4.
  2. **Proveedores / Fabricantes:** Filtro por marca o taller mayorista alimentado desde Ajustes de Hub 4.
  3. **Curva de Tallas Anatómicas:** Botones interactivos seleccionables por talla (CH, M, G, XG, 2XL, 3XL).
  4. **Telas & Fibras Textiles:** Filtro por composición textil (Algodón Pima, Modal Seamless, Spandex, Malla/Mesh, Microfibra Velvet, Bambú Eco, Seda/Satín).
  5. **Colores & Tornasoles Iridiscentes:** Muestras de color interactivas (Sólidos y Tornasoles como Galaxia Holo y Aurora Boreal).
  6. **Volúmenes de Perfumería:** Filtros por presentación (30ml Travel, 50ml EdP, 100ml Regular, 200ml Refill).
  7. **Tonos Cosméticos & Shading:** Filtros de pigmentación y maquillaje (Tono 01 Claro, Tono 02 Medio, Tono 03 Bronce, Tono 04 Profundo).
* Todas las facetas interactúan sinérgicamente con el buscador en tiempo real, el selector de universos y el botón de restablecimiento total ("Limpiar filtros").

### Regla 6.36: Proveedores como Dimensión de Filtrado Transversal (Hub 4 $\leftrightarrow$ Hub 2 $\leftrightarrow$ Storefront)
* **Proveedores como Filtros del Sistema:** Dentro de los ajustes de Hub 4, los nombres de los proveedores registrados forman formalmente parte del universo de filtros.
* **Incidencia Transversal:**
  - **Hub 2 (Filtros de Catálogo y Páginas de Explorar):** Los proveedores se reflejan en el gestor de filtros y dentro de los modales de creación y edición de *Páginas de Explorar*, permitiendo vincular un botón de Explorar directamente al catálogo filtrado por un fabricante específico.
  - **Storefront:** Se presentan como una faceta seleccionable dentro del drawer de filtros para que el comprador pueda filtrar prendas por proveedor o marca asociada.

### Regla 6.37: Formato de Árbol Plegable con Checkboxes en Filtros Facetados (Storefront)
* **Estructura en Árbol con Checkboxes:** Los filtros facetados extendidos del storefront (`#filterDrawer`) se presentan organizados en **formato de árbol jerárquico** (padre/hijo, idéntico al esquema de Hub 4), con casillas de verificación (**checkboxes**) para selección precisa y múltiple.
* **Pestañas Colapsables y Acordeones:** Cada grupo o dimensión de filtros cuenta con cabeceras interactivas que se **contraen y expanden suavemente** (acordeones independientes), optimizando el aprovechamiento vertical del drawer en pantallas móviles y permitiendo al usuario abrir exclusivamente las secciones de su interés.

### Regla 6.38: PDP — Botón de Retorno Rápido y Gatillo Conmutador de Filtros
* **Navegación de Retorno:** La cabecera de cada ficha independiente de producto (`/productos/{slug}`) incorpora obligatoriamente un **botón para volver atrás**, que restituye de inmediato la vista anterior del catálogo o sección previa sin perder el estado de desplazamiento.
* **Gatillo de Filtros:** Dispone asimismo de un **botón que conmuta/despliega el drawer de filtros** directamente desde la ficha de producto, permitiendo al usuario continuar refinando su exploración del catálogo sin requerir regresar al inicio.

### Regla 6.39: Exhibidor Visual de Color Indexado en Catálogo y PDP
* **Muestras de Color en Tarjetas y Fichas:** Tanto las tarjetas de producto en los grids del catálogo (`.product-card`) como la página independiente de producto (PDP) exhiben un **exhibidor visual de color (muestras cromáticas / swatches)** correspondiente a los tonos y tornasoles con los cuales el producto fue indexado en la base de datos.
* **Feedback Inmediato:** Cada swatch despliega su color sólido o degradado tornasol exacto, ofreciendo comprensión visual instantánea de la gama disponible.

### Regla 6.40: Soporte Multi-Color por Modelo y Conmutación Dinámica de Imágenes
* **Multi-Color por Prenda:** Cada modelo puede contener 1 o más variantes de color, incidiendo transversalmente en Hub 4 (gestión y SKU), Hub 2 (vitrinas) y Storefront (compra D2C).
* **Conmutación Reactiva de Imágenes en PDP:** Cuando el comprador hace clic en cualquiera de las variantes de color en la ficha de producto, la galería principal de imágenes conmuta dinámicamente mostrando las fotografías asociadas específicamente al color seleccionado.
* **Configuración en Alta y Edición:** Esta vinculación entre fotografías y variantes de color se define de manera obligatoria desde el modal de alta del producto y se gestiona en sus modales de edición.

### Regla 6.41: Miniaturas Visuales de Producto en Carrito de Compras (Storefront)
* **Miniaturas en Carrito:** El Drawer de Carrito (`#cartSheet`) y la vista previa de compra muestran visiblemente la **imagen miniatura de cada producto** agregado, junto con su título, variante de color seleccionada, talla y precio unitario.
* **Garantía Visual:** El comprador valida con certeza fotográfica la prenda exacta antes de avanzar a la pasarela de pago.

### Regla 6.42: Formulario de Cupón de Descuento Confinado Exclusivamente al Paso 1 del Checkout
* **Despeje del Carrito Pasivo:** Queda prohibido saturar el drawer inicial de carrito o los pasos de pago con formularios persistentes de cupones.
* **Ubicación Exclusiva en Paso 1:** El formulario y campo de texto para aplicar códigos de descuento se muestra **única y exclusivamente dentro del Primer Paso del Checkout** (`currentCheckoutStep === 1`), permitiendo aplicar el descuento y recalcular el total neto previo al llenado de datos de envío y selección de pasarela.

### Regla 6.43: Miniaturas en Inventario y Asignación de Color a Imágenes en Hub 4
* **Miniatura en Tabla de Inventario:** Cada fila del catálogo operativo y lista de inventario en Hub 4 renderiza la fotografía miniatura del producto.
* **Edición y Mapeo en Modal de Producto:** Al abrir el modal de edición de producto en Hub 4, se exhibe la galería de imágenes del artículo, disponiendo de controles para añadir, editar o remover fotografías, así como **asignar la variante de color específica a la que pertenece cada imagen**.

### Regla 6.44: Curaduría de Scraping con Asignación de Variabilidad de Color a Imágenes
* **Curaduría Visual en Bandeja de Scraping:** En la *Bandeja de Curaduría Previa de Scraping* (Hub 3 / staging area), el protocolo de revisión humana incluye obligatoriamente la asociación de la variabilidad de color del producto a cada fotografía scrapeada.
* **Garantía de Entrada al Catálogo:** Ninguna prenda scrapeada ingresa al inventario activo sin que sus imágenes queden debidamente enlazadas a sus respectivos colores, asegurando que la conmutación de fotos por color en el storefront funcione de forma óptima desde el primer instante.

### Regla 6.45: Estructura en Árbol de Proveedor-Marcas y Alta Manual con Información de Contacto y Fiscal en Hub 4 (Ajustes)
* **Visualización Jerárquica en Árbol:** En los ajustes de Hub 4 (pestaña Proveedores & Scrapers), los proveedores deben presentarse en **formato de árbol desplegable / jerárquico** (`Proveedor ──┬── Marca A`, `└── Marca B`) para explicar con absoluta claridad las marcas comerciales que se desprenden o son comercializadas por cada uno de ellos. Cada nodo de proveedor cuenta con chevron interactivo de expansión/colapso, insignias de conteo de marcas y controles masivos de "Expandir Árbol" / "Contraer".
* **Alta Manual Completa (Contacto y Fiscal):** Dado que no todos los proveedores provienen de scrapeo automatizado, el sistema debe habilitar el registro y edición manual completa capturando:
  1. **Información de Contacto y Operativa:** Nombre comercial, razón social, tipo de proveedor/taller, origen (Manual vs Scraping), ciudad, persona de contacto operativo, teléfono/WhatsApp, email de pedidos, sitio web o catálogo digital, lead time prometido en días a rampa, términos comerciales de pago y notas de maquila.
  2. **Información Fiscal y Facturación:** RFC / Tax ID / NIF con formateo automático en mayúsculas, razón social fiscal completa, régimen fiscal (SAT / tributario), domicilio fiscal completo (calle, número exterior/interior, colonia, municipio/alcaldía), código postal fiscal (CP) y correo electrónico especializado para facturación electrónica CFDI / comprobantes.
  3. **Marcas Comerciales Desprendidas:** Interfaz interactiva de chips/etiquetas para asociar, desprender y remover marcas al vuelo mediante atajo de teclado (`Enter`) o botón de acción, persistiendo en el modelo `Supplier` (columna `brands` JSON y accessor `brandsTree`) para su propagación inmediata a los filtros faceteados de Hub 2 y del storefront.

### Regla 6.46: Gestión Integral de Marcas Comerciales (CRUD Completo, Ficha Fiscal Reactiva y Sincronización Transversal de Filtros)
* **Gestión Autónoma y CRUD Completo de Marcas en Hub 4:** Dentro de los ajustes de Hub 4 (pestaña Marcas Comerciales `activeSettingsTab === 'brands'`), los administradores disponen de la capacidad de dar de alta, editar, eliminar, archivar, restaurar y alternar el estado activo de marcas comerciales, así como asignarlas libremente o desvincularlas de proveedores. La interfaz cuenta con KPIs de marcas activas, archivadas y total de SKUs, buscador en tiempo real, filtros por estado y proveedor, y modal autónomo (`brandModalOpen`). Desde el árbol de proveedores (`activeSettingsTab === 'suppliers'`), cada tarjeta dispone de acceso rápido para "+ Desprender Nueva Marca", editar y desvincular marcas en caliente.
* **Reactividad Inmediata en Botón "Ficha / Fiscal":** El botón "Ficha / Fiscal" presente en cada nodo de la tabla y árbol de proveedores debe abrir de forma reactiva e instantánea el modal de proveedor conmutando directamente a la pestaña fiscal (`openProviderFiscalModal(prov)`). El modal debe garantizar un `z-index` de elevación (`z-[120]` con `style="z-index: 120 !important;"`) y erradicar cualquier propiedad `display: none` residual o bloqueo por superposición en el DOM.
* **Sincronización Transversal de Funcionalidades Dependientes (Filtros en Cascada):** La tabla maestra de marcas (`brands`) y su relación con proveedores impacta obligatoriamente y en tiempo real a:
  1. **Hub 4 (Inventario):** Filtro `brandFilter` en la barra de herramientas del catálogo, alimentado dinámicamente por `availableBrandNames`, filtrando la tabla de productos de forma combinada con proveedores, categorías y stock.
  2. **Hub 2 (Tienda & CMS):** Selectores de Proveedor y Marca Comercial en los modales de creación y edición de vitrinas de *Páginas de Explorar* (`store.blade.php`), permitiendo direccionar al comprador al catálogo filtrado por marca o fabricante.
  3. **Storefront PWA:** Estructura de árbol faceteado colapsable (`<details class="filter-tree">`) en el drawer de filtros (`#filterDrawer`), con casillas de verificación (**checkboxes**) indentadas que agrupan a cada proveedor con sus respectivas marcas comerciales desprendidas, sincronizado con el motor reactivo de coincidencia (`site.js`).

---

## 7. Reglas de Merchandising, CMS y Motor de Secciones Modulares (Hub 2)

### Regla 7.1: Bifurcación Mutuamente Excluyente del Hero (Cero Duplicados)
* El constructor de Hero del CMS debe ofrecer conmutación estricta entre dos modos excluyentes:
  1. **Modo A (Visual Estructurado):** Título, subtítulo, badges, botones CTA dinámicos con selector de rutas, degradados overlay y fondo administrado.
  2. **Modo B (Código Custom Scoped):** HTML, CSS y JavaScript libre para campañas especiales.
* El motor Blade debe renderizar únicamente el modo seleccionado, erradicando cualquier duplicidad del banner en la tienda pública.

### Regla 7.2: Regla de Oro del Catálogo vs Contenido Custom
* **Secciones de Catálogo (Tarjetas de Productos, Categorías, Familias Textiles, Tags):** Son 100% dinámicas y se alimentan de las taxonomías de Hub 4. Queda prohibido inyectar áreas de HTML/CSS libre en este tipo de secciones.
* **Secciones de Contenido (Hero, Carruseles, Banners Editoriales, Modales Promocionales, Bloques CTA):** Permiten edición de código custom con aislamiento scoped.

### Regla 7.3: Confinamiento de Herramientas Especializadas
* El Gestor Drag & Drop de Secciones Modulares del Home (`#sectionsTable`) debe ubicarse **exclusivamente dentro de la pestaña Editor Visual (Tab 1)**, evitando su presencia redundante o distractora en las pestañas de contenido, páginas, blog o filtros.

### Regla 7.4: Generación Asistida de Rutas y Anclas Modulares
* Cada sección modular genera automáticamente un ancla DOM identificada (`#section-{id}`).
* El panel administrativo debe exponer botones de copiado rápido en un clic para anclas de sección y slugs de páginas institucionales (`/pagina/{slug}`), así como selectores desplegables que permitan insertar estas rutas en los botones del Hero sin necesidad de tipeo manual.

---

## 8. Reglas de Seguridad, Sanitización y Privacidad

### Regla 8.1: Sanitización de Entradas en Backend contra XSS
* Cualquier contenido HTML o CSS proveniente de formularios administrativos o campos `custom_html` / `custom_css` debe ser sanitizado en el backend antes de persistirse o renderizarse en Blade, bloqueando la inyección de scripts no autorizados o exploits maliciosos.

### Regla 8.2: Aislamiento Estricto por Rol (RBAC)
* La aplicación aplica segregación de vistas y datos según el rol del usuario autenticado:
  - Super Admin y Directores: Acceso irrestricto a métricas financieras, P&L y auditorías.
  - Almacén y Mesa de Empaque: Acceso operativo a picking, packing y guías, con restricción total de datos de márgenes financieros o costos globales.
  - Aliados y Proveedores (Extranet B2B): Acceso aislado únicamente a sus órdenes de compra, minutas de discrepancia y reportes de remisión.

### Regla 8.3: Protección Sanitaria y Normativa PROFECO
* En prendas íntimas (boxers, tangas, jockstraps, bralettes, binders) y cosméticos, la política de no devolución una vez violado el precinto de higiene original de fábrica debe reflejarse en:
  1. La ficha de producto (PDP).
  2. El proceso de checkout antes del pago.
  3. Los avisos impresos en etiquetas térmicas y cajas de embalaje expedidas por Almacén.

---

## 9. Reglas de Calidad, Pruebas Automatizadas y Validación CDP

### Regla 9.1: Cero Pruebas Rotas (100% Passing Test Suite)
* Cada módulo intervenido debe contar con pruebas automatizadas Feature y Unit con PHPUnit.
* Queda prohibido consolidar commits o dar por terminada una tarea si la suite de pruebas presenta fallos (`failures`) o errores de sintaxis (`errors`). Todo commit debe validar el 100% de aserciones en verde.

### Regla 9.2: Validación en Navegador Real con Chrome DevTools Protocol (CDP Headless)
* Cuando se reporten incidentes visuales, fallas de modales o problemas de interacción reactiva, el diagnóstico debe respaldarse mediante pruebas directas en Chrome Headless a través de CDP para inspeccionar:
  - Dimensiones reales computadas (`offsetHeight`, `offsetWidth`, `getBoundingClientRect`).
  - Visibilidad efectiva (`computedDisplay`, `computedVisibility`, `opacity`).
  - Cadena completa de ancestros en el DOM (`parentElement` chain).

---

## 10. Reglas de Documentación Viva, Manuales y Compilación PDF

### Regla 10.1: La Regla del Tríptico Documental
Toda especificación arquitectónica o manual de flujos debe mantenerse sincronizado en tres formatos indisolubles:
1. **Especificación en Markdown:** Documento fuente para desarrolladores (`docs/*_SPEC.md` o `docs/PROJECT_RULES.md`).
2. **Representación Visual en HTML:** Archivo estructurado con tipografía Plus Jakarta Sans, paleta corporativa Slate/Indigo, diagramas de flujo y hojas de estilo optimizadas para impresión (`@media print`, `page-break-before: always`).
3. **Compilado Oficial en PDF:** Versión final imprimible generada directamente desde el HTML mediante Chrome headless:
   ```bash
   chrome.exe --headless=new --disable-gpu --no-sandbox --print-to-pdf="documento.pdf" "documento.html"
   ```

### Regla 10.2: Registro Continuo en `PENDING_TASKS.md`
Cualquier nuevo requerimiento, punto ciego detectado, incidencia o tarea pendiente debe registrarse de inmediato en [`docs/PENDING_TASKS.md`](file:///c:/laragon/www/mi-tienda-online/docs/PENDING_TASKS.md), asignándole un identificador unívoco de tarea (`TASK-XXX-NN`), prioridad operativa, estado y diagnóstico detallado.

---

## 11. Reglas de Control de Versiones y Gestión de Commits

### Regla 11.1: Commits Atómicos y Semánticos
* Los commits de Git deben ser atómicos, agrupando únicamente los archivos relacionados con la tarea o corrección en curso.
* Se debe emplear la convención de mensajes semánticos:
  - `feat(modulo): descripción clara (TASK-ID)` para nuevas funcionalidades.
  - `fix(modulo): descripción clara (TASK-ID)` para correcciones de bugs o errores de renderizado.
  - `docs(modulo): descripción clara` para actualización de especificaciones y manuales.
  - `refactor(modulo): descripción clara` para mejoras estructurales sin alteración funcional.
* Queda prohibido realizar commits genéricos como *"cambios"*, *"avances"* o *"fix"*. Todo commit debe declarar el alcance de la modificación y el ID de tarea asociado.

---

## Resumen de Cumplimiento Inmediato

| Dominio | Regla Clave | Mecanismo de Verificación |
| :--- | :--- | :--- |
| **Ingeniería** | Vertical Slice End-to-End | Cero mocks; persistencia real en MySQL y modelos Eloquent |
| **Codificación** | UTF-8 Universal Obligatorio | `<meta charset="utf-8">`, MySQL `utf8mb4_unicode_ci`, cabeceras HTTP UTF-8 |
| **Contrato Backend** | Sincronización `BACKEND_INTEGRATION_SPEC.md` | Esquemas JSON, FormRequests y migraciones documentados |
| **Arquitectura** | Single Inventory Pool Multiverso | Stock centralizado en Hub 4; vitrinas desacopladas |
| **Workflows** | Flujos de la Aplicación sin Roles | Especificación centrada en interconexión de módulos y datos |
| **Estructura DOM** | Aislamiento de Paneles y Modales | Nodos hermanos bajo `#storeAppRoot` (`depth = 2`); paridad de `<div>` |
| **UI / PWA** | Full-width Breakout, Cache Busting & Resize Solo Vertical | `100vw` sin overflow; `?v=filemtime`; `textarea { resize: vertical !important; }` |
| **PDP Storefront** | Cero WhatsApp & Galería Swipe | Sin botón wa.me en `/productos/{slug}`; modal lightbox interactivo con swipe táctil |
| **Filtros Storefront**| Drawer Lateral Izquierdo | Off-canvas desde `left: 0`, backdrop modal, cero acordeones invasivos |
| **Checkout & Stock** | Apartado Temporal Exclusivo en Checkout | Timer 5m en checkout; carrito nunca se vacía por timeout; badge "Ya no hay existencias" |
| **Pasarelas de Pago**| Cero SPEI / Tarjetas & OXXO Pay | Tarjetas crédito/débito y OXXO Pay 48h; cero CLABE ni validación manual |
| **Storefront Navbar**| Navbar Sticky Permanente & Cinta Banner | `position: sticky; top: 0; z-index: 999;` en scroll; editor de marquesina en Hub 2 |
| **Navbar Canónico** | 5 Botones Oficiales (Desktop/Móvil) | Inicio, Explorar, Catálogo, Carrito y Cuenta permanentes |
| **Revista Digital** | Home Modular = Revista Oficial | Cero blogs separados; Home dinámico orquestado desde Hub 2 Tab 1 |
| **Explorar & Filtros** | Botones con Imágenes y Filtros Directos | Ruta `/explorar`, gestión en Hub 2 Tab 3 ("Páginas de Explorar"), filtros de universo/categoría |
| **Explorar 4:5 & Overlay** | Botones de Imagen Única con Degradado | Tarjetas 4:5 como botones interactivos; degradados configurables; Hub 2 Tab 3 formalmente "Páginas de Explorar" |
| **Catálogo** | Inmutabilidad Canónica | Multiverso con stock y facetas inmutable en estructura base |
| **Carrito Avanzado** | Guardar para Después | Persistencia en `localStorage`, cero impacto en checkout ni subtotal |
| **Iconografía** | Exclusividad SVG & Cero Emojis | Vectoriales SVG en 100% de vistas/scripts; prohibición absoluta de emojis en código |
| **Páginas Institucionales**| Gestión Dual Desktop & Móvil + CRUD | Checkboxes independientes `desktop`/`móvil`; modal `#editPageModal` (`depth = 2`); activar/pausar, archivar, eliminar |
| **SEO Storefront** | Optimización Integral & Sitemap XML | OpenGraph, Twitter Cards, JSON-LD (`Organization`, `WebSite`, `Product`), `/sitemap.xml` dinámico |
| **Wishlist & Carrito** | Wishlist en Drawer & Desktop Navbar | Sección Mis Deseos en `#cartSheet`; botón corazón en navbar desktop con badge `#desktopWishlistCount` |
| **Header Móvil** | Header Limpio Sin Slogan & Sin Carrito Redundante | Solo logo de marca; botón de filtros condicionado a tab catálogo; corazón de wishlist; cero carrito |
| **Páginas & Universos Móvil** | Barras Redundantes Erradicadas | Eliminadas `.mobile-institutional-bar` y `.universe-selector-bar`; navegación en Explorar y Drawer |
| **Búsqueda Conmutable** | Búsqueda Reactiva Universal | Botón lupa en desktop y móvil; barra desplegable `#storeSearchBar` con filtrado en tiempo real |
| **CMS Hub 2** | Hero Excluyente & Regla de Oro | Cero duplicados; catálogo sin custom code; editor visual en Tab 1 |
| **Seguridad** | Sanitización XSS & RBAC | Limpieza en backend de inputs HTML/CSS; aislamiento de métricas |
| **Calidad** | 100% Tests Green & Auditoría CDP | PHPUnit sin errores; pruebas en Chrome Headless |
| **Modo Dark / Light**| Contraste Universal WCAG AA | Textos nítidos (>5.5:1), bordes perimetrales visibles, cero fondos oscuros con texto apagado |
| **Categorías Hub 4** | Creación y Jerarquía Drag & Drop | Alta de categorías raíz y subcategorías en Hub 4 Ajustes |
| **Proveedores Filtro** | Dimensión de Filtrado Transversal | Proveedores como filtro en Hub 4, Hub 2 (Explorar) y Storefront |
| **Filtros Facetados** | Árbol con Checkboxes y Acordeón | Formato de árbol plegable que se contrae/expande en `#filterDrawer` |
| **PDP Navegación** | Botón Volver & Gatillo de Filtros | Retorno rápido y apertura de drawer de filtros directo desde PDP |
| **Swatches de Color** | Exhibidor Visual de Color Indexado | Muestras de color en tarjetas de catálogo y ficha de producto PDP |
| **Variante de Color** | Conmutación Dinámica de Imágenes | Fotos conmutan al color seleccionado en PDP; asignado en alta/edición |
| **Miniaturas Carrito** | Fotografías en Drawer de Carrito | Miniaturas visibles por cada prenda en `#cartSheet` y checkout |
| **Cupón Descuento** | Confinado al Paso 1 del Checkout | Formulario de código exclusivo en paso 1; cero saturación en carrito |
| **Inventario & Color** | Miniaturas y Mapeo Foto-Color | Miniatura en tabla de Hub 4 y asignación de color en modal de edición |
| **Scraping & Color** | Curaduría de Imágenes por Color | Mapeo obligatorio imagen-color en bandeja de curaduría previa |
| **Proveedores Árbol & Fiscal** | Árbol Proveedor-Marcas y Alta Manual | Visualización en árbol, pestañas contacto/fiscal/marcas, botón reactivo Ficha Fiscal |
| **Marcas Comerciales** | CRUD Autónomo & Filtros Transversales | Alta, edición, archivo y borrado de marcas en Hub 4; filtros en Hub 4, Hub 2 y Storefront |
| **Documentación** | Tríptico Documental & Compilación PDF | Markdown + HTML estilizado + PDF vía Chrome headless |
| **Git** | Commits Atómicos Semánticos | Mensajes estructurados con ID de tarea formal |


