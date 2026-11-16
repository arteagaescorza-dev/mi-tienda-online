<?php

namespace App\Services\Mcp;

use App\Models\AllyCampaignDirA;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\Product;
use App\Models\SystemAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class McpServerService
{
    public const PROTOCOL_VERSION = '2025-03-26';
    public const SERVER_NAME = 'mi-tienda-online-revops-mcp';
    public const SERVER_VERSION = '1.0.0';

    /**
     * Procesa una petición JSON-RPC 2.0 MCP y retorna el payload de respuesta (o null si es notificación sin id).
     */
    public function handleJsonRpc(array $payload, ?User $actor = null): ?array
    {
        $id = $payload['id'] ?? null;
        $method = (string) ($payload['method'] ?? '');
        $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

        // Notificaciones JSON-RPC (ej. notifications/initialized) no llevan id y no requieren respuesta
        if ($id === null && str_starts_with($method, 'notifications/')) {
            return null;
        }

        try {
            $result = match ($method) {
                'initialize' => [
                    'protocolVersion' => self::PROTOCOL_VERSION,
                    'capabilities' => [
                        'tools' => ['listChanged' => false],
                        'resources' => ['subscribe' => false, 'listChanged' => false],
                    ],
                    'serverInfo' => [
                        'name' => self::SERVER_NAME,
                        'version' => self::SERVER_VERSION,
                    ],
                ],
                'ping' => new \stdClass(),
                'tools/list' => ['tools' => $this->listTools($actor)],
                'tools/call' => $this->callTool(
                    (string) ($params['name'] ?? ''),
                    is_array($params['arguments'] ?? null) ? $params['arguments'] : [],
                    $actor
                ),
                'resources/list' => ['resources' => $this->listResources()],
                'resources/read' => $this->readResource((string) ($params['uri'] ?? ''), $actor),
                default => throw new \InvalidArgumentException("Método MCP no soportado: {$method}", -32601),
            };

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $code = is_int($e->getCode()) && $e->getCode() < 0 ? $e->getCode() : -32603;

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Retorna el catálogo de Tools MCP visibles para el actor según sus Gates RBAC.
     */
    public function listTools(?User $actor = null): array
    {
        $tools = [
            [
                'name' => 'storefront_search_products',
                'description' => 'Busca productos activos del catálogo respetando la regla de Stock Cero para productos scrapeados/dropshipping (Línea B).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Texto de búsqueda por título, SKU o marca'],
                        'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados (1-25)', 'default' => 10],
                    ],
                ],
            ],
            [
                'name' => 'inventory_get_rop_alerts',
                'description' => 'Obtiene variantes físicas bajo Punto de Reorden (ROP). Excluye estrictamente productos scrapeados y dropshipping.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => ['type' => 'integer', 'description' => 'Máximo de alertas (1-50)', 'default' => 20],
                    ],
                ],
            ],
            [
                'name' => 'allies_list_pending_campaigns',
                'description' => 'Lista las campañas o eventos de aliados en Hub 7 pendientes de aprobación (pending_approval).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados (1-30)', 'default' => 15],
                    ],
                ],
            ],
            [
                'name' => 'system_get_health_summary',
                'description' => 'Retorna el estado operativo del ERP: motor de BD (pgsql/sqlite), disco de medios (supabase/public), tareas pendientes y auditoría.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ],
        ];

        if ($this->canViewFinancials($actor)) {
            $tools[] = [
                'name' => 'finance_get_margin_summary',
                'description' => 'Consulta resumen financiero de ventas, utilidad neta y margen EBITDA reciente (requiere Gate view-financials).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'days' => ['type' => 'integer', 'description' => 'Ventana de días hacia atrás (1-90)', 'default' => 30],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Ejecuta una herramienta MCP y retorna la estructura oficial `content` + `isError`.
     */
    public function callTool(string $name, array $arguments = [], ?User $actor = null): array
    {
        try {
            $data = match ($name) {
                'storefront_search_products' => $this->toolSearchProducts($arguments),
                'inventory_get_rop_alerts' => $this->toolGetRopAlerts($arguments, $actor),
                'finance_get_margin_summary' => $this->toolGetFinanceSummary($arguments, $actor),
                'allies_list_pending_campaigns' => $this->toolListPendingAllies($arguments, $actor),
                'system_get_health_summary' => $this->toolSystemHealth($actor),
                default => throw new \InvalidArgumentException("Tool MCP desconocida: {$name}"),
            };

            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    ],
                ],
                'structuredContent' => $data,
                'isError' => false,
            ];
        } catch (\Throwable $e) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $e->getMessage(),
                    ],
                ],
                'isError' => true,
            ];
        }
    }

    /**
     * Convierte las Tools MCP autorizadas a `functionDeclarations` para Google Gemini.
     */
    public function getGeminiFunctionDeclarations(?User $actor = null): array
    {
        return array_map(function (array $tool) {
            $schema = $tool['inputSchema'];
            $properties = $schema['properties'] ?? [];

            $declaration = [
                'name' => $tool['name'],
                'description' => $tool['description'],
            ];

            if (!empty($properties) && !($properties instanceof \stdClass)) {
                $declaration['parameters'] = $schema;
            }

            return $declaration;
        }, $this->listTools($actor));
    }

    public function listResources(): array
    {
        return [
            [
                'uri' => 'mcp://mi-tienda/catalog/summary',
                'name' => 'Resumen del Catálogo Omnicanal',
                'description' => 'Totales de productos físicos vs. bajo pedido y categorías activas.',
                'mimeType' => 'application/json',
            ],
            [
                'uri' => 'mcp://mi-tienda/governance/health',
                'name' => 'Salud Operativa y Entorno Cloud',
                'description' => 'Estado de base de datos, almacenamiento S3/Supabase y tareas operativas.',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function readResource(string $uri, ?User $actor = null): array
    {
        $payload = match ($uri) {
            'mcp://mi-tienda/catalog/summary' => [
                'active_products' => Product::where('visible', true)->where('is_archived', false)->count(),
                'physical_products' => Product::where('visible', true)->where('is_archived', false)->where('type', 'physical')->count(),
                'dropship_products' => Product::where('visible', true)->where('is_archived', false)->whereIn('type', ['dropship', 'scraped'])->count(),
                'categories_count' => Category::count(),
            ],
            'mcp://mi-tienda/governance/health' => $this->toolSystemHealth($actor),
            default => throw new \InvalidArgumentException("Recurso MCP no encontrado: {$uri}"),
        };

        return [
            'contents' => [
                [
                    'uri' => $uri,
                    'mimeType' => 'application/json',
                    'text' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ],
            ],
        ];
    }

    private function toolSearchProducts(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        $limit = max(1, min(25, (int) ($args['limit'] ?? 10)));

        $products = Product::with(['variants.inventory', 'supplierProducts.supplier', 'fabric'])
            ->where('is_archived', false)
            ->where('visible', true)
            ->when($query !== '', function ($q) use ($query) {
                $needle = '%' . mb_strtolower($query) . '%';
                $q->where(function ($sub) use ($needle) {
                    $sub->whereRaw('LOWER(title) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(sku) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(brand) LIKE ?', [$needle]);
                });
            })
            ->limit($limit)
            ->get();

        return [
            'count' => $products->count(),
            'items' => $products->map(function (Product $product) {
                $isScraped = $product->isScraped();
                $localStock = $isScraped
                    ? 0
                    : (int) $product->variants->sum(fn ($v) => max(0, (int) ($v->inventory?->quantity ?? 0) - (int) ($v->inventory?->reserved ?? 0)));

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'brand' => $product->brand,
                    'price' => (float) $product->price,
                    'fabric' => $product->fabric?->name ?? null,
                    'fulfillment_mode' => $isScraped ? 'on_demand' : 'physical_stock',
                    'local_stock' => $localStock,
                    'badge' => $isScraped ? 'Bajo Pedido' : ($localStock > 0 ? 'Disponible' : 'Agotado'),
                ];
            })->values()->all(),
        ];
    }

    private function toolGetRopAlerts(array $args, ?User $actor): array
    {
        $this->authorizePortal($actor, 'view-dashboard');
        $limit = max(1, min(50, (int) ($args['limit'] ?? 20)));

        $inventories = Inventory::with(['variant.product.supplierProducts.supplier'])
            ->where('type', 'physical')
            ->where('min_threshold', '>', 0)
            ->whereRaw('(quantity - reserved) <= min_threshold')
            ->limit($limit * 2)
            ->get();

        // Política Oficial: Excluir estrictamente productos scrapeados / dropshipping de ROP
        $alerts = $inventories->filter(function (Inventory $inv) {
            $product = $inv->variant?->product;
            return $product && !$product->is_archived && !$product->isScraped();
        })->take($limit)->map(function (Inventory $inv) {
            $available = max(0, (int) $inv->quantity - (int) $inv->reserved);
            return [
                'inventory_id' => $inv->id,
                'sku' => $inv->variant?->sku,
                'product_title' => $inv->variant?->product?->title,
                'location' => $inv->location,
                'available_stock' => $available,
                'rop_units' => (int) $inv->min_threshold,
            ];
        })->values()->all();

        return [
            'count' => count($alerts),
            'alerts' => $alerts,
        ];
    }

    private function toolGetFinanceSummary(array $args, ?User $actor): array
    {
        if (!$this->canViewFinancials($actor)) {
            throw new \RuntimeException('Acceso denegado: el actor no cuenta con permiso manage-finance.');
        }

        $days = max(1, min(90, (int) ($args['days'] ?? 30)));
        $since = now()->subDays($days);

        $ordersCount = Order::where('created_at', '>=', $since)->count();
        $grossSales = (float) Order::where('created_at', '>=', $since)->sum('total');
        $netMargin = (float) OrderFinancialMargin::where('created_at', '>=', $since)->sum('net_margin');

        return [
            'window_days' => $days,
            'orders_count' => $ordersCount,
            'gross_sales' => round($grossSales, 2),
            'net_margin' => round($netMargin, 2),
        ];
    }

    private function toolListPendingAllies(array $args, ?User $actor): array
    {
        $this->authorizePortal($actor, 'view-dashboard');
        $limit = max(1, min(30, (int) ($args['limit'] ?? 15)));

        $campaigns = AllyCampaignDirA::with('ally')
            ->where('status', 'pending_approval')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return [
            'count' => $campaigns->count(),
            'campaigns' => $campaigns->map(fn (AllyCampaignDirA $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'ally_name' => $c->ally?->name,
                'universe' => $c->universe,
                'benefit' => $c->benefit,
                'status' => $c->status,
            ])->values()->all(),
        ];
    }

    private function toolSystemHealth(?User $actor): array
    {
        $this->authorizePortal($actor, 'view-dashboard');

        return [
            'database_driver' => DB::getDriverName(),
            'filesystem_default' => config('filesystems.default'),
            'pending_operational_tasks' => OperationalTask::where('status', 'pending')->count(),
            'audit_logs_total' => SystemAuditLog::count(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function authorizePortal(?User $actor, string $ability): void
    {
        if ($actor === null) {
            return;
        }

        if (!Gate::forUser($actor)->allows($ability)) {
            throw new \RuntimeException("Acceso denegado: se requiere el permiso '{$ability}'.");
        }
    }

    private function canViewFinancials(?User $actor): bool
    {
        if ($actor === null) {
            return false;
        }

        return Gate::forUser($actor)->allows('manage-finance');
    }
}
