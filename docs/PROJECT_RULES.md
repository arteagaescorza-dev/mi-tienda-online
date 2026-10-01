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
* Se eliminan todas las referencias a CLABE interbancaria BBVA o validación manual de comprobantes en el storefront para erradicar fricción operativa y riesgos de conciliación.

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
| **CMS Hub 2** | Hero Excluyente & Regla de Oro | Cero duplicados; catálogo sin custom code; editor visual en Tab 1 |
| **Seguridad** | Sanitización XSS & RBAC | Limpieza en backend de inputs HTML/CSS; aislamiento de métricas |
| **Calidad** | 100% Tests Green & Auditoría CDP | PHPUnit sin errores; pruebas en Chrome Headless |
| **Documentación** | Tríptico Documental & Compilación PDF | Markdown + HTML estilizado + PDF vía Chrome headless |
| **Git** | Commits Atómicos Semánticos | Mensajes estructurados con ID de tarea formal |
