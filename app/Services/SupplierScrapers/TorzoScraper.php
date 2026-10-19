<?php

namespace App\Services\SupplierScrapers;

use Illuminate\Support\Facades\Http;

class TorzoScraper
{
    protected string $baseUrl = 'https://torzo.kexe.mx';

    public function fetchCatalog(): array
    {
        if (app()->environment('local', 'development') && ! app()->runningUnitTests()) {
            sleep(random_int(3, 5));
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            ])
            ->get($this->baseUrl . '/api/productos/catalogo');

        if (! $response->successful()) {
            throw new \RuntimeException('Torzo respondio HTTP ' . $response->status() . '.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ! array_key_exists('productos', $payload) || ! is_array($payload['productos'])) {
            return [];
        }

        return $payload['productos'];
    }

    public function normalizeProduct($item): array
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $title = $item->titulo ?? $item->title ?? $item->name ?? null;
        $supplierSku = $item->supplier_sku ?? $item->sku ?? $item->product_id ?? null;
        $supplierUrl = $item->supplier_url ?? $item->url ?? $this->buildProductUrl($item);
        $price = $item->price ?? null;
        $availability = $item->availability ?? $this->normalizeAvailability($item);

        $rawSnapshot = $item->raw_snapshot ?? [];
        if (is_object($rawSnapshot)) {
            $rawSnapshot = (array) $rawSnapshot;
        }

        $rawSnapshot = array_merge([
            'title' => $title,
            'name' => $title,
            'description' => $item->descripcion ?? $item->description ?? null,
            'brand' => $item->brand ?? $item->categoria ?? null,
            'categoria' => $item->categoria ?? $item->category ?? null,
            'variations' => $this->extractVariations($item),
            'imagen1' => $item->imagen1 ?? null,
            'imagen2' => $item->imagen2 ?? null,
            'images' => array_values(array_filter([
                $item->imagen1 ?? null,
                $item->imagen2 ?? null,
                $item->imagen3 ?? null,
                $item->imagen4 ?? null,
            ])),
            'product_id' => $item->idProducto ?? null,
            'uuid' => $item->uuid ?? null,
            'sku' => $item->sku ?? null,
            'stock' => $this->extractStock($item),
        ], $rawSnapshot);

        return [
            'supplier_sku' => $supplierSku,
            'supplier_url' => $supplierUrl,
            'price' => $price !== null ? (float) preg_replace('/[^0-9.]/', '', (string) $price) : (isset($item->precio) ? (float) $item->precio : null),
            'availability' => $availability,
            'raw_snapshot' => $rawSnapshot,
        ];
    }

    protected function normalizeAvailability($item): string
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $stock = $this->extractStock($item);

        if ($stock === null) {
            return 'unknown';
        }

        return $stock > 0 ? 'in_stock' : 'out_of_stock';
    }

    protected function extractStock($item): ?int
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $stock = 0;
        $found = false;

        for ($i = 1; $i <= 5; $i++) {
            $prop = 'stockItem' . $i;
            $quantity = null;

            if (is_object($item) && property_exists($item, $prop)) {
                $quantity = $item->{$prop};
            } elseif (is_array($item) && array_key_exists($prop, $item)) {
                $quantity = $item[$prop];
            }

            if ($quantity !== null) {
                $found = true;
                $stock += (int) $quantity;
            }
        }

        return $found ? $stock : null;
    }

    protected function extractVariations($item): array
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $variations = [];

        for ($i = 1; $i <= 5; $i++) {
            $sizeKey = 'item' . $i;
            $stockKey = 'stockItem' . $i;
            $size = $item->{$sizeKey} ?? null;
            $stock = $this->valueAsInt($item->{$stockKey} ?? null);

            if ($size === null && $stock === null) {
                continue;
            }

            $variations[] = [
                'size' => $size,
                'stock' => $stock,
                'available' => $stock === null ? null : $stock > 0,
                'disabled' => $stock !== null ? $stock <= 0 : null,
            ];
        }

        return $variations;
    }

    protected function buildProductUrl($item): ?string
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $productId = $item->idProducto ?? $item->product_id ?? null;
        $title = $item->titulo ?? $item->title ?? $item->name ?? $item->sku ?? null;

        if ($productId === null) {
            return null;
        }

        $slug = $this->slugFromTitle((string) $title);

        return $this->baseUrl . '/producto/' . $productId . '/' . $slug;
    }

    protected function fetchProductDetail(string $productUrl): ?array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            ])
            ->get($this->normalizeUrl($productUrl));

        if (! $response->successful()) {
            return null;
        }

        return $this->parseProductHtml($response->body(), $productUrl);
    }

    protected function parseProductHtml(string $html, string $productUrl): array
    {
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($document);

        $title = $this->firstText($xpath, '//h2[contains(concat(" ", normalize-space(@class), " "), " title ")]')
            ?? $this->firstText($xpath, '//meta[@property="og:title"]/@content');
        $priceText = $this->firstText($xpath, '//h5[1]') ?? $this->matchPrice($html);
        $description = $this->firstText($xpath, '//div[contains(concat(" ", normalize-space(@class), " "), " detailSection ")]//p[1]');
        $brand = $this->firstText($xpath, '//nav[contains(@aria-label, "Ruta")]//a[last()-1]');

        $variations = [];
        $totalStock = 0;
        $variationNodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " detailSection--variations ")]//label[contains(concat(" ", normalize-space(@class), " "), " talla-item ")]');

        if ($variationNodes !== false) {
            foreach ($variationNodes as $node) {
                $size = $this->nodeText($xpath, './/span[contains(concat(" ", normalize-space(@class), " "), " talla-text ")]', $node);
                $stockText = $this->nodeText($xpath, './/span[contains(concat(" ", normalize-space(@class), " "), " stock-badge ")]', $node);
                $stock = $this->extractInteger($stockText);
                $enabled = ! $this->nodeHasClass($node, 'disabledBtnAddCart');

                if ($stock !== null) {
                    $totalStock += $stock;
                }

                $variations[] = [
                    'size' => $size,
                    'stock' => $stock,
                    'available' => $enabled && ($stock === null || $stock > 0),
                    'disabled' => ! $enabled,
                ];
            }
        }

        $images = [];
        $imageNodes = $xpath->query('//img[contains(@src, "/imagenes_productos/")]/@src');

        if ($imageNodes !== false) {
            foreach ($imageNodes as $imageNode) {
                $src = trim((string) $imageNode->nodeValue);

                if ($src === '') {
                    continue;
                }

                $images[] = $this->normalizeImageUrl($src);
            }
        }

        $images = array_values(array_unique($images));

        $productId = $this->extractProductId($productUrl);

        return [
            'supplier_sku' => (string) ($productId ?? $this->slugFromUrl($productUrl)),
            'supplier_url' => $this->normalizeUrl($productUrl),
            'price' => $priceText !== null ? (float) preg_replace('/[^0-9.]/', '', $priceText) : null,
            'availability' => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
            'raw_snapshot' => [
                'title' => $title,
                'name' => $title,
                'description' => $description,
                'brand' => $brand,
                'product_id' => $productId,
                'product_url' => $this->normalizeUrl($productUrl),
                'variations' => $variations,
                'imagen1' => $images[0] ?? null,
                'imagen2' => $images[1] ?? null,
                'images' => $images,
                'stock' => $totalStock,
            ],
        ];
    }

    protected function extractProductUrls(string $html): array
    {
        if (! preg_match_all('/href=["\']([^"\']*\/producto\/\d+\/[^"\']+)["\']/i', $html, $matches)) {
            return [];
        }

        $urls = [];

        foreach ($matches[1] as $url) {
            $urls[] = $this->normalizeUrl(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return array_values(array_unique($urls));
    }

    protected function firstText(\DOMXPath $xpath, string $expression): ?string
    {
        $nodeList = $xpath->query($expression);

        if ($nodeList === false || $nodeList->length === 0) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $nodeList->item(0)->textContent ?? '')) ?: null;
    }

    protected function nodeText(\DOMXPath $xpath, string $expression, \DOMNode $context): ?string
    {
        $nodeList = $xpath->query($expression, $context);

        if ($nodeList === false || $nodeList->length === 0) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $nodeList->item(0)->textContent ?? '')) ?: null;
    }

    protected function nodeHasClass(\DOMNode $node, string $className): bool
    {
        if (! $node instanceof \DOMElement) {
            return false;
        }

        $classes = preg_split('/\s+/', trim((string) $node->getAttribute('class'))) ?: [];

        return in_array($className, $classes, true);
    }

    protected function matchPrice(string $html): ?string
    {
        if (preg_match('/\$\s*[0-9]+(?:[\.,][0-9]{2})?/u', $html, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }

    protected function extractInteger(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/-?\d+/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    protected function valueAsInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function extractProductId(string $productUrl): ?string
    {
        if (preg_match('~/producto/(\d+)~', $productUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function slugFromUrl(string $productUrl): string
    {
        $path = (string) parse_url($productUrl, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        return (string) end($segments);
    }

    protected function slugFromTitle(string $title): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $title));

        if ($clean === '') {
            return 'producto';
        }

        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $clean) ?? 'producto');
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'producto';
    }

    protected function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $this->baseUrl . '/' . ltrim($url, '/');
    }

    protected function normalizeImageUrl(string $url): string
    {
        return $this->normalizeUrl(str_replace('./', '', $url));
    }
}
