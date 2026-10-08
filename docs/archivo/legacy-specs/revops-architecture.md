# Arquitectura RevOps para mi-tienda-online

## Objetivo
Crear una plataforma integral de E-commerce + CRM orientada a ropa interior B2C, con una arquitectura RevOps que centralice ventas, marketing y administración.

## Visión general
La aplicación se construye sobre Laravel 13 con PHP 8.3 y MySQL. El frontend usa Vite para recursos modernos, mientras que el backend expone APIs REST para catálogo, carrito y pedidos.

La arquitectura está pensada para un hosting compartido básico (Hostgator / cPanel), por lo que prioriza:
- uso de `database` y `file` drivers compatibles con entornos sin acceso a procesos persistentes
- tareas programadas mediante cron en cPanel
- despliegue por SFTP desde local

## Capas principales

### 1. Presentación
- Frontend Web: Blade + Vite + Tailwind
- APIs públicas: `routes/api.php`
- Carrito y checkout modernos para cliente

### 2. Aplicación
- Controladores de dominio: catálogo, carrito, pedidos, CRM, marketing
- Modelos Eloquent: `Product`, `ProductVariant`, `Supplier`, `Cart`, `Order`, `Lead`, `Attribution`, etc.
- Colas: jobs para scraping, notificaciones y sincronización de stock.

### 3. Datos
- MySQL normalizado con tablas de inventario, variantes, proveedores, pedidos, eventos y leads.
- Campos JSON para metadatos flexibles.
- Tablas de auditoría y logs opcionales en migraciones futuras.

## Módulos RevOps

### Inventario Híbrido
- Productos físicos y dropship.
- Variantes por talla, color y categoría.
- `inventories` controla stock físico y reservas.
- `supplier_products` mapea catálogo externo para dropshipping.

### Dropshipping por Scraping
- Proveedor externo se sincroniza con un catálogo local.
- Pedido dropship entra en estado de verificación manual.
- Admin confirma disponibilidad antes de emitir enlace de pago.

### CRM Propio
- Leads generados desde carritos abandonados y formularios.
- `attributions` captura parámetros UTM y sesión.
- `lead_scores` puntúan comportamientos.
- Datos enlazados a usuario, carrito y pedido.

### Automatización de Marketing
- Email interno para campañas y recuperación de carrito.
- Jobs en cola para envío y seguimiento.
- WhatsApp API integrada en futuro para mensajes automatizados.

### Operaciones y Finanzas
- Gestión de pedidos, envíos y RMA.
- `transactions` para ventas y reembolsos.
- `shipments` para control de guías y estado.
- `coupons` para fidelización.

## CRM y Lead Scoring
### CRM interno
- `Lead`: registro central de contactos y prospectos.
- `LeadStatus`: estados como `new`, `contacted`, `nurturing`, `opportunity`, `converted`, `lost`.
- `Attribution`: se conecta a cada lead para asignar la fuente de tráfico.
- `events`: historial de interacciones, visitas, apertura de email, carrito abandonado.
- `messages`: canal omnicanel para email, WhatsApp y chat.

### Lead scoring
- Cada `Lead` suma puntos según comportamiento:
  - visita al producto: +1
  - agrega al carrito: +5
  - abandona carrito: +10
  - envío de formulario: +20
  - apertura de email: +2
  - respuesta en WhatsApp: +15
- `lead_scores` guarda cada ajuste de puntaje y la razón.
- Las reglas se almacenan en código inicial, pero pueden parametrizarse en futuro.
- Scores totales permiten ordenar leads por probabilidad de conversión.

### Casos de uso
- Carrito abandonado: crear/actualizar lead y enviar campaña de recuperación.
- Prospecto nuevo: registrar UTM y generar tareas de seguimiento.
- Lead caliente: pasar a `opportunity` cuando la puntuación supera un umbral.

## Flujo de datos principal
1. Usuario llega con UTM: `attributions` guarda la fuente.
2. Explora catálogo, selecciona variantes y crea carrito.
3. Carrito se guarda en `carts` con sesión o usuario autenticado.
4. Si abandona, se registra un lead y se programa recuperación.
5. Al pagar, se genera `order`, se reserva inventario y se dispara fulfillment.
6. Si es dropship, el pedido espera verificación manual de proveedor.
7. Un módulo de finanzas registra la transacción final.

## Integraciones
- WhatsApp oficial: outbound/inbound.
- Email: SMTP o `log` en desarrollo.
- Pagos: placeholder para gateway externo.
- Hosting SFTP: sincronización de archivos y build estático.

## Consideraciones de hosting compartido
- Usar `QUEUE_CONNECTION=database` y cron para `artisan queue:work --once`.
- Evitar dependencias de Redis/Memcached si no están disponibles.
- Usar `SESSION_DRIVER=database` y `CACHE_STORE=file` si no hay soporte externo.
- Construir activos locales y subir por SFTP.

## Estrategia de despliegue
1. Desarrollar localmente con Laragon.
2. Generar build de Vite: `npm run build`.
3. Subir `public/`, `app/`, `routes/`, `database/migrations/`, `resources/`, `vendor/` y `.env` por SFTP.
4. Ejecutar migraciones remotas si se puede, o aplicar cambios SQL en cPanel.

## Siguientes pasos
- Añadir un `RouteServiceProvider` o un enrutamiento explícito si el proyecto crece.
- Integrar logging y monitoreo de errores.
- Diseñar un diagrama de flujo de datos para analítica RevOps.
- Implementar el CRM y lead scoring como módulos separados.
