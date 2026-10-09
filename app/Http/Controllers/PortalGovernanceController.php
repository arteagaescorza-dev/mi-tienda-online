<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentAuditLog;
use App\Models\OperationalTask;
use App\Models\SystemAuditLog;
use App\Models\SystemEnvironmentConfig;
use App\Models\User;
use App\Services\Governance\AuditTrailService;
use App\Services\Governance\InterdepartmentalChatService;
use App\Services\Governance\NotificationDispatcherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\Governance\PortalGovernanceIndexRequest;
use App\Http\Requests\Portal\Governance\PortalGovernanceUpdateEnvironmentRequest;
use Illuminate\Support\Facades\Gate;

use Illuminate\View\View;

class PortalGovernanceController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditService,
        protected NotificationDispatcherService $notificationService,
        protected InterdepartmentalChatService $chatService
    ) {}

    /**
     * Vista de pantalla completa del Centro de Gobernanza, Auditoria y RBAC (Hub 8 - TASK-SYS-01).
     */
    public function index(PortalGovernanceIndexRequest $request): View
    {
        Gate::authorize('manage-governance');

        $user = $request->user();
        $activeRole = session('active_role') ?: ($user ? $user->active_role : 'admin');
        $currentTab = $request->query('tab', 'privacidad-rbac');

        // 1. Matriz de Roles y Gobernanza RBAC
        $rolesMatrix = [
            'superadmin' => [
                'name' => 'Super Administrador',
                'description' => 'Acceso sin restricciones a todos los Hubs, auditoria forense y conmutacion de entornos.',
                'allowed_hubs' => ['Dashboard', 'Tienda CMS', 'Marketing', 'Inventario', 'Finanzas', 'Logistica', 'Convenios', 'Gobernanza'],
                'financial_access' => true,
                'audit_access' => true,
                'env_switch' => true,
                'badge_class' => 'bg-purple-100 text-purple-800 border-purple-300',
            ],
            'admin' => [
                'name' => 'Administrador Operativo',
                'description' => 'Supervision general de compras, almacenes, atencion y logistica.',
                'allowed_hubs' => ['Dashboard', 'Tienda CMS', 'Marketing', 'Inventario', 'Finanzas', 'Logistica', 'Convenios', 'Gobernanza'],
                'financial_access' => true,
                'audit_access' => true,
                'env_switch' => true,
                'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            ],
            'warehouse' => [
                'name' => 'Jefe de Almacen / Bodega',
                'description' => 'Fulfillment, recepcion de ordenes de compra, conteos ciclicos y picking.',
                'allowed_hubs' => ['Dashboard', 'Inventario', 'Logistica'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300',
            ],
            'purchases' => [
                'name' => 'Compras & Aprovisionamiento',
                'description' => 'Emision de ordenes de compra, negociacion con proveedores y control ROP.',
                'allowed_hubs' => ['Dashboard', 'Inventario'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-orange-100 text-orange-800 border-orange-300',
            ],
            'logistics' => [
                'name' => 'Logistica & Envíos',
                'description' => 'Mesa de expedicion, generacion de manifiestos, RMA e inspeccion fisica.',
                'allowed_hubs' => ['Dashboard', 'Logistica'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-blue-100 text-blue-800 border-blue-300',
            ],
            'finance' => [
                'name' => 'Finanzas & Contabilidad',
                'description' => 'Cotejo de 3 vias, dispersion SPEI, conciliacion y auditoria de fletes.',
                'allowed_hubs' => ['Dashboard', 'Finanzas', 'Gobernanza'],
                'financial_access' => true,
                'audit_access' => true,
                'env_switch' => false,
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            ],
            'sales' => [
                'name' => 'Ventas & Atencion al Cliente',
                'description' => 'CRM omnicanal, rescate de carritos, soporte WhatsApp y casos.',
                'allowed_hubs' => ['Dashboard', 'Tienda CMS', 'Marketing'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-pink-100 text-pink-800 border-pink-300',
            ],
            'operator' => [
                'name' => 'Operador General',
                'description' => 'Mesa de empaque basica y consulta de folios.',
                'allowed_hubs' => ['Dashboard', 'Logistica'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-slate-100 text-slate-800 border-slate-300',
            ],
            'vendor' => [
                'name' => 'Proveedor / Fabricante B2B',
                'description' => 'Extranet exclusiva en Hub 7: consulta de OCs, registro de remisiones y descarga de ordenes.',
                'allowed_hubs' => ['Extranet Proveedor (Hub 7)'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
            ],
            'ally' => [
                'name' => 'Aliado Comercial / Creador',
                'description' => 'Extranet exclusiva en Hub 7: metricas de campanas Dir A/B, cupones y liquidaciones.',
                'allowed_hubs' => ['Extranet Aliado (Hub 7)'],
                'financial_access' => false,
                'audit_access' => false,
                'env_switch' => false,
                'badge_class' => 'bg-teal-100 text-teal-800 border-teal-300',
            ],
        ];

        // 2. Datos de Auditoria Master
        $auditLogs = $this->auditService->getAuditHistory(perPage: 20);
        $auditStats = $this->auditService->getAuditStats();

        // 3. Notificaciones y Tareas Operativas
        $notificationsFeed = $this->notificationService->getFeedForUser($user, limit: 25);
        $operationalTasks = OperationalTask::forUserRole($activeRole)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // 4. Mensajeria Interdepartamental
        $chatSummary = $this->chatService->getChannelsSummary();
        $chatChannel = $request->query('channel', 'almacen');
        $chatData = $this->chatService->getChannelMessages($chatChannel, 30, $user);

        // 5. Ajustes Globales y Entorno Sandbox / Produccion (TASK-SYS-ENV)
        $envModules = ['global', 'checkout', 'sat', 'shipping', 'spei'];
        foreach ($envModules as $m) {
            SystemEnvironmentConfig::firstOrCreate(
                ['module' => $m],
                ['active_environment' => 'sandbox', 'lock_mock_payments' => true]
            );
        }
        $envConfigs = SystemEnvironmentConfig::all();
        $envAuditLogs = EnvironmentAuditLog::orderBy('created_at', 'desc')->limit(12)->get();

        return view('portal.governance', compact(
            'currentTab',
            'activeRole',
            'rolesMatrix',
            'auditLogs',
            'auditStats',
            'notificationsFeed',
            'operationalTasks',
            'chatSummary',
            'chatChannel',
            'chatData',
            'envConfigs',
            'envAuditLogs'
        ));
    }

    /**
     * Actualiza el entorno de un modulo especifico o global (TASK-SYS-ENV).
     */
    public function updateEnvironment(PortalGovernanceUpdateEnvironmentRequest $request): RedirectResponse
    {
        Gate::authorize('manage-governance');

        $validated = 

        $config = SystemEnvironmentConfig::firstOrCreate(
            ['module' => $validated['module']],
            ['active_environment' => 'sandbox']
        );

        $prevEnv = $config->active_environment;
        $config->active_environment = $validated['target_environment'];
        if ($request->has('lock_mock_payments')) {
            $config->lock_mock_payments = $request->boolean('lock_mock_payments');
        }
        $config->save();

        EnvironmentAuditLog::create([
            'module' => $validated['module'],
            'from_environment' => $prevEnv,
            'to_environment' => $validated['target_environment'],
            'changed_by_user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        $this->auditService->logAction(
            action: 'environment_switched',
            module: 'system_governance',
            referenceCode: $validated['module'],
            oldValues: ['environment' => $prevEnv],
            newValues: ['environment' => $validated['target_environment']],
            description: "Entorno conmutado a {$validated['target_environment']} en modulo {$validated['module']}.",
            user: $request->user()
        );

        return redirect()->route('portal.governance', ['tab' => 'ajustes-entorno'])
            ->with('success', "Entorno del modulo {$validated['module']} actualizado a " . strtoupper($validated['target_environment']) . ".");
    }
}
