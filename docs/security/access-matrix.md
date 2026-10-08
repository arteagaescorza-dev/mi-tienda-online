# Matriz de Control de Acceso, Roles & Permisos (RBAC SSOT)
## Plataforma RevOps E-Commerce Multiverso & ERP (Mi Tienda Online)

**Versión del Documento:** 3.0 (SSOT Canon)  
**Fecha de Publicación:** Octubre 2026  
**Ámbito:** Gobernanza de Seguridad, Roles Operativos, Permisos por Módulo y Aislamiento Financiero  
**Referencia Histórica:** Archivo consolidado en `docs/archivo/legacy-specs/ROLES_AND_WORKFLOWS_SPEC.md`

---

## 1. Arquitectura de Seguridad & Principio de Mínimo Privilegio

La plataforma opera bajo un modelo de **Control de Acceso Basado en Roles (RBAC - Role-Based Access Control)** con segregación estricta de funciones.

### Principio Rector de Privacidad Financiera:
> **"Los operadores de almacén, empaque, contenido y atención comercial operan con visibilidad total de unidades físicas, inventarios y clientes, pero con CERO VISIBILIDAD de márgenes confidenciales, costos de adquisición de maquila o balances contables globales."**

```mermaid
graph TD
    USER[Usuario Autenticado] --> AUTH[Middleware: portal.auth]
    AUTH --> RBAC{Validación RBAC}
    RBAC -->|Gate: access-hub| HUBS[Navegación Permitida por Hub]
    RBAC -->|Gate: view-financial-data| FIN[Datos Financieros & P&L]
    FIN -->|@canViewFinancials| SENSITIVE[Márgenes, CPP, Costos de Compra]
```

---

## 2. Catálogo Canónico de Roles Operativos

| Rol / Perfil | Identificador de Sistema | Enfoque Operativo | Nivel de Acceso Financiero | Módulo Principal de Trabajo |
| :--- | :--- | :--- | :--- | :--- |
| **Director General / RevOps** | `admin` | Orquestación ejecutiva, fiscal SAT, telemetría y P&L global. | **Total / Irrestricto** | Hub 1 (Dashboard) & Todos los Hubs |
| **Finanzas & Contabilidad** | `finance` | Cascada de margen neto, conciliación pasarelas, egresos e impuestos. | **Total / Financiero** | Hub 5 (Finanzas) & Hub 1 |
| **Jefe de Almacén** | `warehouse` / `operator` | Custodia física de stock, Kardex, slotting, rampa y auditorías cíclicas. | **Restringido (Cero Cifras)** | Hub 4 (Inventario) & Hub 1 (Operativo) |
| **Operador de Empaque & Picking** | `picker` / `packer` | Surtido por pasillo, ensamblado BOM, balanzas, guías y manifiestos. | **Restringido (Solo Unidades)** | Hub 6 (Logística) & Hub 1 (Piso) |
| **Encargada de Compras** | `purchases` / `buyer` | Reorden ROP, emisión de OCs a talleres, cotejo de rampa y acuerdos MOQ. | **Parcial (Costos de Compra/CPP)** | Hub 4 (Compras) & Hub 7 (B2B) |
| **Asesora Comercial (Ana)** | `sales` / `support` | Atención al cliente, recuperación de carritos WhatsApp, asesoría de tallas. | **Comercial (PVP y Cupones)** | Hub 1 (Ventas) & Hub 3 (CRM) |
| **Editor de Contenidos / CMS** | `cms_editor` / `store_manager` | Constructor Drag & Drop, banners, revista digital y vitrinas multiverso. | **Cero Acceso Financiero** | Hub 2 (CMS & Storefront) |
| **Superoperador Multirrol** | `all_roles` / `super_operator` | Supervisión total y conmutación entre perfiles operativos en caliente. | **Total / Irrestricto** | Acceso a los 7 Hubs |

---

## 3. Matriz Bidimensional de Permisos por Módulo

### Leyenda de Niveles de Acceso:
* **FULL:** Creación, lectura, actualización, eliminación y exportación de datos.
* **EDIT:** Lectura y edición operativa (sin facultades de borrado definitivo o cambio de parámetros globales).
* **VIEW:** Solo lectura y visualización de registros.
* **RESTRICTED:** Acceso parcial a cantidades físicas con ofuscación obligatoria de montos monetarios.
* **NONE:** Acceso bloqueado. El módulo o pestaña permanece oculto en el sidebar y denegado en backend.

| Área Funcional / Submódulo | `admin` | `finance` | `warehouse` | `picker` | `purchases` | `sales` | `cms_editor` |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Hub 1: KPIs Ejecutivos & Ventas Totales** | FULL | FULL | NONE | NONE | NONE | VIEW | NONE |
| **Hub 1: Semáforo Aging & SLAs de Pedidos** | FULL | VIEW | FULL | FULL | VIEW | FULL | NONE |
| **Hub 1: Health Monitor & Telemetría APIs** | FULL | VIEW | NONE | NONE | NONE | NONE | NONE |
| **Hub 2: Editor Visual de Secciones (Drag & Drop)** | FULL | NONE | NONE | NONE | NONE | NONE | FULL |
| **Hub 2: Paletas de Color & Tokens `:root`** | FULL | NONE | NONE | NONE | NONE | NONE | FULL |
| **Hub 2: Marquesinas, Páginas & Editor de Footer** | FULL | NONE | NONE | NONE | NONE | NONE | FULL |
| **Hub 3: Leads, Scoring & Campañas** | FULL | VIEW | NONE | NONE | NONE | FULL | VIEW |
| **Hub 3: Recuperación Carritos por WhatsApp** | FULL | NONE | NONE | NONE | NONE | FULL | NONE |
| **Hub 4: Catálogo Maestro & Altas de Prenda** | FULL | VIEW | EDIT | VIEW | EDIT | VIEW | VIEW |
| **Hub 4: Kardex Transaccional & Ajustes de Stock** | FULL | VIEW | FULL | NONE | VIEW | NONE | NONE |
| **Hub 4: Precios de Venta (PVP)** | FULL | VIEW | VIEW | NONE | VIEW | FULL | VIEW |
| **Hub 4: Costo Promedio Ponderado (CPP) / Maquila** | FULL | FULL | NONE | NONE | FULL | NONE | NONE |
| **Hub 4: Recetas BOM para Combos & Suscripciones** | FULL | VIEW | EDIT | VIEW | EDIT | NONE | NONE |
| **Hub 4: Tabuladores MOQ & Acuerdos de Proveedor** | FULL | FULL | NONE | NONE | FULL | NONE | NONE |
| **Hub 5: Cascada de Margen Neto Real** | FULL | FULL | NONE | NONE | NONE | NONE | NONE |
| **Hub 5: Conciliación Pasarelas & Facturación SAT** | FULL | FULL | NONE | NONE | NONE | NONE | NONE |
| **Hub 5: Simulador de Absorción de Flete Courier** | FULL | FULL | NONE | NONE | NONE | NONE | NONE |
| **Hub 6: Mesa de Empaque & Balanzas** | FULL | NONE | FULL | FULL | NONE | NONE | NONE |
| **Hub 6: Emisión de Guías Térmicas & Manifiestos** | FULL | NONE | FULL | FULL | NONE | NONE | NONE |
| **Hub 6: Matriz de Retorno Sanitario RMA (PROFECO)** | FULL | VIEW | FULL | EDIT | NONE | VIEW | NONE |
| **Hub 7: Extranet Proveedores & Emisión de OCs** | FULL | FULL | VIEW | NONE | FULL | NONE | NONE |
| **Hub 7: Vouchers Digitales Aliados & Comisiones** | FULL | FULL | NONE | NONE | NONE | VIEW | NONE |

---

## 4. Implementación Técnica en Laravel

### A. Gates y Políticas de Autorización
Definidos centralmente en `app/Providers/AppServiceProvider.php`:

```php
// Gate de Visibilidad Financiera Estricta (TASK-RBAC-03)
Gate::define('view-financial-data', function (User $user) {
    return $user->canAccessFinancialData();
});

// Gate de Acceso a Hub por Rol
Gate::define('access-hub', function (User $user, string $hub) {
    return $user->canAccessHub($hub);
});
```

### B. Directiva Blade Condicional
Para renderizado de componentes con cifras confidenciales en plantillas:

```blade
@canViewFinancials
    <div class="financial-card">
        <span>Costo de Adquisición (CPP):</span>
        <strong>MXN ${{ number_format($item->cost_cpp, 2) }}</strong>
    </div>
@else
    <span class="text-slate-400 text-xs italic">Cifra Confidencial</span>
@endcanViewFinancials
```

### C. Conmutación de Rol Operativo en Caliente (Simulador de Perfil)
Los operadores con perfil `all_roles` disponen de un selector en el footer del Sidebar (`#roleSimulatorSelector`) para alternar en tiempo real la perspectiva visual de la interfaz (`warehouse`, `picker`, `purchases`, `sales`, `cms_editor`), verificando la ergonomía de cada puesto sin requerir logout.

---

## 5. [TODO: Definir regla exacta]

1. `[TODO: Definir regla exacta]` **Política de caducidad automática de sesiones inactivas para el rol `finance` y `admin`.**  
   *Nota actual:* Laravel opera con el valor estándar `SESSION_LIFETIME=120` (2 horas). Debe definirse si para roles directivos/financieros se acorta a 15 minutos de inactividad por estándar bancario.
2. `[TODO: Definir regla exacta]` **Auditoría de exportaciones masivas de base de datos de clientes.**  
   *Nota actual:* El rol `sales` puede exportar listados de leads a CSV; se debe definir si requiere aprobación previa por token de dos factores (`2FA`) enviada al Administrador.
