# Hub 3: CRM & Marketing

Este documento define la arquitectura, contratos de datos y reglas de negocio para el Hub de CRM y Marketing. Refleja la realidad actual de la base de código.

## 1. Propósito
Gestionar clientes potenciales (leads), automatizar el marketing, recuperar carritos abandonados, manejar interacciones de chatbots y rastrear la atribución de ventas.

## 2. Modelo de Datos (Contratos de Datos)

### Leads y CRM
*   **Modelo:** `App\Models\Lead`
*   **Tabla:** `leads`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Almacena la información principal de los clientes potenciales.

*   **Modelo:** `App\Models\LeadInteraction`
*   **Tabla:** `lead_interactions`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Registra cada interacción (toque) con un lead. Reemplaza la funcionalidad aislada de *lead scoring*, integrando la evaluación del comportamiento directamente en el historial de interacciones o metadatos JSON.

### Marketing y Atribución
*   **Modelo:** `App\Models\Attribution`
*   **Tabla:** `attributions`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Rastrea el origen y la campaña que generó una conversión o interacción (UTMs, referidos).

*   **Modelo:** `App\Models\EmailAutomation`
*   **Tabla:** `email_automations`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Gestiona las campañas de correo electrónico automatizadas.

### Carritos y Recuperación
*   **Modelo:** `App\Models\Cart`
*   **Tabla:** `carts`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Almacena los carritos de compra activos (y abandonados).

*   **Modelo:** `App\Models\CartRecoveryToken`
*   **Tabla:** `cart_recovery_tokens`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Tokens seguros generados para enlaces enviados a clientes con carritos abandonados, permitiendo su recuperación directa.

### Chatbots
*   **Modelo:** `App\Models\ChatbotConversation`
*   **Tabla:** `chatbot_conversations`
*   **Estado:** `[IMPLEMENTADO]`
*   **Descripción:** Registro de las interacciones automatizadas de soporte o ventas vía chatbot.

## 3. Lógica de Negocio y File Scope

### Controladores
*   **`App\Http\Controllers\PortalMarketingController`** `[IMPLEMENTADO]`
    *   Gestiona la interfaz de administración del portal para campañas, leads y configuraciones generales de marketing.
*   **`App\Http\Controllers\MarketingApiController`** `[IMPLEMENTADO]`
    *   Provee los endpoints para integraciones externas, webhooks y el frontend para registrar interacciones, atribuciones o suscripciones.
*   **`App\Http\Controllers\LeadController`** `[IMPLEMENTADO]`
    *   Maneja el CRUD y la lógica de estado específicamente para la entidad `Lead`.

### Flujos y Casos Límite
*   **Lead Scoring:** La tabla `lead_scores` heredada ya no existe. El cálculo de score actual debe inferirse a través de las interacciones en `lead_interactions` o consultarse en las lógicas dentro de los servicios asociados a `LeadController` / `PortalMarketingController`.
*   **Recuperación de Carritos:** La tabla `cart_recovery_tokens` es fundamental. Los correos automatizados generan un registro en esta tabla. El token debe tener tiempo de expiración y debe invalidarse una vez que el carrito se convierte en orden. `[IMPLEMENTADO]`
*   **Atribución Múltiple:** Las `attributions` se vinculan tanto a `leads` como potencialmente a órdenes (ventas). Es clave manejar correctamente si el sistema usa "first-click", "last-click" o atribución lineal.

## 4. TODOs y Decisiones Pendientes
*   `[TODO: Definir regla exacta]` - Especificar el mecanismo exacto y el algoritmo para calcular el *Lead Score* en la vista o en procesos en segundo plano ahora que la tabla estática fue eliminada.
*   `[TODO: Definir regla exacta]` - Aclarar la ventana de tiempo para la expiración de los `cart_recovery_tokens`.
*   `[TODO: Definir regla exacta]` - Documentar el modelo de atribución exacto (First-click, Last-click, etc.) que gobierna la tabla `attributions`.
