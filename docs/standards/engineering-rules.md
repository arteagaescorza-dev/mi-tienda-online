# Normas de Ingeniería, Arquitectura & Gobernanza Técnica (SSOT)
## Plataforma RevOps E-Commerce Multiverso & ERP (Mi Tienda Online)

**Versión del Documento:** 3.0 (SSOT Canon)  
**Fecha de Publicación:** Octubre 2026  
**Ámbito:** Estándares Obligatorios de Desarrollo, Buenas Prácticas y Directrices de Arquitectura  
**Referencia Histórica:** Archivo consolidado en `docs/archivo/legacy-specs/PROJECT_RULES.md`

---

## 1. Manifiesto de Ingeniería & Principios Fundamentales

### Regla 1.1: Enfoque Fullstack Senior End-to-End (Vertical Slice)
Queda estrictamente prohibido el desarrollo puramente cosmético, superficial o basado en maquetas desconectadas. Toda nueva funcionalidad, pantalla o ajuste debe desarrollarse como un **Vertical Slice completo (End-to-End)**:

$$\text{Migración MySQL} \longrightarrow \text{Modelos Eloquent} \longrightarrow \text{Servicios Transaccionales (ACID)} \longrightarrow \text{Endpoints REST / FormRequests} \longrightarrow \text{UI PWA Reactiva} \longrightarrow \text{Pruebas Automatizadas}$$

* **Cero Datos Falsos en Producción:** Ningún botón, selector o interruptor puede carecer de persistencia real en la base de datos o capa de caché operativa.
* **Confirmación Inmediata de Usuario:** Toda acción del operador o cliente debe proporcionar retroalimentación visual (spinners de carga, animaciones de éxito o toasts con stack acumulativo y z-index máximo).

### Regla 1.2: Preservación de la Integridad del Código y Comentarios
* Se debe preservar de forma rigurosa la totalidad de comentarios, docstrings y anotaciones existentes en los archivos modificados, a menos que un requerimiento técnico explícito ordene su reemplazo.
* No se deben alterar métodos o controladores preexistentes de otros submódulos sin verificar que la suite completa de pruebas unitarias y funcionales continúe ejecutándose sin errores (`exit code 0`).

### Regla 1.3: Sincronización Mandatoria con las Especificaciones de Módulo
Ninguna tarea de la plataforma se da por concluida sin que su arquitectura de persistencia, migración de base de datos, modelo Eloquent, endpoints RESTful con esquemas JSON y reglas de validación queden formalizados en `docs/modules/`.

---

## 2. Estándares Universales de Codificación y Datos

### Regla 2.1: Declaración Universal y Obligatoria de Codificación UTF-8
* **Estándar Inmutable UTF-8:** Todo el proyecto (código fuente, base de datos, comunicaciones HTTP y documentación técnica) se declara y opera bajo codificación **UTF-8 universal** (MySQL `utf8mb4_unicode_ci` / conjunto de caracteres `utf8mb4`). Queda estrictamente prohibido el empleo de codificaciones legadas o regionales (ISO-8859-1, Windows-1252, Latin1).
* **Cabeceras HTTP y Vistas Blade:** Todas las plantillas Blade y documentos HTML deben declarar explícitamente `<meta charset="utf-8">`. Las respuestas del servidor deben emitir cabeceras `Content-Type: text/html; charset=UTF-8` o `application/json; charset=UTF-8`.
* **Archivos Fuente:** Todo archivo `.php`, `.js`, `.css`, `.json`, `.md` y `.html` debe guardarse obligatoriamente en codificación UTF-8 sin marca de orden de bytes (BOM).
* **Sanitización Activa contra Mojibake:** Los modelos Eloquent clave implementan sanitización activa para corregir en caliente cualquier registro histórico que presente doble codificación UTF-8 (`Ã³`, `Ã±`, `Ã¡`, etc.).

### Regla 2.2: Política Estricta Cero Emojis (Zero Emojis Canon)
Queda estrictamente prohibido el empleo de caracteres emoji o símbolos pictográficos Unicode (`\u{1F300}-\u{1F9FF}`, `\u{2600}-\u{26FF}`, `\u{2700}-\u{27BF}`) en el código fuente, vistas Blade, hojas de estilo CSS, scripts JS, respuestas JSON y textos de interfaz.
* **Iconografía 100% Vectorial SVG:** Todos los elementos gráficos, estados y botones deben construirse exclusivamente mediante **iconos SVG vectoriales limpios** (Feather/Lucide style).
* **Validación Automatizada:** Toda suite de pruebas y scripts de integración continua ejecutan expresiones regulares para garantizar que ningún emoji ingrese a los repositorios.

---

## 3. Persistencia, Transaccionalidad y Gobernanza de Inventario

### Regla 3.1: Transaccionalidad y Atomicidad en Kardex y Finanzas (ACID)
* Todo movimiento de mercancía (entradas de proveedor, ensambles de recetas BOM, apartados por checkout, despachos en mesa de expedición o retornos RMA) debe ejecutarse bajo transacciones de base de datos (`DB::transaction`).
* El Kardex transaccional de Hub 4 es la **fuente única e incontrovertible de verdad** para los inventarios. Ninguna pantalla o controlador puede alterar existencias mediante asignaciones directas sin asentar el registro histórico correspondiente en el Kardex.
* Se debe garantizar el cálculo riguroso del **Costo Promedio Ponderado (CPP)** en cada entrada de compra:
  $$\text{Nuevo CPP} = \frac{(\text{Stock Actual} \times \text{CPP Previo}) + (\text{Cantidad Nueva} \times \text{Costo Unitario Nuevo})}{\text{Stock Actual} + \text{Cantidad Nueva}}$$

### Regla 3.2: Desacoplamiento de Vitrinas frente al Stock Central (Single Inventory Pool)
* La plataforma opera un único almacén físico central y catálogo unificado en **Hub 4 (Inventario)** que abastece a cuatro vitrinas multiverso diferenciadas (`Marte`, `Venus`, `Tiresias`, `Terra`).
* Ninguna vitrina opera con inventarios duplicados. La asignación a una vitrina se gestiona mediante reglas taxonómicas, marcas (`brand`), categorías y filtros dinámicos, nunca clonando SKUs en la base de datos.
* Si una prenda agota su stock disponible en el Kardex central, se oculta o inhabilita automáticamente para la venta en todas las vitrinas donde esté presente.

---

## 4. Normalización de Medios, Rendimiento & Core Web Vitals

### Regla 4.1: Estándar Exclusivo WebP para Imágenes (WebP-Only)
Queda terminantemente prohibido almacenar o servir imágenes en formatos pesados no optimizados (PNG sin compresión, BMP o JPG tradicionales). Toda fotografía de producto, banner de vitrina o imagen de scraping se convierte y almacena obligatoriamente en formato **`.webp`**:
* **Compresión:** Calidad 82% a 85%.
* **Resolución Máxima:** `1080px` de ancho.
* **Proporción de Ficha Textil:** Recorte vertical exacto **4:5** (`1080x1350px`) para catálogo de moda íntima.
* **Peso Máximo:** Estrictamente **menor a 100 KB** por fotografía para garantizar un LCP (Largest Contentful Paint) menor a 1.8 segundos en conexiones móviles 4G.

---

## 5. Diseño de Interfaz & Design System RevOps

### Regla 5.1: Jerarquía de Curvatura (Corner Radius) y Minimalismo Industrial
La plataforma unifica todos sus componentes bajo una arquitectura visual limpia:
* `4px` para badges, tags, pills y botones de ayuda contextual `?`.
* `6px` para botones `.btn`, inputs y tarjetas de acción.
* `8px` para paneles `.panel`/`.card` y contenedores de tablas.
* `12px` exclusivamente para modales flotantes y drawers deslizables.
* Se prohíben curvaturas mayores a 12px (`rounded-2xl` informales) y bordes completamente afilados (`0px`).

### Regla 5.2: Paleta Cromática Semántica de 8 Tokens `:root`
Cada vitrina multiverso inyecta dinámicamente sus 8 tokens semánticos en `:root`:
- `--brand-primary`: Tono primario dominante de botones y tipografía principal.
- `--brand-secondary`: Tono de soporte y badges listos.
- `--brand-accent`: Color de acento, precio destacado y CTAs activos.
- `--brand-bg`: Color del lienzo de fondo (`body.store-body`).
- `--brand-surface`: Fondo de tarjetas, barras y modales.
- `--brand-text`: Color de texto primario y encabezados.
- `--brand-muted`: Color de descripciones secundarias y separadores.
- `--brand-border`: Color de bordes y delimitadores de cuadrícula.

### Regla 5.3: Ajustes del Sistema a Pantalla Completa
Toda interfaz de configuración y ajustes (tanto los Ajustes del Sistema en el sidebar como los Ajustes de Módulo en la cabecera) se renderiza estrictamente a pantalla completa dentro del contenedor principal (`<main id="mainContent">`). Queda terminantemente prohibido el uso de modales flotantes o drawers laterales para paneles de ajustes extensos.

---

## 6. Arquitectura de Despliegue & Restricciones de Infraestructura

### Regla 6.1: Operación Ligera para Hosting Compartido (cPanel / HostGator)
La arquitectura de la aplicación debe operar de forma óptima en entornos sin demonios permanentes ni memoria Redis dedicada:
* **Colas Ligeras:** `QUEUE_CONNECTION=database` procesadas periódicamente vía Cron con bandera `--stop-when-empty --max-time=50`.
* **Scraping Desacoplado:** El proceso pesado de scraping con Puppeteer/Chromium corre en la máquina del operador y remite lotes JSON estructurados al endpoint seguro de ingestión `POST /api/v1/scraper/ingest`.
* **Bloqueos Temporales en Base de Datos:** Las reservas de stock en carrito (`stock_locks` con TTL de 300 segundos) se almacenan en tablas MySQL indexadas, garantizando liberación automática de inventario si el usuario abandona la sesión de checkout.

---

## 7. Protocolo de Pruebas Automatizadas & Aprobación de Cambios

1. **Suite 100% Verde:** Ningún commit o merge a `master` es válido si alguna prueba de PHPUnit falla.
2. **Aislamiento de Entorno:** Todas las pruebas de características deben utilizar `RefreshDatabase` o transacciones para no dejar datos residuales en entornos de prueba.
3. **Comprobación de Regresión:** Cualquier modificación en controladores compartidos (`StorefrontController`, `PortalStoreController`, `CartService`) requiere la ejecución completa de la suite de pruebas del proyecto:
   ```bash
   php artisan test
   ```

---

## 8. [TODO: Definir regla exacta]

1. `[TODO: Definir regla exacta]` **Política de rotación y depuración automática de imágenes de productos archivados en disco.**  
   *Nota actual:* Los productos archivados conservan sus fotos WebP en `storage/app/public/products/`. Debe definirse si se eliminan tras 90 días de inactividad o si se preservan indefinidamente.
2. `[TODO: Definir regla exacta]` **Estrategia de respaldo off-site diario para base de datos MySQL.**  
   *Nota actual:* El script actual genera dump local vía mysqldump; se debe formalizar el destino de réplica externa (S3, Cloudflare R2 o SFTP remoto).
