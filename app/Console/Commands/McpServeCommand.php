<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Mcp\McpServerService;
use Illuminate\Console\Command;

class McpServeCommand extends Command
{
    protected $signature = 'mcp:serve {--user-id= : ID del usuario actor para evaluación RBAC}';

    protected $description = 'Inicia el servidor MCP (Model Context Protocol) sobre transporte Stdio JSON-RPC 2.0.';

    public function handle(McpServerService $mcp): int
    {
        $userId = $this->option('user-id') ?: config('services.mcp.actor_user_id');
        $actor = $userId ? User::find($userId) : User::whereIn('role', ['superadmin', 'admin', 'portal'])->first();

        while (($line = fgets(STDIN)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $payload = json_decode($trimmed, true);
            if (!is_array($payload)) {
                $error = [
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'error' => ['code' => -32700, 'message' => 'Parse error: JSON inválido'],
                ];
                fwrite(STDOUT, json_encode($error) . PHP_EOL);
                fflush(STDOUT);
                continue;
            }

            $response = $mcp->handleJsonRpc($payload, $actor);
            if ($response !== null) {
                fwrite(STDOUT, json_encode($response, JSON_UNESCAPED_UNICODE) . PHP_EOL);
                fflush(STDOUT);
            }
        }

        return self::SUCCESS;
    }
}
