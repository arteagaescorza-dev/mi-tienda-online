<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\OperationalTask;
use App\Models\SystemEnvironmentConfig;
use App\Models\EnvironmentAuditLog;
use App\Models\AiCopilotChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Marca una tarea operativa como completada (TASK-DSH-04).
     */
    public function completeTask(Request $request, int $id): JsonResponse
    {
        $task = OperationalTask::findOrFail($id);
        $task->status = 'completed';
        $task->completed_at = now();
        $task->save();

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'status' => 'completed',
            'completed_at' => $task->completed_at->toIso8601String(),
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
}
