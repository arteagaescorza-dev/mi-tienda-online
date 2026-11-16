<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\McpRpcRequest;
use App\Models\User;
use App\Services\Mcp\McpServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class McpController extends Controller
{
    public function handle(McpRpcRequest $request, McpServerService $mcp): JsonResponse|Response
    {
        $actor = $request->user();

        if (!$actor) {
            $expectedToken = (string) env('MCP_BEARER_TOKEN', '');
            $providedToken = (string) $request->bearerToken();

            if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $request->input('id'),
                    'error' => [
                        'code' => -32001,
                        'message' => 'No autorizado: se requiere sesión autenticada o MCP_BEARER_TOKEN válido.',
                    ],
                ], 401);
            }

            $actorId = env('MCP_ACTOR_USER_ID');
            $actor = $actorId ? User::find($actorId) : User::whereIn('role', ['superadmin', 'admin', 'portal'])->first();
        }

        $response = $mcp->handleJsonRpc($request->validated(), $actor);

        if ($response === null) {
            return response()->noContent(202);
        }

        return response()->json($response);
    }
}
