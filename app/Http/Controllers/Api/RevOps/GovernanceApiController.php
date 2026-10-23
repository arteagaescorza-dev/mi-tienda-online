<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Services\Governance\AuditTrailService;
use App\Services\Governance\InterdepartmentalChatService;
use App\Services\Governance\NotificationDispatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RevOps\GovernanceApiGetAuditLogsRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiRecordCustomActionRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiGetNotificationsRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiMarkNotificationReadRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiMarkAllNotificationsReadRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiDispatchNotificationRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiGetChatMessagesRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiSendChatMessageRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiResolveChatMessageRequest;
use App\Http\Requests\Api\RevOps\GovernanceApiGetChatSummaryRequest;
use Illuminate\Support\Facades\Gate;


class GovernanceApiController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditService,
        protected NotificationDispatcherService $notificationService,
        protected InterdepartmentalChatService $chatService
    ) {}

    /**
     * Consulta paginada y filtrada del Master Log de Auditoria Omnicanal (TASK-DSH-09).
     */
    public function getAuditLogs(GovernanceApiGetAuditLogsRequest $request): JsonResponse
    {
        Gate::authorize('manage-governance');

        $user = $request->user();
        $role = $user ? (session('active_role') ?: $user->role) : null;

        // Solo administradores y superadministradores pueden consultar la bitacora forense
        if (!$role || !in_array($role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return response()->json([
                'error' => 'Acceso denegado por politica de gobernanza y auditoria RBAC (TASK-DSH-09).',
                'message' => 'Solo los perfiles con rango de superadministrador pueden auditar la bitacora omnicanal.',
            ], 403);
        }

        $module = $request->query('module');
        $action = $request->query('action');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 25);

        $paginator = $this->auditService->getAuditHistory(
            module: $module,
            action: $action,
            term: $search,
            perPage: $perPage
        );

        $stats = $this->auditService->getAuditStats();

        // Enriquecer registros con diff_summary
        $items = collect($paginator->items())->map(function ($log) {
            $array = $log->toArray();
            $array['diff_summary'] = $log->diff_summary;
            return $array;
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Registra una accion manual de auditoria solicitada desde la consola directiva.
     */
    public function recordCustomAction(GovernanceApiRecordCustomActionRequest $request): JsonResponse
    {
        Gate::authorize('manage-governance');

        $user = $request->user();
        if (!$user || !in_array($user->active_role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return response()->json([
                'error' => 'No autorizado para emitir asientos manuales de auditoria.',
            ], 403);
        }

        $validated = $request->validated();

        $log = $this->auditService->logAction(
            action: $validated['action'],
            module: $validated['module'],
            referenceCode: $validated['reference_code'] ?? null,
            oldValues: $validated['old_values'] ?? null,
            newValues: $validated['new_values'] ?? null,
            description: $validated['description'],
            user: $user
        );

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
            'message' => 'Evento de auditoria registrado de forma inmutable.',
        ], 201);
    }

    /**
     * Obtiene el feed de notificaciones operativas filtrado por rol y permisos del usuario (TASK-UX-03).
     */
    public function getNotifications(GovernanceApiGetNotificationsRequest $request): JsonResponse
    {
        $user = $request->user();

        // En caso de invocacion no autenticada en entorno de pruebas, asociar al primer usuario portal
        if (!$user) {
            $user = \App\Models\User::whereIn('role', ['portal', 'admin', 'superadmin'])->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Sesion requerida para consultar notificaciones operativas.',
            ], 401);
        }

        $category = $request->query('category');
        $unreadOnly = $request->boolean('unread_only', false);
        $limit = (int) $request->query('limit', 30);

        $feed = $this->notificationService->getFeedForUser(
            user: $user,
            category: $category,
            unreadOnly: $unreadOnly,
            limit: $limit
        );

        return response()->json([
            'success' => true,
            'unread_count' => $feed['unread_count'],
            'total' => $feed['total'],
            'categories' => $feed['categories'],
            'data' => $feed['items'],
        ]);
    }

    /**
     * Marca una notificacion especifica como leida (TASK-UX-03).
     */
    public function markNotificationRead(GovernanceApiMarkNotificationReadRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            $user = \App\Models\User::whereIn('role', ['portal', 'admin', 'superadmin'])->first();
        }

        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $success = $this->notificationService->markAsRead($id, $user);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Notificacion no encontrada o no accesible para el usuario.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount($user),
            'message' => 'Notificacion marcada como leida.',
        ]);
    }

    /**
     * Marca todas las notificaciones pendientes del usuario como leidas.
     */
    public function markAllNotificationsRead(GovernanceApiMarkAllNotificationsReadRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            $user = \App\Models\User::whereIn('role', ['portal', 'admin', 'superadmin'])->first();
        }

        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $affected = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'marked_count' => $affected,
            'unread_count' => 0,
            'message' => 'Todas las notificaciones fueron marcadas como leidas.',
        ]);
    }

    /**
     * Despacha una notificacion operativa manual o automatizada.
     */
    public function dispatchNotification(GovernanceApiDispatchNotificationRequest $request): JsonResponse
    {
        Gate::authorize('manage-governance');

        $user = $request->user();
        $role = $user ? (session('active_role') ?: $user->role) : null;

        // Validar que el rol tenga permisos para emitir alertas
        if (!$role || !in_array($role, ['admin', 'superadmin', 'all_roles', 'portal'], true)) {
            return response()->json([
                'error' => 'No autorizado para emitir notificaciones directivas.',
            ], 403);
        }

        $validated = $request->validated();

        $notification = $this->notificationService->dispatch($validated);

        return response()->json([
            'success' => true,
            'notification' => $notification,
            'message' => 'Notificacion operativa despachada exitosamente.',
        ], 201);
    }

    /**
     * Consulta el timeline de mensajes del chat interdepartamental (TASK-COM-01).
     */
    public function getChatMessages(GovernanceApiGetChatMessagesRequest $request): JsonResponse
    {
        $channel = $request->query('channel', 'almacen');
        $limit = (int) $request->query('limit', 50);
        $user = $request->user();

        $data = $this->chatService->getChannelMessages($channel, $limit, $user);
        $summary = $this->chatService->getChannelsSummary();

        return response()->json([
            'success' => true,
            'channel' => $data['channel'],
            'channel_title' => $data['channel_title'],
            'unresolved_count' => $data['unresolved_count'],
            'summary' => $summary,
            'messages' => $data['messages'],
        ]);
    }

    /**
     * Envia una nueva nota formal en el canal del chat inter-areas.
     */
    public function sendChatMessage(GovernanceApiSendChatMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $message = $this->chatService->sendMessage(
            channel: $validated['channel'],
            message: $validated['message'],
            refType: $validated['reference_type'] ?? null,
            refCode: $validated['reference_code'] ?? null,
            priority: $validated['priority'] ?? 'normal',
            user: $user,
            senderName: $validated['sender_name'] ?? null,
            senderRole: $validated['sender_role'] ?? null,
            targetRole: $validated['target_role'] ?? null
        );

        $summary = $this->chatService->getChannelsSummary();

        return response()->json([
            'success' => true,
            'message' => $message,
            'summary' => $summary,
            'notice' => 'Mensaje interdepartamental transmitido exitosamente.',
        ], 201);
    }

    /**
     * Marca un mensaje operativo como resuelto.
     */
    public function resolveChatMessage(GovernanceApiResolveChatMessageRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $success = $this->chatService->resolveMessage($id, $user);

        if (!$success) {
            return response()->json([
                'success' => false,
                'error' => 'Mensaje no encontrado.',
            ], 404);
        }

        $summary = $this->chatService->getChannelsSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'message' => 'Mensaje operativo marcado como resuelto.',
        ]);
    }

    /**
     * Resumen de mensajes pendientes y contadores de canales para la barra superior.
     */
    public function getChatSummary(GovernanceApiGetChatSummaryRequest $request): JsonResponse
    {
        $summary = $this->chatService->getChannelsSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }
}
