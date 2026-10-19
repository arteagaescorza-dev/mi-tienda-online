<?php

namespace App\Services\SupplierScrapers;

use Illuminate\Support\Facades\Http;

class AdrenalineForteScraper
{
    protected string $baseUrl = 'https://www.adrenalineunderwear.com';

    public function fetchCatalog(): array
    {
        $allProducts = [];
        $page = 1;
        $limit = 250;
        $maxPages = 20;

        while ($page <= $maxPages) {
            if (app()->environment('local', 'development') && ! app()->runningUnitTests()) {
                sleep(random_int(3, 5));
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json, text/plain, */*',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                ])
                ->get($this->baseUrl . '/products.json', [
                    'limit' => $limit,
                    'page' => $page,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Adrenaline Forte respondio HTTP ' . $response->status() . '.');
            }

            $payload = $response->json();
            $products = is_array($payload) && isset($payload['products']) && is_array($payload['products'])
                ? $payload['products']
                : [];

            if (empty($products)) {
                break;
            }

            $allProducts = array_merge($allProducts, $products);

            if (count($products) < $limit) {
                break;
            }

            $page++;
        }

        return $allProducts;
    }

    public function normalizeProduct($item): array
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        $variants = $this->normalizeVariants($item->variants ?? []);
        $firstVariantSku = collect($variants)->pluck('sku')->filter()->first();
        $supplierSku = $firstVariantSku ?: (string) ($item->handle ?? $item->id ?? '');

        $prices = collect($variants)
            ->pluck('price')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        $price = $prices->isNotEmpty() ? $prices->min() : null;
        $inStock = collect($variants)->contains(fn ($variant) => ($variant['available'] ?? false) === true);
        $images = $this->normalizeImages($item->images ?? []);
        $description = $this->sanitizeDescription((string) ($item->body_html ?? ''));

        return [
            'supplier_sku' => $supplierSku,
            'supplier_url' => $this->baseUrl . '/products/' . ($item->handle ?? ''),
            'price' => $price,
            'availability' => $inStock ? 'in_stock' : 'out_of_stock',
            'raw_snapshot' => [
                'title' => $item->title ?? null,
                'name' => $item->title ?? null,
                'description' => $description,
                'brand' => 'Adrenaline Forte',
                'categoria' => $item->product_type ?: 'Catalogo general',
                'handle' => $item->handle ?? null,
                'product_id' => $item->id ?? null,
                'vendor' => $item->vendor ?? null,
                'tags' => $item->tags ?? null,
                'imagen1' => $images[0] ?? null,
                'imagen2' => $images[1] ?? null,
                'images' => $images,
                'variations' => $variants,
                'source' => 'shopify',
            ],
        ];
    }

    protected function normalizeVariants($variants): array
    {
        if (! is_array($variants)) {
            return [];
        }

        return collect($variants)->map(function ($variant) {
            if (is_array($variant)) {
                $variant = (object) $variant;
            }

            return [
                'id' => $variant->id ?? null,
                'title' => $variant->title ?? null,
                'size' => $variant->option1 ?? $variant->title ?? null,
                'sku' => $variant->sku ?? null,
                'available' => (bool) ($variant->available ?? false),
                'price' => isset($variant->price) ? (float) $variant->price : null,
            ];
        })->values()->all();
    }

    protected function normalizeImages($images): array
    {
        if (! is_array($images)) {
            return [];
        }

        return collect($images)->map(function ($image) {
            if (is_array($image)) {
                $image = (object) $image;
            }

            return $image->src ?? null;
        })->filter()->unique()->values()->all();
    }

    protected function sanitizeDescription(string $html): string
    {
        $plain = strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = preg_replace('/\s+/', ' ', $plain);

        return trim((string) $plain);
    }
}
