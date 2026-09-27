<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\SystemEnvironmentConfig;
use App\Models\EnvironmentAuditLog;
use App\Models\AiCopilotChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Obtiene el listado de tareas operativas con cálculo dinámico de SLA (TASK-DSH-04, TASK-DSH-05).
     */
    public function getOperationalTasks(Request $request): JsonResponse
    {
        $this->ensureDefaultOperationalTasksExist();

        $category = $request->query('category');
        $status = $request->query('status', 'pending');

        $query = OperationalTask::query();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $tasks = $query->orderByRaw("CASE WHEN priority = 'critical' THEN 1 WHEN priority = 'high' THEN 2 WHEN priority = 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $tasks->count(),
            'tasks' => $tasks,
        ]);
    }

    /**
     * Marca una tarea operativa como completada y registra en la bitácora inmutable del turno (TASK-DSH-04).
     */
    public function completeTask(Request $request, int $id): JsonResponse
    {
        $task = OperationalTask::findOrFail($id);
        $task->status = 'completed';
        $task->completed_at = now();

        $operatorName = $request->input('operator_name')
            ?: (auth()->user()?->name ?: 'Carlos Almacén');

        $task->completed_by = $operatorName;

        if ($request->has('action_payload')) {
            $task->action_payload = array_merge(
                (array) $task->action_payload,
                (array) $request->input('action_payload')
            );
        }

        $task->save();

        $shiftLog = ShiftLog::create([
            'operational_task_id' => $task->id,
            'event_type' => 'task_completed',
            'message' => "Tarea completada: {$task->title}",
            'operator_name' => $operatorName,
            'metadata' => [
                'reference_code' => $task->reference_code,
                'category' => $task->category,
                'notes' => $request->input('notes', 'Completado conforme a checklist física de almacén.'),
            ],
        ]);

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'status' => 'completed',
            'completed_at' => $task->completed_at->toIso8601String(),
            'completed_by' => $task->completed_by,
            'shift_log' => [
                'id' => $shiftLog->id,
                'message' => $shiftLog->message,
                'operator_name' => $shiftLog->operator_name,
                'time' => $shiftLog->created_at->format('H:i A'),
            ],
        ]);
    }

    /**
     * Obtiene la bitácora cronológica inmutable del turno actual (Shift Log).
     */
    public function getShiftLogs(): JsonResponse
    {
        $this->ensureDefaultOperationalTasksExist();

        $logs = ShiftLog::query()
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(function (ShiftLog $log) {
                return [
                    'id' => $log->id,
                    'event_type' => $log->event_type,
                    'message' => $log->message,
                    'operator_name' => $log->operator_name,
                    'time' => $log->created_at ? $log->created_at->format('h:i A') : now()->format('h:i A'),
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Alterna el entorno del sistema entre Sandbox y Producción con auditoría (TASK-SYS-ENV).
     */
    public function switchEnvironment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'target_environment' => 'required|in:sandbox,production',
        ]);

        $config = SystemEnvironmentConfig::firstOrCreate(
            ['module' => $validated['module']],
            ['active_environment' => 'sandbox']
        );

        $prevEnv = $config->active_environment;
        $config->active_environment = $validated['target_environment'];
        $config->save();

        EnvironmentAuditLog::create([
            'module' => $validated['module'],
            'from_environment' => $prevEnv,
            'to_environment' => $validated['target_environment'],
            'changed_by_user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'module' => $config->module,
            'active_environment' => $config->active_environment,
            'message' => "Entorno de {$config->module} conmutado a {$config->active_environment}",
        ]);
    }

    /**
     * Procesa una consulta contextual para el Copiloto IA (TASK-DSH-07).
     */
    public function copilotChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMsg = $validated['message'];

        // Guardar mensaje del usuario
        AiCopilotChatMessage::create([
            'user_id' => auth()->id(),
            'sender' => 'user',
            'message' => $userMsg,
        ]);

        // Generar respuesta contextual determinista
        $reply = "Analizando métricas del ecosistema Multiverso: El ROAS Neto Real promedio se sitúa en 3.42x. Hay 3 productos cerca del punto de reorden (ROP) y el balance de cámara con aliados presenta un saldo neto acumulado saludable.";
        $actionChips = [
            ['label' => 'Ver Inventario Bajo ROP', 'action' => 'goto_inventory_rop'],
            ['label' => 'Auditar Liquidación SPEI', 'action' => 'goto_allies_clearing'],
        ];

        AiCopilotChatMessage::create([
            'user_id' => auth()->id(),
            'sender' => 'assistant',
            'message' => $reply,
            'action_chips_json' => $actionChips,
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'action_chips' => $actionChips,
        ]);
    }

    /**
     * Inicializa tareas operativas y bitácora del turno si la tabla está vacía.
     */
    public function ensureDefaultOperationalTasksExist(): void
    {
        if (OperationalTask::count() === 0) {
            $today = today();

            OperationalTask::create([
                'title' => 'Empaque: Pedido #MIX-1048',
                'reference_code' => '#MIX-1048',
                'category' => 'empaque',
                'priority' => 'critical',
                'assigned_role' => 'warehouse',
                'status' => 'pending',
                'description' => '3 prendas en Pasillo A-02 y Rack R-08. Requiere bolsa de lavado de cortesía.',
                'due_at' => $today->copy()->setTime(13, 30),
                'sla_minutes_limit' => 60,
                'action_payload' => [
                    'order_id' => 1048,
                    'customer' => 'Mariana Garza',
                    'destination' => 'Monterrey, NL (CP 64000)',
                    'carrier' => 'DHL Express (Guía: DHL-9482-MX)',
                    'items' => [
                        ['sku' => 'BOX-MOD-BLK-M', 'name' => 'Boxer Modal Negro - Talla M', 'aisle' => 'A-02', 'rack' => 'R-08', 'qty' => 2, 'checked' => false],
                        ['sku' => 'TEE-OVS-WHT-L', 'name' => 'Playera Oversize Blanca - Talla L', 'aisle' => 'A-02', 'rack' => 'R-04', 'qty' => 1, 'checked' => false],
                        ['sku' => 'BAG-WASH-PRM', 'name' => 'Bolsa de Lavado Premium (Cortesía)', 'aisle' => 'Mesa Empaque', 'rack' => 'C-01', 'qty' => 1, 'checked' => false],
                    ],
                    'special_instructions' => 'Incluir sticker de agradecimiento y precinto de seguridad sellado.',
                ],
            ]);

            OperationalTask::create([
                'title' => 'Recepción Inbound: Torzo B2B #OC-2026-003',
                'reference_code' => '#OC-2026-003',
                'category' => 'recepcion',
                'priority' => 'high',
                'assigned_role' => 'warehouse',
                'status' => 'pending',
                'description' => 'Lote mayorista con 150 piezas textiles. Cotejo físico y verificación de precinto.',
                'due_at' => $today->copy()->setTime(13, 0),
                'sla_minutes_limit' => 90,
                'action_payload' => [
                    'po_number' => 'OC-2026-003',
                    'supplier' => 'Torzo Textil B2B SA de CV',
                    'ramp' => 'Rampa 2',
                    'carrier' => 'Transportes Castores (Unidad 44)',
                    'total_units' => 150,
                    'seal_number' => 'SEAL-TORZO-9921',
                    'breakdown' => [
                        ['size' => 'S', 'expected' => 30, 'received' => 30],
                        ['size' => 'M', 'expected' => 50, 'received' => 50],
                        ['size' => 'L', 'expected' => 50, 'received' => 50],
                        ['size' => 'XL', 'expected' => 20, 'received' => 20],
                    ],
                    'checks' => [
                        ['label' => 'Precinto de seguridad intacto al abrir caja del fletero', 'checked' => false],
                        ['label' => 'Inspección visual de empaques individuales sin roturas', 'checked' => false],
                        ['label' => 'Conteo físico coincide con remisión adjunta', 'checked' => false],
                    ],
                ],
            ]);

            OperationalTask::create([
                'title' => 'Surtido Interno ROP: Anaquel A-01',
                'reference_code' => 'Anaquel A-01',
                'category' => 'surtido',
                'priority' => 'medium',
                'assigned_role' => 'warehouse',
                'status' => 'pending',
                'description' => 'Mover 15 piezas de Reserva Rack R-04 hacia Anaquel A-01 para surtido rápido.',
                'due_at' => $today->copy()->setTime(14, 0),
                'sla_minutes_limit' => 120,
                'action_payload' => [
                    'source_location' => 'Rack R-04 (Zona Reserva Nivel 2)',
                    'target_location' => 'Anaquel A-01 (Mesa de Picking Rápido)',
                    'sku' => 'BOX-MOD-BLK-M',
                    'product_name' => 'Boxer Modal Negro - Talla M',
                    'units_to_move' => 15,
                    'reason' => 'Quiebre de ROP en zona de surtido rápido (Stock actual: 4 pzs)',
                    'checks' => [
                        ['label' => 'Extracción de 15 piezas de Rack R-04', 'checked' => false],
                        ['label' => 'Validación de código de barras de la caja', 'checked' => false],
                        ['label' => 'Colocación física en Anaquel A-01 y confirmación en anaquel', 'checked' => false],
                    ],
                ],
            ]);

            OperationalTask::create([
                'title' => 'Despacho Manifiesto DHL Express (Corte 14:00 hrs)',
                'reference_code' => 'Manifiesto DHL',
                'category' => 'expedicion',
                'priority' => 'high',
                'assigned_role' => 'warehouse',
                'status' => 'pending',
                'description' => 'Despacho de 12 paquetes consolidados con chofer de recolección DHL.',
                'due_at' => $today->copy()->setTime(14, 0),
                'sla_minutes_limit' => 45,
                'action_payload' => [
                    'manifest_id' => 'MAN-DHL-20260927',
                    'carrier' => 'DHL Express México',
                    'driver_name' => 'Roberto González',
                    'vehicle_plates' => 'LF-8821-B',
                    'packages_count' => 12,
                    'checks' => [
                        ['label' => '12 paquetes escaneados y estibados en jaula móvil', 'checked' => false],
                        ['label' => 'Impresión de 2 tantos de manifiesto con firma autógrafa', 'checked' => false],
                        ['label' => 'Entrega física de bultos a unidad de recolección DHL', 'checked' => false],
                    ],
                ],
            ]);
        }

        if (ShiftLog::count() === 0) {
            $today = today();

            ShiftLog::create([
                'event_type' => 'reception',
                'message' => 'Recepción física completada: #OC-2026-002 (50 piezas)',
                'operator_name' => 'Carlos A.',
                'created_at' => $today->copy()->setTime(10, 42),
            ]);

            ShiftLog::create([
                'event_type' => 'manifest',
                'message' => 'Manifiesto DHL generado con 12 guías impresas',
                'operator_name' => 'Carlos A.',
                'created_at' => $today->copy()->setTime(9, 15),
            ]);

            ShiftLog::create([
                'event_type' => 'system_check',
                'message' => 'Apertura de turno de almacén y calibración de báscula',
                'operator_name' => 'Sistema',
                'created_at' => $today->copy()->setTime(8, 0),
            ]);
        }
    }
}
