<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected ?string $fallbackModel;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        ?string $apiKey = null,
        ?string $model = null,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?string $fallbackModel = null
    ) {
        $this->apiKey = $apiKey ?? config('services.gemini.api_key');
        $this->model = $model ?? config('services.gemini.model', 'gemini-flash-lite-latest');
        $this->fallbackModel = $fallbackModel ?? config('services.gemini.fallback_model', 'gemini-flash-latest');
        $this->baseUrl = rtrim($baseUrl ?? config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->timeout = $timeout ?? (int) config('services.gemini.timeout', 25);
    }

    /**
     * Verifica si la API Key está presente.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Retorna el modelo en uso.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Genera texto con Gemini (con reintento inteligente y fallback).
     */
    public function generateText(string $prompt, array $options = []): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('[GeminiService] API Key no configurada en services.gemini.api_key.');
            return null;
        }

        $modelsToTry = array_unique(array_filter([$this->model, $this->fallbackModel]));
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2048;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
            ]
        ];

        if (!empty($options['response_mime_type'])) {
            $payload['generationConfig']['responseMimeType'] = $options['response_mime_type'];
        }

        if (!empty($options['system_instruction'])) {
            $payload['system_instruction'] = [
                'parts' => [
                    ['text' => $options['system_instruction']]
                ]
            ];
        }

        foreach ($modelsToTry as $targetModel) {
            $url = "{$this->baseUrl}/models/{$targetModel}:generateContent";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $this->apiKey,
                ])
                ->retry(2, 500, function ($exception, $request) {
                    return true;
                }, throw: false)
                ->timeout($this->timeout)
                ->post($url, $payload);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    if ($text !== null) {
                        return trim($text);
                    }
                }

                // Si fue 503 o 429, loguear y permitir intentar con el siguiente modelo
                $status = $response->status();
                Log::warning("[GeminiService] Modelo {$targetModel} respondió con status {$status}, probando fallback si aplica.");
            } catch (\Throwable $e) {
                Log::error("[GeminiService] Excepción al llamar a modelo {$targetModel}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Genera un objeto o array estructurado en formato JSON a partir de Gemini.
     */
    public function generateJson(string $prompt, array $options = []): ?array
    {
        $options['system_instruction'] = ($options['system_instruction'] ?? '') . "\n\nResponde ÚNICAMENTE con un objeto JSON válido y parseable, sin bloques de código markdown ni explicaciones adicionales.";
        $options['response_mime_type'] = 'application/json';
        $options['max_tokens'] = $options['max_tokens'] ?? 2048;

        $rawText = $this->generateText($prompt, $options);
        if (!$rawText) {
            return null;
        }

        // Limpiar bloques ```json ... ``` si los incluyera
        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($rawText));
        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

        $decoded = json_decode($cleanJson, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        Log::warning('[GeminiService] No se pudo decodificar JSON generado por Gemini', [
            'raw_output' => $rawText,
            'json_error' => json_last_error_msg(),
        ]);

        return null;
    }

    /**
     * Conversación multi-turno o contextual con Gemini.
     *
     * @param array $messages Array de mensajes [['sender' => 'user'|'assistant', 'message' => '...']]
     */
    public function chat(array $messages, ?string $systemInstruction = null): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $modelsToTry = array_unique(array_filter([$this->model, $this->fallbackModel]));
        $contents = [];

        foreach ($messages as $msg) {
            $role = ($msg['sender'] ?? 'user') === 'user' ? 'user' : 'model';
            $text = $msg['message'] ?? ($msg['text'] ?? '');
            if (empty(trim((string)$text))) continue;

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $text]
                ]
            ];
        }

        if (empty($contents)) {
            return null;
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1500,
            ]
        ];

        if ($systemInstruction) {
            $payload['system_instruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        foreach ($modelsToTry as $targetModel) {
            $url = "{$this->baseUrl}/models/{$targetModel}:generateContent";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $this->apiKey,
                ])
                ->retry(2, 500, throw: false)
                ->timeout($this->timeout)
                ->post($url, $payload);

                if ($response->successful()) {
                    $reply = $response->json('candidates.0.content.parts.0.text');
                    if ($reply !== null) {
                        return trim($reply);
                    }
                }

                $status = $response->status();
                Log::warning("[GeminiService] Chat en modelo {$targetModel} devolvió status {$status}.");
            } catch (\Throwable $e) {
                Log::error("[GeminiService] Excepción en chat con {$targetModel}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Prueba de conectividad activa contra Google Gemini API.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'unconfigured',
                'error' => 'API Key de Gemini no configurada en variables de entorno.',
                'model' => $this->model,
            ];
        }

        $startTime = microtime(true);
        $result = $this->generateText("Responde únicamente la palabra: CONECTADO", [
            'temperature' => 0.1,
            'max_tokens' => 20,
        ]);
        $latencyMs = round((microtime(true) - $startTime) * 1000, 1);

        if ($result !== null) {
            return [
                'success' => true,
                'status' => 'connected',
                'reply' => $result,
                'latency_ms' => $latencyMs,
                'model' => $this->model,
                'project_name' => config('services.gemini.project_name'),
                'project_number' => config('services.gemini.project_number'),
            ];
        }

        return [
            'success' => false,
            'status' => 'error',
            'error' => 'No se pudo obtener respuesta válida de Google Gemini.',
            'model' => $this->model,
        ];
    }
}
