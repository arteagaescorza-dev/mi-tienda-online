<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InternalCronRunRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class InternalCronController extends Controller
{
    public function run(InternalCronRunRequest $request): JsonResponse
    {
        $expectedSecret = (string) env('CRON_SECRET', '');
        $providedSecret = (string) $request->header('X-Cron-Secret', '');

        if ($expectedSecret === '' || !hash_equals($expectedSecret, $providedSecret)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado: X-Cron-Secret inválido o no configurado.',
            ], 401);
        }

        $command = $request->validated('command');
        $exitCode = Artisan::call($command);

        return response()->json([
            'status' => $exitCode === 0 ? 'success' : 'error',
            'command' => $command,
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
            'executed_at' => now()->toIso8601String(),
        ], $exitCode === 0 ? 200 : 500);
    }
}
