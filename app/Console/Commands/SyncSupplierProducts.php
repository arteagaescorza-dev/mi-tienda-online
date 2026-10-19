<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\SupplierSyncRun;
use App\Services\SupplierScrapers\AdrenalineForteScraper;
use App\Services\SupplierScrapers\TorzoScraper;
use App\Services\SupplierCatalogSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SyncSupplierProducts extends Command
{
    protected $signature = 'supplier:scrape {supplier_id?} {--force : Forzar ejecución ignorando el intervalo de 3 días}';
    protected $description = 'Sincroniza productos desde proveedores dropship por scraping.';

    public function handle(SupplierCatalogSyncService $syncService): int
    {
        $supplierId = $this->argument('supplier_id');
        $force = (bool) $this->option('force');
        $suppliers = $supplierId
            ? Supplier::where('id', $supplierId)->get()
            : Supplier::where('type', 'dropship_scrape')->get();

        if ($suppliers->isEmpty()) {
            $this->info('No se encontraron proveedores dropship_scrape.');
            return self::SUCCESS;
        }

        foreach ($suppliers as $supplier) {
            $this->info("Procesando proveedor: {$supplier->name} ({$supplier->id})");

            // POLÍTICA DE SCRAPING: Solo se escrapea 1 vez cada 3 días salvo forzado explícito
            if (! $force) {
                $lastSyncRun = SupplierSyncRun::where('supplier_id', $supplier->id)
                    ->where('status', 'success')
                    ->latest('finished_at')
                    ->first();
                $lastChecked = SupplierProduct::where('supplier_id', $supplier->id)
                    ->max('last_checked_at');

                $threeDaysAgo = now()->subDays(3);
                $lastRunDate = $lastSyncRun?->finished_at ?? ($lastChecked ? \Carbon\Carbon::parse($lastChecked) : null);

                if ($lastRunDate && $lastRunDate->gt($threeDaysAgo)) {
                    $this->info("  Proveedor {$supplier->name} omitido: Política oficial de scraping permite 1 consulta cada 3 días. Última ejecución: {$lastRunDate->toDateTimeString()}. Usa --force para ejecutar inmediatamente.");
                    continue;
                }
            }

            $run = SupplierSyncRun::create([
                'supplier_id' => $supplier->id,
                'status' => 'running',
                'started_at' => now(),
            ]);

            try {
                if ($supplier->website === 'https://torzo.kexe.mx/') {
                    $count = $this->runTorzoScraper($supplier, $syncService);
                } elseif ($this->isAdrenalineForteSupplier($supplier)) {
                    $count = $this->runAdrenalineForteScraper($supplier, $syncService);
                } else {
                    $count = $this->runExistingProducts($supplier);
                }

                $run->update(['status' => 'success', 'synced_count' => $count, 'finished_at' => now()]);
            } catch (\Throwable $e) {
                $run->update(['status' => 'failed', 'finished_at' => now(), 'error' => $e->getMessage()]);
                $this->error("  Error: {$e->getMessage()}");
            }
        }

        $this->info('Sincronización de proveedores finalizada.');
        return self::SUCCESS;
    }

    protected function devThrottle(string $context = 'consulta'): void
    {
        if (app()->environment('local', 'development') && ! app()->runningUnitTests()) {
            $delay = random_int(3, 5);
            $this->line("    [Rate Limit Dev] Esperando {$delay}s antes de la {$context} para proteger reputación de IP...");
            sleep($delay);
        }
    }

    protected function runExistingProducts(Supplier $supplier): int
    {
        $count = 0;
        foreach ($supplier->supplierProducts as $supplierProduct) {
            if (empty($supplierProduct->supplier_url)) {
                $this->warn("  SKU {$supplierProduct->supplier_sku} no tiene URL de producto.");
                continue;
            }

            $this->devThrottle("consulta de SKU {$supplierProduct->supplier_sku}");
            $this->info("  Scraping SKU {$supplierProduct->supplier_sku}");

            $response = Http::timeout(20)->get($supplierProduct->supplier_url);
            if (! $response->successful()) {
                throw new \RuntimeException('URL inaccesible HTTP ' . $response->status() . ': ' . $supplierProduct->supplier_url);
            }

            $parsed = $this->parseProductHtml($response->body());
            $this->updateSupplierProduct($supplierProduct, $parsed);
            $count++;
        }

        return $count;
    }

    protected function runTorzoScraper(Supplier $supplier, SupplierCatalogSyncService $syncService): int
    {
        $this->info('  Usando scraper específico Torzo');
        $scraper = new TorzoScraper();
        $items = $scraper->fetchCatalog();

        if (empty($items)) {
            $this->warn('  No se pudo obtener el catálogo de Torzo.');
            return 0;
        }

        $normalized = [];
        foreach ($items as $item) {
            $data = $scraper->normalizeProduct($item);

            if (empty($data['supplier_sku'])) {
                continue;
            }

            $normalized[] = $data;
        }

        $count = $syncService->sync($supplier, $normalized);
        $this->info("    Actualizados {$count} productos.");
        return $count;
    }

    protected function runAdrenalineForteScraper(Supplier $supplier, SupplierCatalogSyncService $syncService): int
    {
        $this->info('  Usando scraper específico Adrenaline Forte');
        $scraper = new AdrenalineForteScraper();
        $items = $scraper->fetchCatalog();

        if (empty($items)) {
            $this->warn('  No se pudo obtener el catálogo de Adrenaline Forte.');
            return 0;
        }

        $normalized = [];
        foreach ($items as $item) {
            $data = $scraper->normalizeProduct($item);

            if (empty($data['supplier_sku'])) {
                continue;
            }

            $normalized[] = $data;
        }

        $count = $syncService->sync($supplier, $normalized);
        $this->info("    Actualizados {$count} productos.");
        return $count;
    }

    protected function isAdrenalineForteSupplier(Supplier $supplier): bool
    {
        $website = strtolower(trim((string) $supplier->website));

        return str_contains($website, 'adrenalineunderwear.com');
    }

    protected function parseProductHtml(string $html): array
    {
        $name = $this->matchMeta($html, 'og:title') ?? $this->matchTagText($html, 'h1');
        $price = $this->matchMeta($html, 'product:price:amount') ?? $this->matchTextCurrency($html);
        $availability = $this->matchMeta($html, 'product:availability') ?? $this->matchTextAvailability($html);

        return [
            'name' => $name,
            'price' => $price,
            'availability' => $availability,
            'raw_snapshot' => [
                'scraped_at' => now()->toISOString(),
                'html_length' => strlen($html),
            ],
        ];
    }

    protected function matchMeta(string $html, string $property): ?string
    {
        if (preg_match('/<meta[^>]+property="' . preg_quote($property, '/') . '"[^>]+content="([^"]+)"/i', $html, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/<meta[^>]+name="' . preg_quote($property, '/') . '"[^>]+content="([^"]+)"/i', $html, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function matchTagText(string $html, string $tag): ?string
    {
        if (preg_match('/<' . preg_quote($tag, '/') . '[^>]*>(.*?)<\/' . preg_quote($tag, '/') . '>/is', $html, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        return null;
    }

    protected function matchTextCurrency(string $html): ?string
    {
        if (preg_match('/([\$|€|S\/|USD|PEN]\s?[0-9]+(?:[\.,][0-9]{2})?)/i', $html, $matches)) {
            return preg_replace('/[^0-9\.,]/', '', $matches[1]);
        }

        return null;
    }

    protected function matchTextAvailability(string $html): string
    {
        if (preg_match('/(out\s*of\s*stock|agotado|sin\s*stock)/i', $html)) {
            return 'out_of_stock';
        }

        if (preg_match('/(in\s*stock|disponible|en\s*stock|ready\s*to\s*ship)/i', $html)) {
            return 'in_stock';
        }

        return 'unknown';
    }

    protected function updateSupplierProduct(SupplierProduct $supplierProduct, array $parsed): void
    {
        $supplierProduct->fill([
            'price' => $parsed['price'] !== null ? preg_replace('/[\,]/', '.', $parsed['price']) : $supplierProduct->price,
            'availability' => in_array($parsed['availability'], ['in_stock','out_of_stock'], true)
                ? $parsed['availability']
                : 'unknown',
            'raw_snapshot' => array_merge($supplierProduct->raw_snapshot ?? [], $parsed['raw_snapshot']),
            'last_checked_at' => now(),
        ]);

        $supplierProduct->save();
    }
}
