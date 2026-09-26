# Hoja de Ruta del Proyecto

## Contexto

`mi-tienda-online` es una plataforma de e-commerce B2C para ropa interior, con CRM y operaciones RevOps. Usa Laravel 13, PHP 8.3, MySQL, Blade y Vite. Se prepara para desplegarse en hosting compartido mediante SFTP, cron y colas con base de datos.

## Estado Actual

- Catalogo publico, autenticacion, carrito API y CRM basico de leads existen.
- Hay modelos y migraciones para productos, variantes, inventario, proveedores, carritos y pedidos.
- Checkout ya tiene endpoint transaccional y formulario conectado; pedidos y portal administrativo aun no estan completamente conectados a datos operativos.
- La suite base ejecuta migraciones en SQLite en memoria y `php artisan test` pasa con 12 pruebas y 41 aserciones.

## Modulos del Producto

El menu objetivo contiene estos modulos. Ninguno debe considerarse descartado; los estados solo indican el nivel actual de implementacion.

| Modulo | Estado actual | Dependencia o siguiente alcance |
| --- | --- | --- |
| Dashboard | KPIs operativos basicos | KPIs reales de inventario, finanzas y embudo completo |
| Tienda | Parcial | Catalogo persistente, checkout y pedidos reales |
| Leads | Basico | Segmentacion, actividad y seguimiento |
| CRM | Pendiente | Contactos, historial, tareas y pipeline comercial |
| Scoring | Vista simulada | Reglas, eventos y ajustes de puntaje persistentes |
| Inventario | Consulta y ajustes auditables | Reservas y dropshipping completo |
| Chatbot | Pendiente | Conversaciones, derivacion a lead y canal de soporte |
| Atribucion | Modelo existente | Captura UTM, sesiones y atribucion de conversion |
| Emailing | Pendiente | Plantillas, campanas, envios y metricas |
| Cupones | Pendiente | Reglas, vigencia, limites y aplicacion en checkout |
| Finanzas | Vista simulada | Transacciones, costos, reembolsos y margen real |
| Conciliacion | Pendiente | Comparacion de pagos de pasarela contra pedidos y movimientos |
| Logistica | Pendiente | Envios, guias, estados, incidencias y RMA |
| Garantias | Pendiente | Solicitudes, evidencias, dictamen y resolucion |
| Proveedores | Sincronizacion programada basica | Mapeo completo de variantes, monitoreo y automatizacion avanzada |

## Orden de Dependencias

1. Tienda, inventario y proveedores forman la base transaccional.
2. Checkout, cupones, pagos, logistica, garantias y finanzas dependen de pedidos reales.
3. Leads, atribucion, CRM, scoring, emailing y chatbot comparten la informacion del cliente y sus eventos.
4. Conciliacion y dashboard se completan al contar con transacciones y datos operativos confiables.

## Prioridad 1: Estabilizar Pruebas

Objetivo: recuperar una suite base verde antes de ampliar flujos de negocio.

1. Completado: habilitar migraciones en `tests/Feature/ExampleTest.php` para las pruebas que cargan la pagina principal.
2. Completado: ejecutar `php artisan test`; resultado actual: 12 pruebas y 41 aserciones correctas.
3. Completado para carrito API: `CartApiTest` cubre lectura, alta, actualizacion y eliminacion de articulos.
4. Pendiente: ampliar cobertura para productos API y autenticacion.

Terminado cuando: `php artisan test` finaliza sin fallos y cubre al menos los flujos actuales de API.

## Prioridad 2: Autorizacion del Portal

Objetivo: restringir las rutas `/admin/*` a usuarios con rol `portal`.

1. Completado: crear y registrar un middleware de rol reutilizable.
2. Completado: aplicarlo a dashboard, scoring, inventario y finanzas.
3. Completado: `PortalAuthorizationTest` cubre acceso permitido para `portal` y respuesta `403` para `customer`.

Terminado cuando: un cliente autenticado no puede acceder a ninguna ruta administrativa.

## Prioridad 3: Checkout UI Primero

Objetivo: alcanzar primero paridad visual con la version 1 y validar toda la experiencia visual antes de ampliar la funcionalidad de cada paso.

### Estrategia de migracion UI-first

1. Migrar pantallas, componentes, jerarquia, estados y responsive de la version 1.
2. Adaptar cada pantalla a las rutas y datos de la version 2 sin conectar logica nueva innecesaria.
3. Validar visualmente desktop y movil antes de implementar interacciones pendientes.
4. Implementar la interaccion frontend de cada componente.
5. Crear o ampliar backend, persistencia e integraciones despues de validar la UI.

### Fase A: Pantallas y navegacion

1. Completado: pantalla de revision del carrito en `/checkout`.
2. Completado: pantalla de datos de envio en `/checkout/datos`.
3. Completado: pantalla de pago en `/checkout/pago`, identificando el metodo pendiente de integracion.
4. Completado: pantalla de confirmacion en `/checkout/exito`.
5. En progreso: unificar indicadores de paso, resumen de compra, estados vacio/carga/error y version movil.
6. Pendiente: validar visualmente el flujo completo en desktop y movil con datos reales de catalogo.

### Fase B: Funcionalidad por paso

1. Completado: sincronizar el carrito local con `PUT /api/cart/sync` y usar precios del servidor.
2. Completado: crear `Order` y `OrderItem` desde los articulos del carrito en una transaccion.
3. Completado: validar direccion, contenido del carrito, stock y doble envio en servidor.
4. Completado: reservar inventario distribuido por ubicacion y marcar el carrito como `ordered`.
5. Completado: mostrar pedidos reales del usuario autenticado en `/mis-pedidos`, incluyendo sus variantes.
6. Pendiente: conectar la pantalla de pago con una pasarela real y actualizar `payment_status` mediante webhooks.
7. Pendiente: agregar cupones, costo de envio, impuestos y recalculo visible antes de confirmar.

Terminado cuando: todas las pantallas del checkout tienen una experiencia consistente y cada paso se conecta a su funcionalidad real sin datos simulados.

## Prioridad 3B: Migracion del Panel Administrativo a 6 Hubs (Activa)

Objetivo: adoptar el prototipo UI-first de 6 Hubs Operativos (fuente en
`C:\Users\Mickey\Documents\export`) como panel administrativo real, siguiendo
4 fases con compuerta de aprobacion. Ver el plan completo, mapa de rutas y
tareas priorizadas en [docs/admin-panel-migration.md](admin-panel-migration.md).

Regla de bloqueo: la Fase 2 (tablas/migraciones del backend) no inicia hasta
que la Fase 1 (UI) quede aprobada explicitamente y registrada en ese documento.

## Prioridad 4: Catalogo e Inventario Operativos

Objetivo: eliminar los datos de demostracion donde ya existe un modelo persistente.

1. En progreso: conectar la pagina de productos y detalle a `Product`, `ProductVariant` y categorias; las tarjetas exponen `variant_id` real cuando existe catalogo persistente.
2. Completado: seleccionar la variante correcta por talla y color en las tarjetas persistentes, actualizando `variant_id`, precio y clave de carrito.
3. En progreso: dashboard e inventario consultan pedidos, leads, carritos, stock y proveedores; faltan finanzas y embudo completo.
4. En progreso: añadir movimientos auditables y ajustes atómicos de stock, respetando reservas.
5. Completado: conectar controles web por ubicación, con confirmación, errores y protección por rol.
6. En progreso: sincronizar disponibilidad, precio y stock `dropship` en `SupplierProduct` e `Inventory`.
7. En progreso: definir reglas completas frente a dropshipping y mapear variantes externas por talla/color.
8. En progreso: invocar Torzo y Adrenaline Forte mediante `supplier:scrape`, con `Http::fake` en pruebas.
9. En progreso: programar `supplier:scrape` diariamente a las 02:00 sin solapamientos.
10. En progreso: mapear stock externo por talla cuando existen variantes locales.
11. En progreso: registrar estado, fechas, cantidad sincronizada y error de cada ejecucion de scraping.
12. Completado: cubrir fallos HTTP con estado `failed`, conservar el error y probar respuestas 503 con `Http::fake`.
13. En progreso: mostrar historial de sincronizaciones en el portal con estado, fecha, cantidad y error.
14. En progreso: persistir correspondencias externas por talla y stock en `supplier_variant_mappings`.
15. En progreso: evitar asignaciones ambiguas cuando falta color y mostrar el mapa de variantes en el portal.
16. En progreso: filtros por proveedor/estado y retencion configurable de ejecuciones.
17. En progreso: reglas persistentes por proveedor para traducir colores externos a locales.
18. En progreso: administrar estas reglas desde una pantalla del portal y ampliar normalizacion de nombres.

Terminado cuando: catalogo, stock y portal de inventario provienen de la base de datos.

## Prioridad 5: CRM y Automatizaciones

Objetivo: implementar el ciclo de captacion y recuperacion de clientes.

1. Persistir eventos y ajustes de score de leads.
2. Registrar atribucion UTM y eventos de carrito abandonado.
3. Crear reglas de lead scoring y transiciones de estado.
4. Implementar jobs de recuperacion por email con cola `database`.
5. Preparar integracion de WhatsApp como canal posterior.

Terminado cuando: un carrito abandonado genera o actualiza un lead, registra su score y programa una accion de seguimiento.

## Prioridad 6: Operacion y Produccion

Objetivo: preparar los flujos para venta real y despliegue seguro.

1. Elegir e integrar una pasarela de pago.
2. Implementar envios, guias, RMA, transacciones y reembolsos.
3. Configurar cron para colas, scraping y tareas programadas en hosting compartido.
4. Configurar logging, monitoreo de errores y respaldos de base de datos.
5. Documentar variables de entorno, despliegue SFTP y procedimiento de rollback.

Terminado cuando: se puede desplegar, cobrar, registrar el pedido, despacharlo y observar errores operativos.

## Proximo Paso Recomendado

Completar la fase visual del checkout antes de seguir ampliando backend:

> Unificar el indicador de pasos, los estados de carga/error/vacio y la vista responsive de `/checkout`, `/checkout/datos`, `/checkout/pago` y `/checkout/exito`.

## Notas de Seguridad

- No desplegar la cuenta de desarrollo con contrasena `1234`.
- No almacenar contrasenas en texto plano; Laravel debe usar siempre hashes mediante `Hash::make`.
- Las rutas administrativas deben contar con autorizacion por rol antes de exponerlas.