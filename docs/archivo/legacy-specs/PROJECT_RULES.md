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

### Regla 3.3 (Inamovible de Sistema): Sincronización Automática, Reactiva y Omnicanal de Toda la Información del Producto (Hub 4 <-> Hub 2 <-> Storefront)
* **Principio Inamovible de Arquitectura:** Esta directiva no es una regla negociable ni secundaria, sino un axioma inamovible de la arquitectura de la plataforma. **Toda la información del producto sin excepción** —incluyendo familias, categorías, subcategorías, colores (sólidos, tornasoles, multicolores o degradados), curvas de tallas, precios de venta (PVP), costos, descuentos, existencias de inventario por talla, existencias totales en Kardex, descripciones comerciales, bullets, marcas y telas/composiciones textiles— ya sea que se modifique desde el panel de Ajustes de Hub 4, el Workstation de Alta de Producto, el Drawer de Edición Rápida, o la bandeja de curaduría de Scraping, **DEBE sincronizarse y propagarse automáticamente, de inmediato y sin intervención manual** entre Hub 4 (Kardex e inventario), Hub 2 (CMS & Storefront Controller), la base de datos relacional y el Storefront PWA.
* **Gobernanza de Precios, Costos y Variantes:** Al modificar el precio de venta (`pvp` o `price`), costo (`cost`), precio regular (`regular_price`) o porcentaje de descuento (`discount_pct`) de un producto, el sistema propaga atómicamente dichos valores a **todas las variantes asociadas (`ProductVariant`)** y al metadato financiero del producto. Queda estrictamente prohibido que variantes preexistentes retengan precios o costos desactualizados que desfasen el carrito, el checkout o el catálogo del Storefront.
* **Gobernanza de Existencias de Inventario y Kardex:** Las cantidades físicas, reservadas y disponibles de cada producto y sus variantes deben calcularse en tiempo real a partir de los registros de inventario (`Inventory`) y movimientos de Kardex (`InventoryMovement`). Toda modificación de stock en el drawer de Hub 4 o ajuste rápido de inventario actualiza reactivamente `meta.sizes`, `meta.local_stock`, `meta.physical_stock` y `meta.available_stock`, reflejándose de forma instantánea en los badges de disponibilidad ("Stock Físico" / "Bajo Pedido") de las tarjetas del Storefront y de la ficha técnica (PDP).
* **Gobernanza de Descripciones, Telas y Atributos Comerciales:** La descripción del producto, copy persuasivo, bullets, tags SEO, marca comercial (`brand`) y tela/composición textil (`fabric`, `fabric_id`, `fabric_composition`) deben actualizarse de manera unificada. La ficha técnica de producto (PDP) debe priorizar dinámicamente la composición y tipo de tela curados en el producto (`$product->fabric`) por encima de valores estáticos por omisión.
* **Gobernanza de Categorías y Familias:** Al asignar, actualizar o crear categorías o subcategorías en un producto o en la configuración global, el sistema sincroniza simultáneamente la tabla pivote `category_product`, el campo JSON `meta` (`category`, `subcategory`, `family`) y los endpoints del catálogo PWA (`getPwaStoreProducts`). Queda estrictamente prohibido que un producto guardado quede desvinculado de su taxonomía oficial o que el drawer no envíe/persista la categoría y subcategoría seleccionadas.
* **Gobernanza de Colores y Swatches:** Todo color nuevo asignado a una prenda se registra automáticamente en `apparel_colors` (con soporte para degradados y tornasoles). Toda edición en la configuración de colores propaga en tiempo real los nuevos nombres y valores cromáticos a las prendas vinculadas, viéndose reflejado de forma inmediata en las tarjetas de producto y en la página de producto (PDP).
* **Gobernanza de Curvas de Tallas (Cero Fallback a 'UNI'):** Todo producto debe resolver y mapear su curva de tallas activa (por marca, proveedor, ámbito o curva por defecto del sistema) poblando `meta.sizes` y generando las variantes correspondientes en `ProductVariant` con existencias iniciales. Queda estrictamente prohibido que prendas de vestir con curvas asignadas o bajo demanda (dropship) caigan por omisión a la talla unitaria ('UNI') en las tarjetas del Storefront o en la PDP.
* **Propagación Reactiva en Cascada:** Al editar o renombrar una categoría, color, curva de tallas, tela (`ApparelFabric`) o marca (`Brand`) en los Ajustes de Inventario (`InventorySettingsApiController` / `BrandApiController`), el servicio `ProductCatalogSyncService` ejecuta la propagación a todos los productos y variantes dependientes.

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
* **Política Oficial de Stock Local Cero (Dropshipping Puro):** Todos los productos originados por scraping o categorizados como dropshipping se muestran estrictamente con **0 unidades de stock físico / local** en el almacén central (`localStock = 0`, `physicalStock = 0`, `availableStock = 0`). Las existencias físicas del almacén local aplican exclusivamente a prendas de confección propia o stock físico adquirido. Los productos scrapeados son 100% bajo pedido (dropshipping directo de fabricante / mayorista) y muestran la disponibilidad del proveedor externo (`supplierStock`), portando el badge **"Bajo Pedido"** / **"Drop Bajo Pedido"**, permitiendo su compra fluida sin descontar ni comprometer falsamente inventario físico local.
* **Exclusión Estricta de ROP y DOH para Productos Scrapeados y Dropshipping:** Dado que los productos scrapeados operan exclusivamente bajo pedido (Línea B / Dropshipping) con 0 unidades de inventario físico local, **no participan bajo ningún concepto en el Punto de Reorden (ROP / Reorder Point)** ni en cálculos de Días de Inventario Restante (DOH). En el panel de Hub 4 y tablas de inventario:
  - Queda estrictamente prohibido desplegar la alerta o botón intermitente de reorden `[⚡ ROP]` en productos scrapeados o dropshipping.
  - Se sustituye cualquier indicador de DOH o alerta ROP por la píldora informativa neutral **"On-Demand"** (Bajo Demanda).
  - Los productos scrapeados no se contabilizan ni se muestran cuando se filtra por *"Bajo ROP (Reabastecer Urgente)"* (`under_rop`).
  - El motor de recálculo de parámetros ROP (`saveRopSettings`) asigna `ropUnits = 0` e `isUnderRop = false` para todo ítem dropship/scrapeado, previniendo falsas órdenes de reposición para el almacén local.
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

### Regla 6.46: Gestión Integral de Marcas Comerciales (CRUD Completo en Modal y Ajustes, Ficha Fiscal Reactiva y Sincronización Transversal de Filtros)
* **Gestión Autónoma y CRUD Completo de Marcas en Hub 4 y Modal de Proveedor:** Dentro de los ajustes de Hub 4 (pestaña Marcas Comerciales `activeSettingsTab === 'brands'`) y **dentro de la Pestaña 3 ("Marcas Desprendidas") del modal de proveedor (`providerModalOpen`)**, los administradores disponen de gobernanza completa y autónoma:
  1. **Alta y Desprendimiento en Caliente:** Botón `+ Nueva Marca` para apertura del modal de alta completa (`brandModalOpen`, `z-[130]`) con proveedor preasignado, y formulario de alta rápida en 1 clic ("Desprender / Asociar Nueva Marca Comercial") con persistencia inmediata vía API REST (`/api/v1/inventory/brands`).
  2. **Controles Individuales por Marca:** Cada marca listada en el árbol de marcas del proveedor exhibe su slug, conteo de SKUs vinculados, insignia de estado y botones dedicados de acción en un clic: **Editar** (nombre, slug, logo, descripción y web), **Activar / Pausar** (`is_active`), **Archivar / Restaurar** (`is_archived`) y **Eliminar / Desvincular** (`deleteOrDetachBrandFromProvider`).
  3. **Desacoplamiento Estructural (Regla 5.1):** Los modales `providerModalOpen` (`z-[120]`) y `brandModalOpen` (`z-[130]`) residen como nodos hermanos en el nivel raíz del DOM (`hybridInventoryModule()`), garantizando visibilidad inmediata sobre la pantalla completa de ajustes (`z-[90]`) e independencia total de la pestaña activa en segundo plano.
* **Reactividad Inmediata en Botón "Ficha / Fiscal":** El botón "Ficha / Fiscal" presente en cada nodo de la tabla y árbol de proveedores debe abrir de forma reactiva e instantánea el modal de proveedor conmutando directamente a la pestaña fiscal (`openProviderFiscalModal(prov)`), garantizando un `z-index` de elevación (`z-[120]` con `style="z-index: 120 !important;"`) y erradicar cualquier propiedad `display: none` residual o bloqueo por superposición en el DOM.
* **Sincronización Transversal de Funcionalidades Dependientes (Filtros en Cascada):** La tabla maestra de marcas (`brands`) y su relación con proveedores impacta obligatoriamente y en tiempo real a:
  1. **Hub 4 (Inventario):** Filtro `brandFilter` en la barra de herramientas del catálogo, alimentado dinámicamente por `availableBrandNames`, filtrando la tabla de productos de forma combinada con proveedores, categorías y stock.
  2. **Hub 2 (Tienda & CMS):** Selectores de Proveedor y Marca Comercial en los modales de creación y edición de vitrinas de *Páginas de Explorar* (`store.blade.php`), permitiendo direccionar al comprador al catálogo filtrado por marca o fabricante.
  3. **Storefront PWA:** Estructura de árbol faceteado colapsable (`<details class="filter-tree">`) en el drawer de filtros (`#filterDrawer`), con casillas de verificación (**checkboxes**) indentadas que agrupan a cada proveedor con sus respectivas marcas comerciales desprendidas, sincronizado con el motor reactivo de coincidencia (`site.js`).

### Regla 6.47: Vinculación Obligatoria de Proveedor y Marca en Fichas de Alta, Edición y Curaduría con Alta Rápida In Situ
* **Presencia Universal en Toda Ficha de Producto:** En todos los puntos de captura, edición y curaduría del catálogo comercial:
  1. **Alta de Nuevo Producto (`newProductPanoramicModal`):** Es mandatorio disponer de un selector reactivo de *Proveedor / Fabricante* (`supplier_id`) y un selector dependiente de *Marca Comercial* (`brand`), eliminando inputs de texto libre ambiguos.
  2. **Curaduría Previa de Scraping (`scrapingCurationModalOpen`):** Toda prenda en staging debe permitir asignar interactivamente su proveedor oficial y la marca comercial con la que se comercializará antes de conceder la aprobación a inventario.
  3. **Ficha de Edición / Drawer Panorámico (`skuPanoramicModal`):** La sección de *Catalogación & Proveedor* debe estructurarse mediante selectores sincronizados de proveedor y marca comercial vinculada.
* **Botones de Alta Rápida In Situ ("+ Nuevo Proveedor" y "+ Nueva Marca"):** Cada selector de proveedor y marca en estas fichas debe incorporar un botón de acción rápida en cabecera:
  - `+ Nuevo Proveedor`: Despliega de inmediato el modal de registro de proveedor (`providerModalOpen`, `z-[120]`) para capturar información operativa y fiscal sin perder los datos ya introducidos en la ficha de producto. Al guardarse exitosamente, el nuevo proveedor se selecciona automáticamente en el formulario activo.
  - `+ Nueva Marca`: Despliega de inmediato el modal de marca comercial (`brandModalOpen`, `z-[130]`) preasignando el proveedor actualmente seleccionado. Al guardarse, la nueva marca se asocia y se autoselecciona de inmediato en el producto.
* **Filtrado Dinámico en Cascada:** Al seleccionar un proveedor en cualquiera de estas fichas, el listado de marcas comerciales disponibles debe filtrar automáticamente las marcas pertenecientes o comercializadas por dicho proveedor mediante `getBrandsForSupplier(supplierId)`, manteniendo consistencia relacional en toda la plataforma.

### Regla 6.48: Distinción Estricta e Inviolable entre Marcas Comerciales y Líneas de Producto / Tipos de Prenda
* **Separación Taxonómica Inviolable:** Queda terminantemente prohibido categorizar, registrar o listar Tipos de Prenda o Líneas de Producto (`TANGA`, `BIKINI`, `JOCKSTRAPS`, `BRIEF/BOXERS`, `TANK TOPS`, `T-SHIRTS`, `SWIMWEAR`, `COMBOS`, etc.) como si fuesen Marcas Comerciales (`brand`).
* **Pertenencia Categorial (`category` / `garment_type`):** Las líneas de producto son taxonomías de tipología y clasificación de prenda y deben residir exclusivamente en las propiedades de categorización (`meta.category`, `meta.garment_type`, `category`). Las marcas comerciales representan la identidad de la casa fabricante o firma comercial (ej. `Torzo`, `Adrenaline Forte`).
* **Blindaje y Gobernanza Transversal (Backend & Frontend):**
  1. **Modelo Proveedor (`Supplier::getBrandsTree`):** El cálculo dinámico del árbol de marcas filtra de forma obligatoria contra la lista canónica `PRODUCT_LINE_CATEGORIES`, impidiendo que valores categoriales heredados de productos o sincronizaciones se interpreten o proyecten como marcas del proveedor.
  2. **API de Marcas (`BrandApiController`):** Las acciones `store()`, `update()` y `seedInitialBrands()` validan contra `Supplier::isProductLineCategory()`. Cualquier intento de registrar un tipo de prenda como marca comercial es rechazado con error de validación HTTP 422.
  3. **Frontend Interactivo (`inventory-module.js`):** El gestor de inventario y los modales de alta (`getDetailedBrandsForCurrentProvider`, `getBrandsForSupplier`, `addAndPersistBrandToProvider`, `saveBrandModal`) aplican una lista de exclusión en caliente (`isProductLineCategory`), impidiendo al operador añadir categorías como marcas y filtrando cualquier registro residual.
  4. **Normalización de Base de Datos:** Los productos existentes deben vincularse a su marca comercial real en `products.brand` y conservar su tipología de prenda en `meta.category` y `meta.garment_type`.

### Regla 6.49: Asociación de Curvas de Tallas a Marcas y Proveedores y Reactividad de Edición
* **Asociación Relacional Opcional:** Las curvas maestras de tallas (`apparel_size_curves`) pueden asociarse a un Proveedor (`supplier_id`) y/o a una Marca Comercial (`brand_id`), permitiendo curvas patronales exclusivas por casa confeccionista o submarca, o mantenerse sin asignación para operar como *Curvas Universales*.
* **Selectores Jerárquicos en el Gestor:** En la pestaña *Curvas & Tallas* de Hub 4, el formulario de creación y edición incorpora selectores de Proveedor y Marca. Al seleccionar un proveedor, el listado de marcas comerciales filtra dinámicamente las firmas vinculadas a dicho fabricante.
* **Badges y Filtrado Contextual en Fichas:**
  1. Cada tarjeta de curva en la vitrina administrativa despliega badges distintivos (`Prov: ...`, `Marca: ...` o `Curva Universal`).
  2. En el modal panorámico de alta de producto (`newProductPanoramicModal`), el selector `getAvailableSizeCurves(supplier_id, brand)` prioriza o filtra las curvas correspondientes al fabricante y la marca del producto.
* **Prohibición de Ternarios en `x-model` (Integridad Reactiva):** Queda estrictamente prohibido utilizar expresiones ternarias dentro de directivas `x-model`. La captura y edición de curvas y tallas debe realizarse a través de objetos de formulario unificados (`curveForm` y `sizeForm`), previniendo errores de asignación izquierda inválida (`Invalid left-hand side in assignment`) y asegurando la persistencia inmediata al editar.

### Regla 6.50: Asociación Mandatoria de Marcas Comerciales a un Proveedor y Sincronización Reactiva de Árbol
* **Gobernanza Mandatoria (Cero Marcas Huérfanas):** Toda marca de producto se debe asociar de forma obligatoria a un proveedor registrado en la plataforma. Queda estrictamente prohibido persistir o dar de alta marcas comerciales sin `supplier_id` asignado.
  1. **Validación Backend (`BrandApiController@store`):** La regla `supplier_id => 'required|exists:suppliers,id'` es obligatoria. Cualquier solicitud sin proveedor es rechazada con HTTP 422 y mensaje descriptivo de gobernanza.
  2. **Formularios Frontend (`brandModalOpen`):** El selector de proveedor es mandatorio (`required`), etiquetado como "Obligatorio (Regla 1)", impidiendo el envío con selección vacía.
  3. **Migración y Normalización de BD:** Todas las marcas comerciales existentes (incluyendo firmas como *Mazho* y *Torzo Sexywear*) deben vincularse a su respectivo proveedor fabricante (`Torzo`, `supplier_id = 4`), erradicando inconsistencias históricas.
* **Sincronización Bidireccional y Reactividad en Caliente:** Al registrar o asociar una marca comercial a un proveedor, el backend sincroniza la columna `suppliers.brands` y devuelve el árbol relacional fresco (`supplier_brands_tree`). El módulo Alpine.js (`inventory-module.js`) actualiza reactivamente en memoria `prov.brands`, `prov.brandsTree` y la fila correspondiente de la tabla de proveedores sin necesidad de recargar la página.

### Regla 6.51: Fotografías Manuales con Asignación de Color de Prenda para Conmutación y Filtros de Variante
* **Captura Flexible de Imágenes en Workstation y Curaduría:** Tanto en el Workstation de Alta de Producto (`newProductPanoramicModal`) como en la Curaduría de Scraping (`scrapingCurationModalOpen`), el operador puede añadir manualmente URLs de fotografías adicionales del producto mediante un campo de entrada ágil con botón `+ Agregar Imagen`.
* **Asignación Obligatoria de Variante de Color por Fotografía:** Cada fotografía capturada en la galería cuenta con un selector de color de prenda (`color_name`) basado en la paleta oficial (Negro, Blanco, Rojo Pasión, Azul Marino, Gris Jaspe, etc.).
* **Designación de Foto de Portada (`is_primary`):** Cada imagen cuenta con un botón interactivo de selección de portada para definir la fotografía principal de vitrina.
* **Persistencia Estructurada en Catálogo (`meta.gallery` / `meta.images`):** La API almacena la colección de imágenes con sus respectivos metadatos de color, permitiendo que:
  1. En el catálogo público (Storefront y Hub 2), al hacer clic en un swatch de color, la fotografía de la tarjeta conmute instantáneamente a la variante de color correspondiente.
  2. En la ficha de detalle de producto (PDP), la galería sincronice la imagen visualizada con el selector de color activo.

### Regla 6.52: Alta In Situ de Categorías y Subcategorías sin Abandonar la Línea de Trabajo
* **Continuidad Operativa del Catalogador:** Durante el proceso de alta o curaduría, si una prenda pertenece a una categoría o subcategoría que aún no existe en el sistema, el operador nunca debe verse forzado a abandonar la pantalla, perder los datos capturados y trasladarse a los ajustes de inventario.
* **Botones In Situ (`+ Categoría` y `+ Subcategoría`):** En las cabeceras de los selectores taxonómicos del Workstation y de la Curaduría Scraping, se incorporan botones interactivos de alta in situ.
* **Modal Liviano de Creación Rápida (`quickCategoryModalOpen`, `z-[140]`):**
  1. Permite introducir nombre y descripción de la nueva categoría o subcategoría con preasignación automática del `parent_id` cuando se trate de una subcategoría.
  2. Al confirmarse el guardado vía `/api/v1/inventory/settings/categories`, la nueva entidad se inserta en tiempo real en la estructura en memoria `categoriesTree`.
  3. Se auto-selecciona automáticamente en el formulario del producto activo (Workstation o Curaduría), permitiendo al operador continuar la captura o aprobación sin interrupción.

### Regla 6.53: Asignación de Variantes de Color por Fotografía en la Ficha de Producto (Drawer) y Conmutación Reactiva
* **Asignación Dinámica en la Ficha de Producto:** Desde el drawer / modal de edición de producto (`skuPanoramicModal`), el operador tiene la capacidad de:
  1. Subir nuevas fotografías mediante carga directa de archivos o ingreso de URLs manuales con botón `+ Agregar Imagen`.
  2. Asignar de forma explícita un color de prenda (`color_name`) a cada fotografía mediante un selector interactivo basado en la paleta oficial de colores (`apparelColors`).
  3. Designar la imagen de portada / principal (`is_primary`) con un clic mediante botón con estrella ★.
  4. Crear un color nuevo in situ sin salir de la ficha ni recargar la página.
* **Persistencia Estructurada y Acceso Transparente:** La API transaccional (`ProductSkuAndDuplicateApiController@updateComplete`) procesa y almacena la colección en `meta.gallery`, `meta.images`, `meta.images_by_color` y `meta.image_color_map`. El modelo `Product` expone los accessors `$product->images` y `$product->images_by_color`.
* **Conmutación Visual en Vitrina:** Esta relación permite la conmutación reactiva e inmediata de la fotografía principal de la tarjeta cuando el comprador o el operador selecciona un color en el catálogo, en la ficha de detalle (PDP) o en las vistas modulares.

### Regla 6.54: Conversión Universal Obligatoria a WebP y Normalización Estándar (1080x1350 px, Proporción 4:5)
* **Conversión Universal a WebP:** Todas las imágenes que se suben a la aplicación —sin importar su formato original (JPEG, PNG, GIF, BMP) ni su peso o resolución— se convierten forzosamente al formato moderno **WebP** (`.webp`) con factor de compresión equilibrado (calidad 85).
* **Normalización Uniforme de Dimensiones (Estándar E-Commerce de Moda):**
  1. Las imágenes se redimensionan y ajustan a un tamaño canónico universal de **1080 × 1350 píxeles** (proporción vertical 4:5), el estándar de oro en el comercio electrónico de moda y retail para visualización óptima tanto en desktop como en dispositivos móviles y pantallas de alta densidad (Retina).
  2. La imagen se ajusta preservando su relación de aspecto original (`aspect ratio`) y se encuadra en el centro de un lienzo sólido de fondo blanco neutral (`#FFFFFF`) con antialiasing de alta calidad.
* **Servicio Especializado:** La lógica se encapsula en `App\Services\ImageNormalizationService` y se expone a través del endpoint transaccional `POST /api/v1/inventory/upload-image`, garantizando que ninguna imagen cruda o heterogénea contamine el almacenamiento ni degrade la velocidad de carga de la tienda.

### Regla 6.55: Alta In Situ de Nuevos Colores de Prenda en el Workstation (Sin Salir del Workstation)
* **Continuidad sin Interrupciones en el Workstation:** Durante el alta de producto (`newProductPanoramicModal`), el catalogador puede dar de alta un nuevo color de producto in situ si este no existe previamente en el catálogo de colores, sin abandonar el workstation ni recargar la pantalla.
* **Botones In Situ (`+ Color`):** Se disponen botones de alta in situ tanto en la cabecera del selector de color principal como junto al selector de color de cada miniatura fotográfica cargada.
* **Modal Rápido de Color (`quickColorModalOpen`, `z-[140]`):**
  1. Permite registrar el nombre del color, su valor hexadecimal (`#hex`) con selector nativo interactivo y el tipo de color (sólido o gradiente CSS).
  2. Al confirmarse el guardado vía `POST /api/v1/inventory/settings/colors`, la entidad se persiste en `apparel_colors`, se inserta reactivamente en la lista en memoria `apparelColors` y se autoselecciona de inmediato en el formulario o en la imagen destino.

### Regla 6.56: Código SKU Dinámico Reactivo Basado en Cualidades del Producto (Proveedor, Marca, Categoría, Tela, Color)
* **Reactividad Dinámica del SKU:** El código SKU no es una cadena fija ni se genera una única vez de forma estática; es reactivo y evoluciona dinámicamente conforme a las cualidades esenciales del producto.
* **Parámetros Determinantes:** El SKU se recalcula automáticamente si cualquiera de los siguientes atributos cambia en el formulario del Workstation:
  1. Proveedor / Fabricante (`supplier_id`).
  2. Marca Comercial (`brand`).
  3. Categoría / Línea de Prenda (`category`).
  4. Tipo de Tela / Composición (`fabric_type` / `fabric_id`).
  5. Color Oficial (`color_name` / `color_hex`).
* **Motor de Generación:** La lógica se coordina a través del método reactivo `updateDynamicSku(silent)` que invoca al servicio `SkuGeneratorService` vía `POST /api/v1/inventory/generate-sku`, previniendo colisiones de identificadores y garantizando una taxonomía lógica y trazable en el Kardex.

### Regla 6.57: CRUD Integral de Telas en Ajustes de Inventario y Alta In Situ en el Workstation (+ Tipo de Tela)
* **CRUD Maestro de Telas:** En los Ajustes de Inventario (Pestaña 1, *Telas*), la plataforma dispone de un gestor CRUD completo para tipos de telas y composiciones textiles (`apparel_fabrics`): creación, edición, activación/desactivación reactiva, archivado y eliminación definitiva.
* **Selección Contextual en Workstation y Drawer:** En el Workstation de Alta de Producto y en la Ficha de Edición, el tipo de tela se selecciona dinámicamente de entre las telas activas registradas en el CRUD maestro.
* **Botón de Alta In Situ (`+ Tipo de Tela` / `+ Nueva Tela`):** Si la tela deseada no existe en el catálogo:
  1. Un botón in situ abre el modal rápido `quickFabricModalOpen` (`z-[140]`).
  2. Permite capturar el nombre comercial de la tela, su composición textil porcentual y una descripción técnica.
  3. Al confirmarse vía `POST /api/v1/inventory/settings/fabrics`, la nueva tela se persiste en la base de datos, se incorpora a la colección en memoria `apparelFabrics` y se selecciona automáticamente en el formulario en edición, disparando además la actualización del SKU dinámico.

### Regla 6.58: Asociación Directa y Estricta del Tipo de Tela con los Productos (fabric / fabric_id)
* **Asociación Formal en la Capa de Datos:** El tipo de tela no es un texto libre o volátil sin control; se asocia formalmente a nivel relacional en la tabla `products` mediante dos columnas dedicadas:
  - `fabric`: Cadena descriptiva con el nombre y composición canónica de la tela (ej. `Algodón & Modal`, `Microfibra Spandex`).
  - `fabric_id`: Clave foránea entera no nulable vinculada a `apparel_fabrics.id`.
* **Modelado Eloquent:** El modelo `Product` declara las relaciones formales `fabricRelation()` y `fabric()` vinculadas a `ApparelFabric`, mientras que `ApparelFabric` define la relación inversa `products() -> hasMany(Product::class)`.
* **Gobernanza:** Esta estructura formal garantiza la integridad referencial, habilita auditorías de calidad de materiales en Kardex y alimenta los filtros facetados de búsqueda por composición textil en el Storefront y Hub 2.

### Regla 6.59: CRUD Integral de Secciones del Sitio Web E-Commerce (Home Hub 2, Dimensiones y Filtros Facetados)
* **CRUD Autónomo de Secciones Web:** En los Ajustes de Inventario (Pestaña 7, *Secciones Web*), la plataforma dispone de un gestor CRUD integral para las secciones del sitio web de e-commerce (`cms_sections`):
  1. **Creación:** Registro de nuevas secciones con nombre, slug, vitrina asociada (`storefront_id`), tipo (`catalog`, `editorial`, `banners`, `promo`), orden (`order_index`) y estado inicial.
  2. **Edición:** Modificación de atributos, nombres y asignación de vitrina.
  3. **Activación / Pausa:** Conmutación rápida de estado activo (`is_active: 1` vs `0`) mediante botón toggle en 1 clic.
  4. **Archivado / Restauración:** Archivado preventivo (`is_archived: 1`) sin pérdida de historial y reactivación instantánea.
  5. **Eliminación:** Borrado definitivo con confirmación de seguridad.
* **Interconexión Modular con Hub 2 (Home Multiverso):** Las secciones creadas se sincronizan directamente como secciones modulares en el Home de Hub 2 (`store.blade.php`), permitiendo vincular productos a cada sección a través del campo `products.section_name` y `meta.section_name`.
* **Dimensión de Filtrado Facetado:** Las secciones activas forman parte de las dimensiones de filtrado transversal que se aplican a los filtros de catálogo y facetas de exploración de Hub 2 y Storefront PWA.

### Regla 6.60: Poblado Real de la Bandeja de Curaduría de Scraping (Cero Mocks, 259 Productos Reales de Torzo y Persistencia End-to-End)
* **Erradicación Total de Ejemplos Mock:** La bandeja de curaduría de scraping en el módulo de Inventario (`portal.inventory`) está terminantemente desvinculada de datos simulados o hardcodeados (como `TOR-RAW-019` o `TOR-RAW-020`).
* **Poblado de Catálogo Real en Staging (`scraped_pending_items`):**
  1. Todos los productos scrapeados de proveedores aliados (como el catálogo real de Torzo B2B con 259 prendas íntimas masculinas) residen formalmente en la tabla `scraped_pending_items` con estado `curation_status = 'pending'`.
  2. Cada ítem incluye sus fotografías reales de alta resolución hospedadas en el servidor de Torzo (`https://torzo.kexe.mx/imagenes_productos/...`), sus tallas disponibles con inventario en almacén proveedor (`sizes`), costos mayoristas netos (`scraped_cost`), precios de venta sugeridos (`curated_price`), composiciones textiles detectadas (`suggested_fabric`) y colores cromáticos oficiales con su código `#hex`.
* **Ergonomía de Curaduría Masiva:**
  1. La bandeja de curaduría dispone de un contenedor con scroll vertical delimitado (`max-h-[780px] overflow-y-auto`) para navegar cómodamente el lote completo de prendas sin saturar el layout.
  2. Incorpora una barra de búsqueda reactiva en tiempo real (`scrapedSearchQuery`) y un filtro contextual por categoría de prenda (`scrapedCategoryFilter`) para localizar productos al instante.
  3. Muestra miniaturas nítidas de 56×64 px con esquinas redondeadas y visualizador del margen comercial proyectado en vivo `((PVP - Costo) / PVP)`.
* **Persistencia Transaccional End-to-End:**
  1. **Aprobación (`approveScrapedItem`):** Dispara una petición asíncrona a `POST /api/v1/inventory/scraping/pending/{id}/curate` con `action: 'approve'`, actualizando el ítem a `approved`, creando o actualizando el producto en el catálogo maestro (`products`), asignando su tela relacional (`fabric_id`), sección web (`section_name`) y galería de imágenes con variantes de color, e insertándolo en el inventario activo con el badge *"Drop Bajo Pedido"*.
  2. **Descarte (`discardScrapedItem`):** Envía `action: 'reject'` con el motivo de descarte y remueve la prenda de la bandeja de entrada sin recargar la pantalla.

### Regla 6.61: Gobernanza del Ciclo de Scraping, Descontinuación en Origen y Herramienta de Productos Archivados
* **Ciclo Integral de Scraping en 5 Fases Estrictas:**
  1. **Fase 1 (Mapeo y Normalización en VSC):** Al suministrar la URL o catálogo a escrapear, se identifican y cuadran los campos con el sistema (en VSC). Se determinan correspondencias para títulos, precios mayoristas, PVPs recomendados, tallas, composiciones textiles y paletas de color.
  2. **Fase 2 (Visto Bueno y Aislamiento en Curaduría):** Al obtener el visto bueno en Visual Studio Code sobre la normalización, los productos scrapeados se insertan en estado de curaduría scraping (`scraped_pending_items` con `curation_status = 'pending'`). **Ningún escrapeo se valida automáticamente ni puede poblar el catálogo operativo real de forma desatendida; solamente el usuario puede aprobarlo**. Mientras un producto permanezca en curaduría, está estrictamente aislado y jamás se muestra en el catálogo operativo ni en la vitrina comercial.
  3. **Fase 3 (Control de Frecuencia de Actualización):** La tabla de Proveedores & Scrapers permite configurar interactivamente el intervalo de refresco automático (`scrape_frequency`: cada 15m, 30m, 1h, 6h, 12h, 24h, 7d). El cambio se persiste de forma inmediata contra el backend mediante `PATCH /api/v1/inventory/suppliers/{id}/frequency` impactando la columna `suppliers.scrape_frequency`.
  4. **Fase 4 (Detección de Descontinuación en Origen y Modal de Discrepancias B2B):** Cuando un ciclo de actualización de scraping detecta que un producto fue retirado del catálogo original del proveedor mayorista, el sistema emite una alerta prioritaria hacia el **Centro de Auditoría Operativa y Discrepancias B2B**. Al accionar dicha discrepancia, se despliega un modal interactivo con la ficha del producto descontinuado y ofrece las 3 opciones de resolución obligatorias:
     - **Retirar del Catálogo (Ocultar):** Despublica la prenda de la vitrina comercial y del catálogo operativo activo sin destruirla (`visible = false`).
     - **Archivar Producto:** Traslada la prenda a la herramienta de archivados y descontinuados (`is_archived = true`, `visible = false`, registrando fecha y motivo).
     - **Eliminar Producto:** Supresión física definitiva del registro en la base de datos (`DELETE`).
  5. **Fase 5 (Herramienta de Productos Archivados y Descontinuados):** El catálogo operativo cuenta con una herramienta permanente accesible desde la barra de herramientas principal (`archivedProductsModalOpen`) y desde el menú *"Herramientas de Stock"*, donde residen todas las prendas archivadas y descontinuadas. Dentro de ella, el usuario cuenta con un buscador reactivo y dos acciones resolutivas por prenda:
     - **Volver a Activar (`reactivateArchivedProduct`):** Reincorpora la prenda inmediatamente al catálogo operativo activo (`is_archived = false`, `visible = true`).
     - **Eliminar (`deleteArchivedProduct`):** Borrado definitivo e irreversible del producto.

### Regla 6.62: Integración Nativa con Google Gemini AI (Copiloto de Producto, Chat Estratégico Ejecutivo y Telemetría de Salud)
* **Credenciales y Configuración de Entorno:**
  1. La conexión con Google Generative Language se gobierna de forma centralizada en el archivo `.env` mediante `GEMINI_API_KEY`, `GEMINI_MODEL="gemini-flash-lite-latest"`, `GEMINI_FALLBACK_MODEL="gemini-flash-latest"`, `GEMINI_PROJECT_NAME` y `GEMINI_PROJECT_NUMBER`.
  2. Las credenciales se mapean en `config/services.php` bajo la clave `'gemini'` con control de timeout (25s) y URL base oficial (`https://generativelanguage.googleapis.com/v1beta`).
* **Servicio Centralizado de Alta Resiliencia (`App\Services\Ai\GeminiService`):**
  1. **Generación de Texto (`generateText`):** Soporta system instructions, reintentos automáticos (`retry(2, 500)`) y conmutación transparente a modelo fallback si el modelo principal experimenta alta demanda (HTTP 503 o 429).
  2. **JSON Estructurado Nativo (`generateJson`):** Configurado con `responseMimeType: "application/json"`, garantizando objetos JSON parseables sin bloques markdown para títulos, descripciones persuasivas y tags.
  3. **Chat Multi-Turno Contextual (`chat`):** Mantiene memoria conversacional a partir del histórico de mensajes almacenados en la tabla `ai_copilot_chat_messages`.
* **Funcionalidades Operativas Conectadas en la Plataforma:**
  1. **Copiloto IA de Producto (`ProductAiCopilotService`):** En el Workstation de Alta de Producto y en la ficha de edición, genera descripciones de 2 párrafos resaltando ergonomía y transpirabilidad, bullets técnicos y tags SEO. Cuenta con fallback determinístico local si la red no estuviese disponible.
  2. **Chat Estratégico del Dashboard Ejecutivo (`DashboardApiController@copilotChat`):** Procesa consultas en lenguaje natural sobre márgenes EBITDA, cortes de paquetería DHL a las 14:00 hrs, quiebres de inventario ROP y recuperación de carritos abandonados con gatillos de WhatsApp y chips de acción interactivos.
  3. **Monitor y Endpoint de Telemetría (`GET /api/v1/dashboard/copilot/status`):** Expone el estado en vivo de la conexión con Google Gemini, latencia en milisegundos y resumen de módulos potenciados por IA.

### Regla 6.63: Acción de Archivo Directo en Catálogo Operativo hacia la Herramienta de Archivados y Descontinuados (Regla 1)
* **Acción de Archivo en Cada Fila del Catálogo:** La tabla principal del catálogo operativo incorpora en cada fila de producto un botón dedicado para archivar (`archiveProduct(prod)`).
* **Persistencia y Traslado Transaccional:** La acción ejecuta `POST /api/v1/inventory/products/{id}/resolve-discontinued` con `action: 'archive'`. El backend actualiza `is_archived = true` y `visible = false`, registrando fecha y motivo.
* **Segregación Inmediata en Memoria:** El producto se retira inmediatamente del listado reactivo `products` y se añade a `archivedProducts`, quedando disponible en la herramienta de *Archivados y Descontinuados* para reactivación o eliminación permanente.

### Regla 6.64: Paridad Estructural y Taxonómica en Curaduría de Scraping con Modales In Situ y Copiloto IA (Regla 2)
* **Homologación con Workstation de Alta:** La bandeja de curaduría de scraping (`curateItemModalOpen`) replica de forma idéntica los campos de captura del Workstation de Alta de Producto:
  1. Categorías y subcategorías con botón in situ para crear nuevas taxonomías sin perder contexto (`openQuickCategoryModal`).
  2. Tipos de tela y composición con botón in situ (`openQuickFabricModal`).
  3. Paleta de colores con muestras hexadecimales y botón in situ (`openQuickColorModal`).
  4. Asignación de color individual por fotografía scrapeada con botón `+ Color` in situ.
  5. Selectores de vitrina comercial (`websiteSection`), marca (`brand`), precios, costos e inventario.
  6. Tarjeta y gatillo del Copiloto Comercial IA para redacción persuasiva.
* **Paridad de Diseño Visual (Mismo Diseño que el Workstation):** La curaduría de scraping no solo replica los campos, sino que debe tener **el mismo diseño visual** que el Workstation de Alta de Producto. Queda prohibido mantener estilos divergentes entre ambas pantallas:
  1. **Misma estructura y jerarquía:** Mismo orden de secciones, agrupación en tarjetas (`bg-white rounded-2xl border border-slate-200 shadow-2xs`), encabezados de sección con numeración/ícono SVG y subtítulos descriptivos equivalentes.
  2. **Mismos componentes:** Etiquetas (`text-[11px] font-bold text-slate-700 uppercase tracking-wider`), inputs y selects (`bg-slate-50 border-slate-200 rounded-lg text-xs font-semibold`, foco `ring-indigo-500/20`), botones in situ (`+ Categoría`, `+ Tela`, `+ Color`, `+ Vitrina`) con idéntico tamaño, color e ícono.
  3. **Mismas tarjetas especializadas:** La tarjeta de Margen Comercial & Pricing usa el mismo diseño claro índigo del Workstation (Regla 6.75: `bg-indigo-50/50`, inputs blancos, tipografía `text-slate-900`, Utilidad por Pieza y Margen Bruto con badges *Saludable / Aceptable / Riesgo*); los selectores de Acabado/Diseño y Modalidad Logística usan los mismos toggles con indicador circular; la galería de fotos usa la misma grilla 4:5 con selector de color por foto.
  4. **Misma paleta y tipografía:** Sin fondos oscuros, gradientes o variantes de color propias de la curaduría que no existan en el Workstation. Cero emojis (solo íconos SVG).
  5. **Fuente única de verdad:** Cualquier cambio de diseño aplicado al Workstation de Alta de Producto debe replicarse en la curaduría de scraping en el mismo commit (y viceversa), preferentemente reutilizando parciales Blade / componentes compartidos para evitar divergencias futuras.

### Regla 6.65: Generación Persuasiva de Contenido y Metadatos con Copiloto IA en Workstation de Alta y Curaduría (Regla 3)
* **Redacción Persuasiva B2C:** El Copiloto Comercial IA genera textos persuasivos de alta conversión orientados a moda íntima masculina y confort, integrando título optimizado, dos párrafos descriptivos (beneficios sensoriales, ajuste anatómico y durabilidad), viñetas técnicas (bullets) y etiquetas de posicionamiento SEO.
* **Invocación Unificada:** La función `generateAiDescription(context)` soporta tanto `context === 'newProduct'` como `context === 'curating'`, conectándose al endpoint `/api/v1/inventory/products/generate-description` respaldado por Google Gemini.

### Regla 6.66: Centralización de Ajustes, Telemetría y Pruebas de Inteligencia Artificial en Ajustes del Sistema (Regla 4)
* **Pestaña Dedicada en Ajustes:** El modal de configuración del sistema (`moduleSettingsModalOpen`) contiene la pestaña permanente *Inteligencia Artificial (Gemini)* (`activeSettingsTab === 'ai'`).
* **Parámetros Gobernables:** Permite inspeccionar y administrar la API Key de Google Gemini (con conmutador de visibilidad segura), ID de proyecto GCP, selector de modelo principal (`gemini-flash-lite-latest`), modelo fallback (`gemini-flash-latest`), slider de temperatura creativa (0.1 a 1.0) y tokens máximos.
* **Diagnóstico en Tiempo Real:** Incluye tarjeta de telemetría con indicador visual de estado (Conectado / Error / Sin Verificar), latencia en milisegundos y botón para realizar prueba de conexión en vivo (`testGeminiConnection`).

### Regla 6.67: Ciclo de Gobernanza: Pre-guardado en Borrador y Aprobación Oficial para Ingreso al Catálogo Operativo (Regla 5)
* **Bifurcación de Guardado en Workstation:** El pie del Workstation de Alta de Producto dispone de dos acciones claramente diferenciadas:
  1. **Pre-guardar Borrador (`saveNewProductAsDraft`):** Persiste el producto en la base de datos con `approval_status = 'draft'` y `visible = false`.
  2. **Aprobar y Publicar en Catálogo (`saveNewProduct(true)`):** Persiste y aprueba inmediatamente el producto (`approval_status = 'approved'`, `visible = true`).
* **Aislamiento Total del Catálogo Operativo:** Los productos en estado de borrador (`draft`) se alojan en la colección segregada `draftProducts` (`portalDraftProducts`). Jamás se muestran a clientes en vitrinas comerciales ni en el listado del catálogo operativo hasta que reciben aprobación oficial.
* **Herramienta y Modal de Borradores Pendientes:** Accesible desde la barra de herramientas superior y desde el menú de herramientas de stock (`draftsModalOpen`). Permite filtrar borradores por SKU o nombre, y ejecutar tres acciones:
  - **Aprobar & Publicar (`approveDraftProduct`):** Invoca `POST /api/v1/inventory/products/{id}/approve`, transfiere la prenda al catálogo operativo activo y la publica en tienda.
  - **Editar en Workstation (`loadDraftIntoWorkstation`):** Carga todos los campos del borrador en el workstation para continuar su edición.
  - **Descartar (`discardDraftProduct`):** Elimina el borrador de forma controlada.

---


### Regla 6.68: Tipificación de Producto Liso o Con Diseño en Workstation de Alta de Producto
* **Selector Taxonómico de Diseño:** El Workstation de Alta de Producto incorpora en el Paso 2 (Taxonomía) un selector obligatorio para definir si la prenda o artículo es de "Color Liso" (`liso`) o cuenta con "Diseño / Estampado" (`con_diseno`).
* **Reflejo Reactivo en Resumen:** La tipificación se visualiza en tiempo real en la ficha técnica (Columna 2) mediante un badge distintivo (púrpura para "Con Diseño", slate neutro para "Color Liso").

### Regla 6.69: Alta In Situ de Vitrinas Web E-Commerce y Sincronización con Ajustes de Módulo
* **Botón `+ Vitrina` en Workstation:** La selección de vitrina comercial (`websiteSection`) en el Paso 2 incluye un botón interactivo para dar de alta una nueva vitrina web sin recargar la página (`openQuickStoreSectionModal('newProduct')`).
* **Sincronización Bidireccional:** El alta in situ crea la sección web vía API (`POST /api/v1/inventory/settings/store-sections`), la inserta en la colección reactiva `storeSections`, la auto-selecciona en el formulario del producto y la sincroniza con los ajustes generales del módulo de inventario y del storefront.

### Regla 6.70: Color Multicolor Nativo de Sistema Inmutable con Degradado Arcoíris
* **Opción Nativa "Es Multicolor":** En la creación de colores (tanto en el modal de Ajustes como en el alta in situ `+ Color`), se incluye un checkbox interactivo *"Es Multicolor"*. Al activarse, desactiva la paleta hexadecimal y define el color como *"Multicolor"*.
* **Identidad Visual Arcoíris:** En todas las interfaces (muestrarios, swatches, fichas y tablas), el color Multicolor se representa mediante el degradado arcoíris `linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)`.
* **Inmutabilidad y Preservación del Sistema:** El color Multicolor es una entidad predeterminada del sistema. No puede ser eliminado ni duplicado, garantizando integridad para prendas estampadas o con patrones multitonos.

### Regla 6.71: Workstation Alta de Producto Paginado por Pasos (Wizard Secuencial en Columna 1)
* **Paginación Estricta sin Desbordamiento:** El formulario de la Columna 1 abandona el scroll vertical monolítico y se divide en 4 pasos paginados e independientes gobernados por `workstationStep` (1 a 4):
  1. **Paso 1 (Fotos & Colores):** Carga interactiva drag & drop, recorte estandarizado 4:5 (1080×1350 px), designación de foto de portada y asignación individual de variante de color por fotografía con botón in situ `+ Color`.
  2. **Paso 2 (Datos & Diseño):** Nombre comercial, análisis de duplicados, proveedor, marca, categoría, subcategoría, conmutador Liso / Con Diseño, modalidad logística (Propio / Dropship) y vitrina web con botón `+ Vitrina`.
  3. **Paso 3 (Variantes & Slotting):** Dimensión de variante (curvas de tallas, tonos, volúmenes, piezas únicas), selector condicional de tipo de tela (visible exclusivamente si la dimensión es textil/talla) y ubicación en bodega (slotting: Pasillo, Rack, Gaveta).
  4. **Paso 4 (Copiloto & Pricing):** Copiloto comercial IA con redacción persuasiva B2C, tags SEO de indexación, cálculo de margen comercial bruto en tiempo real y codificación Auto-SKU.
* **Navegación Ergonómica:** Cuenta con stepper superior interactivo con indicadores de estado e indicadores *"Paso Anterior"* y *"Siguiente Paso"* en la base de cada pantalla.

### Regla 6.72: Desacoplamiento de Color Principal en Paso 3 y Asignación Exclusiva por Fotografía en Paso 1
* **Erradicación de Redundancia:** En el Paso 3 se elimina por completo la sección *"Color Principal"*, evitando inconsistencias y duplicidad de captura.
* **Asociación Canónica en Galería:** La relación cromática de la prenda se asigna de manera visual, directa y unívoca en cada fotografía cargada durante el Paso 1, permitiendo asociar múltiples fotos a variantes de color específicas con su respectiva muestra cromática.

### Regla 6.73: División Vertical de Columna 2: Galería Superior (30% con Portada 10% Mayor) y Ficha Técnica Reactiva en Tiempo Real (70%)
* **Aislamiento en Dos Secciones Verticales:** La Columna 2 de previsualización en vivo se estructura verticalmente sin sobrepasar la altura del viewport modal (`max-h-[82vh]`):
  1. **Sección Superior (30% de la altura):** Galería horizontal oscura (`bg-slate-950`) que exhibe todas las fotografías cargadas del producto. La imagen designada como portada se muestra destacada, con un tamaño **un 10% mayor** (`scale-110`, anillo índigo y badge "Portada"). Cada miniatura visualiza el chip del color asignado y permite conmutar la portada en un clic.
  2. **Sección Inferior (70% de la altura con scroll vertical fluido):** Ficha técnica reactiva que se actualiza en tiempo real al tipear en cualquier paso: categoría, subcategoría, badges de diseño y logística, nombre comercial, marca, proveedor, vitrina web, tejido textil (si aplica), matriz de existencias desglosada, ubicación en bodega (slotting), PVP, costo y margen bruto %, copy persuasivo del Copiloto IA y chips de tags SEO.

### Regla 6.74: Fidelidad Taxonómica Estricta en Workstation y Copiloto IA
* **Correspondencia Exacta de Atributos Curados:** El resumen generado por producto en la ficha técnica (Columna 2) del Workstation de Alta de Producto, así como las descripciones comerciales y bullets redactados por el Copiloto IA (Gemini / Heurístico), deben basarse **estricta y exclusivamente en las características configuradas y curadas por el usuario** en el mismo workstation:
  1. **Tipo de Tela / Composición:** Debe sincronizarse dinámicamente con los registros de la base de datos (`apparel_fabrics`) sin forzar valores arbitrarios ("Modal Seamless") por omisión. Si el usuario selecciona "Poliéster 95%, Elastano 5%", tanto la ficha previa como el SKU y el prompt de la IA deben recibir y reflejar con fidelidad dicha tela.
  2. **Prohibición de Invención Taxonómica en IA:** El prompt enviado al Copiloto IA debe prohibir taxativamente inventar fibras o acabados (como modal, seda o algodón) si no forman parte explícita de los atributos provistos en la sesión.
  3. **Reactividad Bidireccional Limpia:** Los selectores de atributos en el formulario no deben contener directivas Blade fijas (`:selected`) que bloqueen la vinculación de datos en tiempo real de Alpine.js / JavaScript.

### Regla 6.75: Carga Múltiple de Fotografías y Curaduría Secuencial 4:5
* **Soporte Multi-Foto en Carga:** El selector y zona de dropzone de imágenes en el Paso 1 del Workstation admite la selección simultánea de múltiples fotografías (`multiple`, límite global de 10 imágenes por producto).
* **Cola Secuencial de Encuadre y Recorte:** Al cargar múltiples fotos, el sistema encola los archivos válidos y activa un flujo secuencial donde el usuario cura, acomoda y recorta las imágenes **una por una** de manera interactiva:
  1. El modal de recorte (`#imageCropModalOpen`) visualiza un indicador de avance en el encabezado (*"Foto X de Y"*).
  2. Al confirmar el recorte de la imagen actual (`applyCrop45`), se persiste en la galería y automáticamente se monta la siguiente foto en cola sin cerrar el flujo de trabajo.
  3. Al completar la última foto de la cola, el modal se cierra con confirmación toast global. Si el usuario cancela, se descartan los archivos pendientes restantes respetando las fotos ya curadas.
* **Sobriedad Visual en Tarjeta de Pricing:** La tarjeta de margen comercial y fijación de precios en el Paso 4 debe mantener una armonía visual sobria e integrada con el panel de administración (fondos claros, tipografía de alto contraste legible `text-slate-900`, inputs blancos con sombra sutil y badges de margen destacados con contraste accesible WCAG AA).

### Regla 6.76: Login RevOps Aislado & Erradicación Total de Chrome del Storefront (TASK-AUTH-01)
* **Aislamiento Total de la Pantalla de Ingreso:** La pantalla de autenticación del panel administrativo y RevOps (`/login`, `/ingreso/usuario`) debe ser una vista dedicada, autónoma y completamente desvinculada de cualquier layout o componente periférico de la tienda pública (storefront).
* **Erradicación de Navegación y Filtros de Tienda:** Queda terminantemente prohibido renderizar el encabezado público (menús "Inicio", "Ofertas", "Novedades", "Marcas", "Catálogo", "Guía de tallas", "Carrito", "WhatsApp", buscador de productos), el cajón de navegación móvil, barras de anuncios y las barras laterales de filtrado rápido ("Filtro rápido / Categorías, tallas y más") dentro de la interfaz de login administrativo.
* **Exclusividad de la Sección de Ingreso:** La interfaz debe presentar exclusivamente la tarjeta de autenticación centrada en pantalla completa (`min-h-screen flex items-center justify-center`):
  1. **Identidad Visual RevOps:** Logotipo corporativo, indicador de estado operativo del sistema y tarjeta con estética sobria y moderna (Plus Jakarta Sans, paleta oscura refinada y soporte para altos contrastes).
  2. **Campos Requeridos:** Correo corporativo (`email`), contraseña (`password`) con botón interactivo SVG para alternar visibilidad (mostrar/ocultar) y checkbox opcional de persistencia de sesión ("Mantener sesión activa").
  3. **Seguridad y Auditoría:** Alerta clara de errores de validación (`$errors->first()`), token CSRF obligatorio y nota de pie indicando entorno seguro cifrado TLS y registro en bitácora de auditoría.
  4. **Iconografía Profesional:** Cero emojis informales; uso exclusivo de iconos vectoriales SVG.

### Regla 6.77: Optimización Responsiva del Panel de Administración (Tablets y Móviles)
* **Responsividad Obligatoria:** El panel de administración (RevOps) debe estar completamente optimizado para dispositivos móviles y tablets. Las tablas, formularios de datos, sidebars y paneles de configuración deben adaptar su diseño (`layout`) de forma fluida (e.g. usando diseño adaptable, menús colapsables o `overflow` adecuado).
* **Paridad Funcional:** El administrador debe poder realizar el 100% de las funciones operativas, de curaduría y configuración desde un dispositivo móvil o tablet, sin pérdida de contexto, garantizando una UX ergonómica en pantallas táctiles.

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

### Regla 7.5: Paridad Estructural Estricta y Previsualización en Tiempo Real en Modales de Sección Modular
* **Paridad Estructural (Creación vs Edición):** Los modales de edición (`editSectionModal`) y creación (`createSectionModal`) de las secciones modulares del Home en Hub 2 (`store.blade.php`) deben mantener exactamente la misma estructura de layout de 2 columnas (`md:w-[700px]` para formulario y panel interactivo a la derecha), con idénticos campos organizados sistemáticamente: Tipo de Sección, Bifurcador de Modo Estructurado vs Custom Scoped, Bloque Común de Títulos, Subtítulos, Badges, Tipografías Google Fonts independientes y Alineación de texto; Bloque Hero con motor de ancho completo horizontal independiente con switch (`settings[full_width_horizontal]`), Fullpage vertical (`settings[full_width]`), Altura personalizada, Parallax Scrolling, Fondo Imagen/Video con opciones de Autoplay/Loop/Muted, Selector de Foco de Atención en Matriz de 9 Celdas (`settings[image_focal_point]`), Capa de Degradado Overlay con transparencia RGBA, y Conmutador de Botones vs Clic Integral (`settings[hero_has_buttons]` & `settings[hero_click_url]`) con controles de estilo para CTAs si están activos; Bloque Carrusel Dinámico con modo global/por-slide; Bloque Pop-Up; y Bloque Custom.
* **Previsualización Renderizada en Tiempo Real (Live Preview):** Todo cambio o tipeo en cualquier campo (inputs, selects, color pickers, sliders de opacidad, conmutador de ancho horizontal 100%, selección de foco en la matriz de 9 celdas, conmutador de botones/clic integral, subidas de archivos locales o URLs externas) debe actualizar en tiempo real el contenedor de previsualización derecho (`editLivePreviewContainer` / `livePreviewContainer`), con soporte interactivo para conmutador de dispositivos Desktop/Mobile (375px centrado) sin colisión cruzada entre modales.
* **Comportamiento en Storefront y Foco de Imagen:** El Storefront (`home.blade.php`) aplica el punto focal seleccionado a las declaraciones CSS de `background-position` (tanto para imágenes solas como en composición combinada con capas de degradado overlay) y `object-position` para fondos de video, garantizando que el cuadrante seleccionado permanezca visible ante recortes responsive. Cuando el Hero no tiene botones (`hero_has_buttons = 0`), no se renderizan botones e inserta un enlace absoluto de superficie completa (`.hero-full-click-link`). Cuando se activa el ancho 100% horizontal (`full_width_horizontal = 1`), se aplica la clase `.hero-full-width-h` (100vw).
* **Gestión de Espaciados Modulares y Eliminación de Brechas por Defecto (`settings[spacing_top]` & `settings[spacing_bottom]`):**
  - **Cero Brechas por Defecto (Zero-gap Default):** Por defecto, todas las secciones modulares del Storefront (`hero`, `brand_pillars`, `lead_magnet`, `curated_products`, `subcategories_slider`, `custom_html_css_js`, `buttons`) deben renderizarse sin espacios en blanco no deseados entre sí (`margin-top: 0px`, `margin-bottom: 0px`, `padding-top: 0`, `padding-bottom: 0`), garantizando continuidad visual seamless (especialmente en transiciones entre fondos oscuros o panorámicos de 100vw).
  - **Controles Sincronizados de Margen Vertical:** En ambos modales (`createSectionModal` y `editSectionModal`), se integran controles independientes para margen superior e inferior (`#createSpacingWrap` y `#editSpacingWrap`), equipados con input numérico en píxeles (0-160px), range slider reactivo (0-120px) y botones de presets rápidos (`0px`, `12px`, `24px`, `36px`, `48px`).
  - **Reactividad en Live Preview y Storefront:** Los márgenes configurados se aplican dinámicamente en tiempo real en el Live Preview mediante `window.setSectionSpacing()` y se reflejan con prioridad en el Storefront (`home.blade.php`), hidratándose automáticamente en la edición (`openEditSectionModal`).
* **Pre-poblado Inmediato al Abrir Edición (`openEditSectionModal`):** Al presionar el botón "Editar", la función `openEditSectionModal` debe restaurar y pre-poblar fielmente el 100% de los valores guardados en `sec.settings` y `sec.query_rules`, incluyendo la activación de la celda correspondiente en la matriz de 9 puntos de foco, activando de inmediato la previsualización renderizada sin requerir interacción manual del usuario.

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

### Regla 8.4: Mitigación de Vulnerabilidades de Runtime en Motor PHP y Blindaje Multicapa (PHP2Shell: CVE-2026-109618 y CVE-2026-109619)
* **Arquitectura de Defensa en Profundidad ante Incidentes de Runtime:** Ante vulnerabilidades críticas globales del motor PHP (Pre-Auth RCE, corrupción de memoria OOM en `longjmp` o Use-After-Free en estados globales), la plataforma implementa una estrategia de mitigación en 4 capas concurrentes:
  1. **Hardening del Motor PHP (`php.ini`):** Deshabilitación obligatoria de `session.upload_progress.enabled = Off` y `session.upload_progress.cleanup = Off` en todos los entornos de ejecución, erradicando el vector de manipulación de estado de sesión persistente.
  2. **Virtual Patching / WAF de Aplicación (`PreventPhp2ShellExploit`):** Registro de middleware HTTP prioritario en la cúspide del pipeline (`bootstrap/app.php`) que inspecciona y rechaza con HTTP 403 Forbidden cualquier solicitud que intente inyectar variables de progreso de sesión (`PHP_SESSION_UPLOAD_PROGRESS`), cabeceras anómalas o delimitadores multipart irregulares.
  3. **Blindaje en Servidor Web (`.htaccess` / Nginx):** Bloqueo a nivel de capa perimetral web para rechazar peticiones malformadas antes de su derivación al proceso PHP.
  4. **Gobernanza de Actualización Upstream:** Migración obligatoria a versiones oficiales con parche de seguridad (PHP 8.3.36+ para rama 8.3, 8.4.27+ o 8.5.12+).

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
* Queda prohibido realizar commits genéricos como *"cambios"*, *"avances"* o *"fix"*. Todo commit debe declarar el alcance de la modificación y el ID de tarea asociado.---

## 12. Reglas del Módulo de Inventario & Compras (Hub 4) Seccionadas por Ciclos Operativos

> **Principio de Gobernanza por Ciclos Operativos (Canon Regla 10):**  
> Para garantizar que ningún proceso del negocio quede desregulado o sujeto a interpretaciones ambiguas, las directrices operativas del Módulo de Inventario, Catálogo y Compras (Hub 4) se estructuran formalmente por **Ciclos Operativos**. Esta organización permite identificar de manera inmediata qué etapas de la cadena de valor están plenamente normadas y qué fases subsecuentes presentan brechas operativas pendientes de regulación formal.

### Matriz de Cobertura y Diagnóstico de Ciclos Operativos

| Ciclo Operativo | Módulo / Submódulo | Estado de Regulación | Reglas Formales | Brechas / Pendientes por Reglar |
| :--- | :--- | :--- | :--- | :--- |
| **Ciclo 1: Catálogo, Ingesta & Curaduría de Producto** | Hub 4 (Inventario / Workstation / Curaduría) | **100% Reglado y Operativo** | 12.1.1 a 12.1.6 | Ninguna. Flujo de captura, fotos 4:5, taxonomías in situ, fidelidad IA y borradores blindados. |
| **Ciclo 2: Gobernanza de Proveedores & Marcas** | Hub 4 (Ajustes / Proveedores & Marcas) | **100% Reglado y Operativo** | 12.2.1 a 12.2.2 | Pendiente reglamentar evaluación automatizada periódica de SLA / Scorecard por mora de entrega. |
| **Ciclo 3: Planeación de Compras y Tabuladores MOQ** | Hub 4 (Compras / Bandeja MOQ) | **100% Reglado y Operativo** | 12.3.1 a 12.3.5 | Pendiente reglamentar cálculo de pronóstico estacional y sugerido automático por velocidad de venta (DOH). |
| **Ciclo 4: Discrepancias, Acuerdos Mercantiles y Notas de Crédito** | Hub 4 (Compras / Acuerdos Mercantiles) | **100% Reglado y Operativo** | 12.4.1 a 12.4.3 | Pendiente reglamentar conciliación automatizada con CFDI de egreso (SAT) emitido por el proveedor. |
| **Ciclo 5: Emisión de PO, Despacho y Kardex de Auditoría** | Hub 4 (Compras / Expediente PO) | **100% Reglado y Operativo** | 12.5.1 a 12.5.3 | Pendiente reglamentar portal extranet B2B para confirmación de fecha de embarque por el proveedor. |
| **Ciclo 6: Recepción Física Inbound en Muelle / Rampa** | Hub 4 (Almacén / Recepción Rampa) | *En Proceso (UI Diseñada)* | Pendiente formalizar | Requiere reglamentar lectura de código de barras 1D/2D en muelle y generación de acta circunstanciada. |
| **Ciclo 7: Liquidación Contable y Cuentas por Pagar (AP)** | Hub 5 (Finanzas / SAT) | *En Proceso (Integración Cross-Hub)* | Pendiente formalizar | Requiere reglamentar cotejo de 3 vías (PO vs Remisión Rampa vs Factura XML CFDI 4.0). |

---

### 12.1 Ciclo Operativo 1: Catálogo, Ingesta & Curaduría de Producto

#### Regla 12.1.1: Fidelidad Taxonómica Estricta en Catálogo & Copiloto IA (Cero Invención de Fibras)
* **Sincronización Mandatoria con Base de Datos:** Los atributos textiles (tipo de tela, composición, acabados) deben sincronizarse dinámicamente con los registros persistidos en la tabla `apparel_fabrics`. Queda prohibido forzar valores por defecto genéricos ("Modal Seamless") en productos cuya ficha o curaduría contenga composiciones distintas.
* **Prohibición de Invención de Fibras en IA:** El prompt enviado a Google Gemini / Copiloto Comercial prohíbe taxativamente inventar fibras o adjetivos técnicos inexistentes en los datos curados. Si la prenda es "Poliéster 95%, Elastano 5%", la IA y la ficha previa deben ceñirse con exactitud fáctica a dicha composición.
* **Reactividad Bidireccional Limpia:** Los selectores taxonómicos no deben contener directivas Blade fijas (`:selected`) que impidan la reactividad bidireccional de Alpine.js al cambiar de producto o variante.

#### Regla 12.1.2: Curaduría Multi-Foto Secuencial 1 a 1 (Lienzo 4:5 y WebP Universal)
* **Carga Simultánea en Cola:** El dropzone admite la selección múltiple de hasta 10 imágenes por producto. El sistema las procesa en una cola secuencial interactiva donde el usuario encuadra y recorta las imágenes una por una.
* **Formato Universal 4:5 y WebP:** Toda imagen aprobada se normaliza a 1080×1350 px (proporción vertical 4:5) sobre lienzo neutral y se persiste en formato `.webp`, optimizando la velocidad de carga en móvil y desktop.

#### Regla 12.1.3: Asignación Cromática Foto-Color en Galería y Portada Destacada (+10%)
* **Erradicación de Redundancia de Color en Variantes:** Se elimina la asignación manual aislada de "Color Principal" en el paso de variantes. La variante cromática se asocia de forma visual, directa y unívoca a cada fotografía de la galería.
* **Portada Destacada:** La fotografía designada como portada oficial se resalta visualmente en la galería de ficha con un tamaño un 10% mayor (`scale-110`, anillo índigo y badge "Portada ★").
* **Conmutación Dinámica en PDP:** Al hacer clic en un swatch de color en la vitrina pública (PDP), las imágenes conmutan instantáneamente al set de fotografías vinculadas a dicho color.

#### Regla 12.1.4: Taxonomías In Situ sin Abandono de Flujo de Captura
* **Creación al Vuelo:** Durante la captura en el Workstation o en la Curaduría de Scraping, el usuario dispone de botones in situ (`+ Categoría`, `+ Tela`, `+ Color`, `+ Vitrina`) que despliegan modales livianos sin recargar la página ni perder los datos ya introducidos.
* **Persistencia y Selección Automática:** Al guardar la nueva entidad vía API, esta se inserta en la memoria reactiva local y se auto-selecciona en el formulario en curso.

#### Regla 12.1.5: Wizard Secuencial de 4 Pasos & Previsualización Vertical 30/70
* **Paginación Ergonómica:** El formulario de captura se distribuye en 4 pasos secuenciales (1. Fotos & Colores, 2. Datos & Diseño, 3. Variantes & Slotting, 4. Copiloto & Pricing), eliminando scrolls verticales interminables.
* **Live Preview Vertical 30/70:** La columna lateral divide su espacio en 30% para la galería visual con chip de color y 70% para la ficha técnica reactiva que recalcula márgenes, SKU y atributos en vivo.

#### Regla 12.1.6: Aislamiento y Gobernanza de Borradores (Publicación Condicionada)
* **Segregación Estricta de Borradores:** Todo producto guardado como borrador (`draft`) se persiste con `visible = false` y se aísla en la colección segregada de borradores. Jamás se exhibe en catálogo operativo ni en vitrinas públicas hasta que recibe visto bueno formal mediante el flujo de aprobación.

---

### 12.2 Ciclo Operativo 2: Gobernanza de Proveedores & Marcas

#### Regla 12.2.1: Estructura Jerárquica de Árbol Proveedor-Marcas (Cero Marcas Huérfanas)
* **Entidad Matriz Proveedor:** El proveedor representa la razón social, persona moral o fabricante directo con quien se pactan condiciones comerciales, plazos de crédito (ej. 30, 60 días), datos fiscales (RFC, régimen) y canales de comunicación.
* **Marcas como Entidades Hijas:** Las marcas comerciales (ej. *Torzo Sexywear*, *Mazho*, *Dajo Underwear*) son submarcas que pertenecen obligatoriamente a un proveedor padre registrado (`supplier_id`).
* **Prohibición de Marcas Huérfanas:** Queda terminantemente prohibido registrar o mantener marcas comerciales desvinculadas de un proveedor matriz en la base de datos o en la interfaz.

#### Regla 12.2.2: Tabuladores MOQ Exclusivos e Independientes por Proveedor (Sin Predefinidos)
* **Independencia Total de Escalas:** Cada proveedor registrado cuenta con su propio tabulador de Cantidad Mínima de Pedido (MOQ) y escalas de precios escalonadas por volumen.
* **Requisito de Alta Previa de Proveedor:** No existen tabuladores genéricos globales predeterminados. Para crear un tabulador MOQ, es mandatorio que el proveedor esté registrado previamente en el catálogo de fabricantes.
* **Estructura Paramétrica:** El tabulador define vigencia, moneda, escalas de volumen (ej. Menudeo, 50, 100, 200, 400 prendas) y filas de costo unitario por categoría o prenda.

---

### 12.3 Ciclo Operativo 3: Planeación de Compras y Tabuladores MOQ

#### Regla 12.3.1: Árbol Único Consolidado de Proveedor y Marcas en Bandeja MOQ (PO Única)
* **Consolidación de Marcas Bajo el Fabricante Padre:** En la Bandeja MOQ de Compras, todos los requerimientos de reabastecimiento generados para las marcas filiales de un mismo proveedor (ej. prendas de *Torzo Sexywear* y prendas de *Mazho*) deben consolidarse dentro de **un único árbol de pedido bajo el proveedor matriz (*Torzo*)**.
* **Generación de PO Unificada:** Queda prohibido dividir a las marcas de un mismo fabricante en órdenes de compra independientes. El volumen acumulado de todas sus marcas suma conjuntamente para alcanzar el umbral MOQ del fabricante y genera una **única Orden de Compra formal (PO)**.

#### Regla 12.3.2: Reactividad Instantánea y Auto-Recálculo ante Modificación de Tabuladores MOQ
* **Sincronización Inmediata sin Recarga:** La Bandeja MOQ debe actualizarse de forma instantánea y reactiva siempre que se cree, modifique, active o elimine un tabulador MOQ en los Ajustes del sistema.
* **Preservación del Estado en Interfaz:** El recálculo de escalas, porcentajes de progreso y costos unitarios se produce en caliente mediante mutación inmutable de arrays y contadores reactivos (`moqRevision`), sin recargar el navegador ni perder filtros o selecciones activas.

#### Regla 12.3.3: Tabulación Dinámica de Costos por Escalas de Volumen y Salto de MOQ
* **Salto de Escala en Tiempo Real:** Al sumar unidades planificadas en el árbol del proveedor y sobrepasar el umbral de una escala superior (ej. saltar de 50 a 100 piezas), el sistema actualiza automáticamente el costo unitario de todas las partidas del fabricante según el precio correspondiente en el tabulador.
* **Actualización en Cascada de Subtotales:** Los subtotales por partida, el subtotal neto del fabricante y la inversión estimada se recalculan en tiempo real, reflejando el ahorro por volumen alcanzado.

#### Regla 12.3.4: Jerarquía de Fallback en Matriz MOQ (Categoría Específica -> General -> Fila Única)
* **Resolución de Costos por Prenda:** Para asignar el costo tabulado a un ítem, el sistema busca en la matriz del proveedor la categoría coincidente con el producto (ej. "Tanga", "Bikini", "Boxer").
* **Fallback Tolerante:** Si la categoría específica no está tipificada, pero el tabulador cuenta con una fila "General" o con una **única fila configurada**, todas las prendas del fabricante adoptarán dicha escala de costos en lugar de quedar sin tabular.

#### Regla 12.3.5: Erradicación Absoluta de Datos Mock en Submódulo de Compras (Datos Reales de BD)
* **Datos 100% Reales y Auditables:** El submódulo de compras, sus árboles de planeación, el expediente PO y el historial de transacciones operan exclusivamente con información real persistida en la base de datos (proveedores reales como Torzo y Dajo B2B, productos reales del catálogo y acuerdos de crédito existentes). Queda erradicado cualquier arreglo mock ficticio en código.

#### Regla 12.3.6: Visibilidad de Ahorro Real ($ y %) y Nivel de Tabulación Activo en Bandeja MOQ
* **Nivel de Tabulación Activo Visible:** Cada árbol de proveedor en la Bandeja MOQ debe mostrar en su encabezado el tabulador de volumen actualmente desbloqueado con base en la suma de prendas planificadas (ej. `Nivel Tabulación Activo: 100 prendas ($75.00/u)`).
* **Cálculo Reactivo de Ahorro Real:** Cada vez que el volumen de compra alcanza una nueva escala de precio preferencial, la interfaz calcula y expone inmediatamente el ahorro económico real conseguido en moneda nacional (`-$X.XX MXN`) y el porcentaje de descuento efectivo (`Y% de ahorro`) frente al costo base o precio inicial, incentivando saltos eficientes de lote.

---

### 12.4 Ciclo Operativo 4: Discrepancias en Recepción, Acuerdos Mercantiles y Notas de Crédito

#### Regla 12.4.1: Pestaña Permanente de Acuerdos Mercantiles y Notas de Crédito en Compras
* **Sub-Pestaña Dedicada en UI:** El módulo de Compras incorpora una pestaña permanente (*"Notas de Crédito y Acuerdos Mercantiles"*) donde residen, se auditan y se gestionan la totalidad de los convenios compensatorios generados con proveedores por incidencias en muelle o rampa.

#### Regla 12.4.2: Trazabilidad, Edición y Referenciación Inmutable de Discrepancias
* **Expediente Completo del Acuerdo:** Cada acuerdo mercantil cuenta con folio único (`ACU-YYYY-XXXX`), folio de factura/remisión afectada, tipificación de discrepancia (unidades faltantes, piezas dañadas o penalización de entrega), desglose de piezas, costo unitario aplicado, importe económico a favor, contacto autorizador del fabricante y notas de minuta.
* **Capacidad de Edición y Trazabilidad:** Los acuerdos pueden consultarse y editarse desde la interfaz mediante modal interactivo, manteniendo la trazabilidad histórica de su estado (`pendiente`, `aplicado`, `cancelado`).

#### Regla 12.4.3: Selector de Acuerdos Mercantiles Pendientes y Compensación Contable en Emisión de PO
* **Selector Dinámico por Proveedor:** En el modal de emisión del Expediente PO, la lista desplegable de compensación de créditos recupera exclusivamente los acuerdos mercantiles en estado `pendiente` correspondientes al proveedor de la orden.
* **Deducción Aritmética Automática:** Al seleccionar un acuerdo, el sistema deduce su monto del total a pagar (`Total = Subtotal + IVA - Compensación de Acuerdo`), imprime la Cláusula 4 con la referencia legal en el PDF de la orden y actualiza el estado del acuerdo en la base de datos al emitirse la PO.

#### Regla 12.4.4: Estricto Aislamiento de Acuerdos por Proveedor y Multi-Selección en Emisión de PO
* **Conmutación Estricta al Proveedor de la Orden:** El listado de acuerdos mercantiles y notas de crédito en el modal de emisión de orden de compra debe filtrar estricta e inviolablemente los acuerdos del proveedor seleccionado (o marcas pertenecientes a dicho fabricante matriz), imposibilitando la contaminación cruzada entre proveedores distintos.
* **Multi-Selección de Acuerdos Pendientes:** El comprador puede seleccionar uno o múltiples acuerdos pendientes simultáneamente mediante casillas de verificación (checkboxes). El sistema suma acumulativamente sus saldos a favor, los deduce del total de la orden y concatena todos los folios de referencia en la Cláusula 4 del contrato formal y en la base de datos al timbrar la PO.

---

### 12.5 Ciclo Operativo 5: Emisión de Orden de Compra (PO), Despacho y Kardex de Auditoría

#### Regla 12.5.1: Snapshot Inmutable y Expediente PO en PDF
* **Membrete Formal y Términos:** La orden emitida cuenta con folio oficial (`PO-YYYY-XXXX`), membrete corporativo, datos fiscales del fabricante, condiciones de entrega, desglose de partidas por curva de tallas y cláusulas de compensación.
* **Persistencia Snapshot:** El documento se almacena íntegro en la base de datos (`po_snapshot`) garantizando que reimpresiones posteriores conserven con exactitud fáctica los precios, acuerdos y partidas pactadas en la fecha de emisión original.

#### Regla 12.5.2: Despacho Electrónico B2B con Token de Acuse de Recibo
* **Notificación al Fabricante:** El modal de despacho permite enviar la orden formal vía correo electrónico al ejecutivo de cuenta del fabricante, adjuntando el PDF estructurado y registrando el evento en el feed de auditoría.

#### Regla 12.5.3: Trazabilidad Transaccional ACID en Kardex de Compras (`purchase_kardex`)
* **Bitácora Inmutable de Compras:** Toda acción relevante del ciclo de abastecimiento (`PO_EMITIDA`, `ON_DEMAND_CONVERTIDO`, `DISCREPANCIA_REGISTRADA`, `ACUERDO_APLICADO`, `INBOUND_RECEPCION`) se asienta cronológicamente en la tabla `purchase_kardex` con folio, operador responsable, monto económico y variación de unidades, garantizando auditoría integral.

---

### 12.6 Ciclo Operativo 6: Segregación de Roles RBAC en Hub 1 (Almacén vs Compras)

#### Regla 12.6.1: Segregación Operativa de Tareas en Hub 1 por Rol RBAC
* **División Clara de Sub-Áreas:** La bandeja operativa del Hub 1 (Bandeja de Entrada Operativa) debe estar formalmente segregada entre:
  1. **Área de Almacén & Empaque Físico (Carlos Almacén):** Centrada exclusivamente en el corte diario DHL de las 14:00 hrs, mesa de empaque físico, recepción ciega en rampa 2, surtido de racks y manifiesto de transporte. Cero visibilidad de márgenes financieros o costos comerciales.
  2. **Área de Compras & Abastecimiento B2B (Elena Compras):** Enfocada en validación de cumplimiento de entregas en muelle/rampa, resolución de discrepancias por faltantes o mermas, disparador de órdenes de reabastecimiento ROP, consolidación de bandejas MOQ y conciliación de acuerdos/notas de crédito con proveedores.
* **Navegación Fluida y Filtros RBAC:** Los usuarios con rol `warehouse` o `packer` acceden por defecto al sub-área de Almacén; los usuarios con rol `purchases` acceden al sub-área de Compras. Los Super Administradores disponen de conmutadores tipo pastilla (*pills*) para alternar fluidamente entre ambas disciplinas operativas sin recarga.

---

### 12.7 Ciclo Operativo 7: Gobernanza de Hubs y Extranet de Proveedores (Hub 5 y Hub 7)

#### Regla 12.7.1: Preservación de Enlaces de Navegación en Sidebar y Gobernanza de Hub 5 (Finanzas)
* **Arquitectura de Navegación Estable:** Los hipervínculos del menú lateral (sidebar) no deben eliminarse destructivamente del DOM mediante directivas Blade de sesión cuando cambian los perfiles de prueba. La capa de permisos RBAC del cliente gestiona la visibilidad y el acceso sin alterar la integridad estructural de los 8 Hubs del panel.

#### Regla 12.7.2: Unificación del Portal Extranet de Proveedores en Hub 7
* **Residencia Centralizada de Extranets:** Toda interfaz destinada a interacción con aliados, socios o proveedores externos debe residir en Hub 7 (*"Ecosistema B2B, Aliados & Proveedores"*). Hub 7 provee un conmutador de tres modalidades:
  1. *Vista de Administración RevOps (Aliados & Convenios)*
  2. *Pantalla Extranet del Aliado (Partner Portal)*
  3. *Pantalla Extranet del Proveedor / Fabricante (B2B Supplier Extranet Portal)* accesible vía `route('portal.vendor.extranet')`.

#### Regla 12.7.3: Gobernanza del Directorio de Aliados y Acuerdos Comerciales desde el Engranaje del Navbar (Superadministrador)
* **Ubicación Exclusiva en Engranaje Navbar:** El alta, edición y configuración del directorio de socios aliados y sus acuerdos comerciales reside formalmente en el botón de engranaje del navbar superior (`open-module-settings`). Este panel está disponible exclusivamente cuando se navega dentro del Hub 7 y su acceso está estrictamente restringido a usuarios con rango de **Superadministrador** (`superadmin`). Funciona de manera idéntica y homologada al alta manual de proveedores de Hub 4.
* **Campos y Acuerdos Gobernables:**
  1. **Datos Generales:** Nombre comercial, código de aliado (auto-generado si se omite), giro/categoría de socio comercial (sauna, spa, creador de contenido / OnlyFans, productor de fiestas / festival circuit, colectivo drag & ballroom, fitness gym, bar/nightclub, etc.), universo afín (`marte`, `venus`, `tiresias`, `terra`), contacto, WhatsApp, email, redes/handle social y notas de alianza.
  2. **Reglas de Compensación Dirección A (Web &rarr; Aliado):** Tarifa contable de crédito por prospecto calificado CAPI (`lead_acquisition_cost`), acreditada al aliado cuando un usuario genera un voucher desde pop-ups o vitrinas.
  3. **Reglas de Compensación Dirección B (Aliado &rarr; Web):** Tipo de comisión (`percentage` o `fixed`) y tasa (`commission_rate`) devengada a favor del aliado por cada orden efectiva liquidada en tienda con su cupón UTM determinista.
  4. **Tipos de Descuentos Autorizados:** Matriz de selección de beneficios que el aliado tiene permitido configurar desde su portal (`cover` libre, `cover_2x1`, `free_drink` trago cortesía, `store_discount` % descuento en tienda, `vip_access` acceso VIP, `service_promo` promoción en servicios) y tope máximo de descuento en tienda (`max_discount_pct`, por defecto 20.00%).
  5. **Datos Bancarios de Liquidación:** Banco receptor y cuenta CLABE de 18 dígitos para dispersiones en Cámara de Compensación.

#### Regla 12.7.4: Política de Solicitud Exclusiva de Eventos por Aliados y Sincronización Automática e Inmutable
* **Facultad Restringida del Aliado (Solicitud Exclusiva):** Los aliados comerciales **únicamente son capaces de solicitar** la dada de alta de su evento o promoción con los que quieren convenir con nosotros. Tienen estrictamente prohibida la publicación directa o desatendida.
* **Bandeja de Entrada en Hub 7:** Toda solicitud enviada desde la extranet del aliado (`POST /aliado/extranet/{token}/campana`) se inserta con estado `pending_approval` y recae en la *Bandeja de Solicitudes de Eventos & Convenios* del Hub 7, con su póster oficial, universo, beneficio propuesto e instrucciones de canje.
* **Sincronización Automática e Inmutable:** Tras la revisión y aprobación por el Superadministrador (`POST /admin/convenios/campanas/{id}/aprobar`), la campaña conmuta a `active` y se propaga **inmediata, veraz y automáticamente** a la página de convenios de explorar del Storefront (`/pagina/convenios`) y al generador de Pop-ups de suscripción en Hub 2. Queda terminantemente prohibido que la información no sea actual, fidedigna o que contenga datos inventados.

#### Regla 12.7.5: Política Oficial de Scraping: Intervalo de 3 Días y Throttling en Desarrollo
* **Frecuencia Oficial de Scraping (Cada 3 Días):** El scraping de catálogos mayoristas de proveedores dropship (`supplier:scrape`) se ejecuta como máximo 1 vez cada 3 días (`every_3_days` / 72 horas) por proveedor, persistiendo la fecha en `suppliers.last_scraped_at` y `supplier_sync_runs`. El comando omite automáticamente proveedores consultados dentro de dicha ventana salvo que se invoque el flag explícito `--force`.
* **Throttling en Entorno de Desarrollo (3 a 5 Segundos):** En entornos `local` o `development`, toda consulta HTTP de extracción debe inyectar una pausa aleatoria obligatoria de 3 a 5 segundos (`sleep(random_int(3, 5))`) entre peticiones sucesivas para evitar penalizaciones de reputación de IP con Google, bloqueos de firewall y saturación de enrutadores locales.

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
| **Gobernanza Marcas** | Asociación Mandatoria a Proveedor | Cero marcas huérfanas; `supplier_id` requerido; sincronización bidireccional reactiva |
| **Fotos & Variantes Color** | Galería Manual con Variante de Color | Entrada ágil de URLs en Workstation y Curaduría; selector de color de prenda por foto y portada |
| **Alta In Situ Taxonomías**| Categorías y Subcategorías al Vuelo | Botones in situ y modal rápido en Workstation y Curaduría sin abandonar la captura de producto |
| **Variantes de Color Ficha** | Conmutación Foto-Color en Drawer (6.53) | Carga de fotos, asignación de color por foto, portada ★ y alta de color in situ en ficha |
| **WebP & Normalización** | WebP Universal & 1080x1350 px (6.54) | Conversión obligatoria a .webp, lienzo 4:5 centrado y fondo blanco neutral para desktop y móvil |
| **Alta In Situ Color** | Color al Vuelo en Workstation (6.55) | Botones + Color y modal interactivo hex/degradado sin abandonar el alta ni recargar |
| **SKU Dinámico** | Reactividad por Atributos (6.56) | Auto-actualización sensible a cambios de proveedor, marca, categoría, tela y color |
| **CRUD de Telas** | Telas en Ajustes & In Situ (6.57) | Gestor CRUD en Pestaña 1 y botón + Tipo de Tela en Workstation con actualización reactiva |
| **Asociación de Telas** | Vínculo Directo Producto-Tela (6.58) | Columnas fabric / fabric_id y relaciones formales Product <-> ApparelFabric |
| **Secciones E-Commerce** | CRUD Secciones & Hub 2 Home (6.59) | Gestor Pestaña 7, modularidad en Home Hub 2 y dimensión transversal para filtros |
| **Bandeja Curaduría Real** | 259 Prendas Torzo & Cero Mocks (6.60) | Poblado real en `scraped_pending_items`, búsqueda reactiva y persistencia end-to-end |
| **Gobernanza Ciclo Scraping** | 5 Fases, Descontinuación & Archivo (6.61) | Mapeo VSC, visto bueno curaduría, frecuencia regulable, modal discrepancias (retirar/archivar/eliminar) y herramienta de archivados |
| **Integración Google Gemini** | Copiloto Producto & Chat Ejecutivo (6.62) | Servicio GeminiService con fallback, descripciones comerciales en Workstation y chat multi-turno en Dashboard |
| **Tipificación Diseño** | Liso vs Con Diseño en Workstation (6.68) | Selector explícito en Paso 2 y badge reactivo en ficha técnica |
| **Vitrinas In Situ** | Alta Rápida de Vitrina Web (6.69) | Botón + Vitrina en Paso 2 sincronizado con ajustes de tienda |
| **Color Multicolor** | Multicolor Arcoíris Nativo (6.70) | Checkbox "Es Multicolor", degradado 6 colores e inmutabilidad de sistema |
| **Workstation Wizard** | 4 Pasos Paginados en Columna 1 (6.71) | Paginación secuencial por pasos (1-4) erradicando desbordamientos |
| **Desacople Color Paso 3**| Color Exclusivo por Foto en Paso 1 (6.72) | Eliminado Color Principal de Paso 3; asignación directa en fotos |
| **Preview 30/70 Col 2** | Galería 30% (Portada +10%) & Datos 70% (6.73) | División vertical ergonómica: fotos 30% y ficha técnica en vivo 70% |
| **Fidelidad Taxonómica**| Telas y Atributos Curados en IA & Resumen (6.74) | Cero invenciones de fibras ("modal"); sincronización real con BD y prompt estricto |
| **Curaduría Multi-Foto**| Carga Múltiple & Recorte Secuencial 1 a 1 (6.75) | Cola de fotos en dropzone; recorte interactivo una por una y pricing pulido |
| **Login RevOps Aislado** | Pantalla de Ingreso Exclusiva & Cero Chrome Storefront (6.76) | Vista dedicada centrada; erradicación total de navbar, carrito, búsqueda y filtros de tienda |
| **Gobernanza Ciclos Operativos** | Estructuración por Ciclos de Valor (12.0) | Mapeo de ciclos operativos, auditoría de cobertura e identificación explícita de brechas |
| **Fidelidad Taxonómica Catálogo**| Correspondencia Textil Estricta (12.1.1) | Cero invenciones de fibras; telas sincronizadas con `apparel_fabrics` y prompt veraz |
| **Curaduría 4:5 WebP** | Estandarización Universal (12.1.2) | Carga en cola, recorte interactivo 1 a 1 y conversión mandatoria a `.webp` |
| **Foto-Color & Portada 10%** | Conmutación Visual & Portada Mayor (12.1.3) | Color por foto en Paso 1, portada +10% en ficha y conmutación automática en PDP |
| **Taxonomías In Situ** | Alta al Vuelo sin Salir de Captura (12.1.4) | Botones + Categoría, + Tela, + Color, + Vitrina con modales livianos e inserción reactiva |
| **Árbol Proveedor-Marcas** | Jerarquía Matriz & Cero Marcas Huérfanas (12.2.1) | Marcas hijas con `supplier_id` mandatorio consolidando volumen del fabricante padre |
| **Tabuladores MOQ Independientes**| Escalas Exclusivas por Proveedor (12.2.2) | Tabuladores individuales sin predeterminados; requiere proveedor registrado previo |
| **Bandeja MOQ Consolidada** | Pedido Único por Fabricante y Marcas (12.3.1) | Consolidación de marcas bajo el proveedor padre en un solo árbol generando 1 sola PO |
| **Reactividad MOQ Instantánea**| Recálculo Dinámico en Caliente (12.3.2) | Actualización automática de la Bandeja MOQ al crear, editar o borrar tabuladores |
| **Salto de Escala de Costo** | Tabulación Automática por Volumen (12.3.3) | Actualización en tiempo real del costo de partidas al superar umbrales de escala MOQ |
| **Fallback en Matriz MOQ** | Tolerancia Categórica Jerárquica (12.3.4) | Categoría específica &rarr; General &rarr; Fila única para evitar partidas sin costo |
| **Cero Mocks en Compras** | Datos 100% Reales y Auditables (12.3.5) | Erradicación total de mocks; persistencia en BD de proveedores, lotes y acuerdos |
| **Acuerdos y Notas de Crédito** | Pestaña Dedicada de Acuerdos Mercantiles (12.4.1)| Submódulo permanente en Compras para auditar convenios por incidencias en rampa |
| **Trazabilidad Discrepancias** | Edición y Expediente de Acuerdo (12.4.2) | Folio, remisión, piezas, costo, importe, contacto y estados `pendiente`/`aplicado` |
| **Compensación Contable en PO**| Deducción Automática en Emisión de PO (12.4.3) | Selector de acuerdos pendientes que descuenta el saldo del total a pagar en la PO |
| **Expediente PO en PDF** | Snapshot Fidedigno e Inmutable (12.5.1) | Persistencia íntegra de la PO emitida (`po_snapshot`) para reimpresión histórica idéntica |
| **Despacho B2B por Correo** | Notificación con Token de Acuse (12.5.2) | Envío formal por email al ejecutivo del taller con PDF y registro en auditoría |
| **Kardex Compras ACID** | Bitácora Transaccional Inmutable (12.5.3) | Registro cronológico en `purchase_kardex` de cada hito económico y logístico |
| **Ahorro Real en MOQ** | Nivel Activo y Ahorro $ / % (12.3.6) | Visibilidad reactiva de nivel de tabulación, dinero real ahorrado ($) y porcentaje de descuento (%) |
| **Multi-Acuerdos en PO**| Estricto Aislamiento y Multi-Selección (12.4.4) | Selector filtrado por proveedor con selección de múltiples notas de crédito y suma acumulada |
| **Segregación Hub 1 RBAC**| Almacén vs Compras (12.6.1) | Sub-área Carlos Almacén (DHL 14:00, rampa ciega) vs Elena Compras (discrepancias, MOQs, acuerdos) |
| **Gobernanza Sidebar** | Preservación de Hub 5 Finanzas (12.7.1) | Navegación estable en el DOM sin mutilación de enlaces; control por capa de permisos RBAC |
| **Extranet Proveedores** | Portal Unificado en Hub 7 (12.7.2) | Residencia de Extranet B2B de Proveedores en Hub 7 conmutando con Aliados y RevOps Admin |
| **Hub 7 Directorio Aliados** | Configuración Engranaje Navbar (12.7.3) | Alta manual de aliados, Dir A (leads CAPI), Dir B (comisiones %) y descuentos autorizados exclusiva en engranaje para Superadmin |
| **Hub 7 Política Solicitud** | Solicitud Exclusiva de Aliados (12.7.4) | Aliados solo solicitan eventos; bandeja de autorización y sincronización automática Hub 7 <-> Hub 2 <-> Storefront |
| **Política Scraping 3 Días** | Frecuencia 72h & Delay Dev 3-5s (12.7.5) | 1 corrida cada 3 días salvo `--force`; delay 3-5s en entorno local para protección de IP |
| **Sincronización Automática Catálogo** | Sincronización Reactiva Omnicanal (3.3) | Principio Inamovible: actualización automática de categorías, colores, tallas y variantes entre Hub 4, Hub 2 y Storefront |
| **Documentación** | Tríptico Documental & Compilación PDF | Markdown + HTML estilizado + PDF vía Chrome headless |
| **Git** | Commits Atómicos Semánticos | Mensajes estructurados con ID de tarea formal |


