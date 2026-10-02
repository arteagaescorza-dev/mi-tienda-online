<?php

namespace Database\Seeders;

use App\Models\ScrapedPendingItem;
use App\Models\SupplierProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PopulateScrapedPendingItemsSeeder extends Seeder
{
    public function run(): void
    {
        $supplierProducts = SupplierProduct::all();
        $count = 0;

        foreach ($supplierProducts as $sp) {
            $snapshot = is_array($sp->raw_snapshot) ? $sp->raw_snapshot : [];
            $rawTitle = $snapshot['title'] ?? $snapshot['name'] ?? $sp->supplier_sku;
            $cleanTitle = trim(Str::title(str_replace(['_', '-'], ' ', (string) $rawTitle)));
            
            $desc = $snapshot['description'] ?? "Prenda oficial de diseño {$cleanTitle} importada del catálogo oficial de Torzo.";
            $desc = trim(preg_replace('/\s+/', ' ', (string) $desc));

            // Detección de Categoría
            $catRaw = strtoupper((string) ($snapshot['categoria'] ?? $snapshot['category'] ?? ''));
            $titleRaw = strtoupper($cleanTitle);
            $suggestedCategory = match(true) {
                str_contains($catRaw, 'TANGA') || str_contains($titleRaw, 'TANGA') => 'Tanga',
                str_contains($catRaw, 'BIKINI') || str_contains($titleRaw, 'BIKINI') => 'Bikini',
                str_contains($catRaw, 'JOCK') || str_contains($titleRaw, 'JOCK') || str_contains($titleRaw, 'SUSPENSORIO') => 'Jockstraps',
                str_contains($catRaw, 'BOXER') || str_contains($catRaw, 'BRIEF') || str_contains($titleRaw, 'BOXER') || str_contains($titleRaw, 'BRIEF') => 'Boxers',
                str_contains($catRaw, 'TANK') || str_contains($titleRaw, 'CAMISILLA') || str_contains($titleRaw, 'TANK') => 'Camisillas',
                str_contains($catRaw, 'SHIRT') || str_contains($catRaw, 'PLAYERA') || str_contains($titleRaw, 'PLAYERA') => 'Playeras',
                str_contains($catRaw, 'SWIM') || str_contains($titleRaw, 'BAÑO') || str_contains($titleRaw, 'SWIM') => 'Trajes de Baño',
                str_contains($catRaw, 'TRUZA') || str_contains($titleRaw, 'TRUZA') => 'Truzas',
                str_contains($catRaw, 'COMBO') || str_contains($titleRaw, 'PACK') || str_contains($titleRaw, 'COMBO') => 'Combos',
                default => 'Ropa Interior',
            };

            // Detección de Tela
            $textSearch = strtolower($cleanTitle . ' ' . $desc);
            $suggestedFabric = match(true) {
                str_contains($textSearch, 'licra') || str_contains($textSearch, 'spandex') || str_contains($textSearch, 'lycra') => 'Microfibra Spandex / Licra',
                str_contains($textSearch, 'algod') || str_contains($textSearch, 'cotton') => 'Algodón & Modal',
                str_contains($textSearch, 'malla') || str_contains($textSearch, 'mesh') || str_contains($textSearch, 'transpirable') => 'Malla / Mesh Transpirable',
                str_contains($textSearch, 'bamb') => 'Bambú Eco Air',
                str_contains($textSearch, 'modal') => 'Modal Seamless',
                str_contains($textSearch, 'seda') || str_contains($textSearch, 'satin') => 'Seda Satinada',
                default => 'Microfibra Spandex / Licra',
            };

            // Detección de Color
            $colorSearch = strtoupper($cleanTitle . ' ' . $desc);
            [$detectedColorName, $detectedColorHex, $colorType] = match(true) {
                str_contains($colorSearch, 'ROJO') || str_contains($colorSearch, 'ROJA') => ['Rojo Pasión', '#dc2626', 'solid'],
                str_contains($colorSearch, 'AZUL MARINO') || str_contains($colorSearch, 'NAVY') => ['Azul Marino', '#1e3a8a', 'solid'],
                str_contains($colorSearch, 'AZUL REY') || str_contains($colorSearch, 'AZUL') => ['Azul Rey', '#1d4ed8', 'solid'],
                str_contains($colorSearch, 'BLANCO') || str_contains($colorSearch, 'BLANCA') => ['Blanco Puro', '#f8fafc', 'solid'],
                str_contains($colorSearch, 'VERDE MILITAR') || str_contains($colorSearch, 'OLIVA') => ['Verde Militar', '#365314', 'solid'],
                str_contains($colorSearch, 'VERDE') => ['Verde Esmeralda', '#16a34a', 'solid'],
                str_contains($colorSearch, 'AMARILLO') || str_contains($colorSearch, 'MOSTAZA') => ['Amarillo Mostaza', '#eab308', 'solid'],
                str_contains($colorSearch, 'NARANJA') || str_contains($colorSearch, 'NEON') => ['Naranja Neón', '#ea580c', 'solid'],
                str_contains($colorSearch, 'MORADO') || str_contains($colorSearch, 'UVA') || str_contains($colorSearch, 'VIOLETA') => ['Morado Uva', '#7e22ce', 'solid'],
                str_contains($colorSearch, 'ROSA') || str_contains($colorSearch, 'FUCSIA') || str_contains($colorSearch, 'FIUSHA') => ['Rosa Neón', '#ec4899', 'solid'],
                str_contains($colorSearch, 'GRIS') || str_contains($colorSearch, 'PLATA') => ['Gris Jaspe', '#64748b', 'solid'],
                str_contains($colorSearch, 'VINO') || str_contains($colorSearch, 'BORGOÑA') || str_contains($colorSearch, 'BURGUNDY') => ['Rojo Borgoña', '#881337', 'solid'],
                str_contains($colorSearch, 'BEIGE') || str_contains($colorSearch, 'PIEL') || str_contains($colorSearch, 'NUDE') => ['Nude Piel', '#f5d0b5', 'solid'],
                str_contains($colorSearch, 'CAFE') || str_contains($colorSearch, 'MARRON') => ['Café Moka', '#78350f', 'solid'],
                default => ['Negro Profundo', '#0f172a', 'solid'],
            };

            // Detección de Imágenes Limpias
            $rawImages = $snapshot['images'] ?? [];
            if (empty($rawImages)) {
                $rawImages = array_filter([$snapshot['imagen1'] ?? null, $snapshot['imagen2'] ?? null]);
            }
            $cleanImages = [];
            foreach ($rawImages as $img) {
                if (!is_string($img) || empty(trim($img))) continue;
                $img = trim($img);
                if (!Str::startsWith($img, ['http://', 'https://'])) {
                    $img = 'https://torzo.kexe.mx/' . ltrim($img, '/.');
                }
                $cleanImages[] = str_replace('/./', '/', $img);
            }
            $cleanImages = array_values(array_unique($cleanImages));
            $mainImage = $cleanImages[0] ?? 'https://torzo.kexe.mx/imagenes_productos/default.jpg';

            // Detección de Tallas y Existencias
            $sizes = ['ch' => 0, 'm' => 0, 'g' => 0, 'xl' => 0];
            $totalStock = 0;
            if (!empty($snapshot['variations']) && is_array($snapshot['variations'])) {
                foreach ($snapshot['variations'] as $v) {
                    $sz = strtolower(trim((string) ($v['size'] ?? '')));
                    $stk = max(0, (int) ($v['stock'] ?? 0));
                    if ($sz === 'ch' || $sz === 's') $sizes['ch'] += $stk;
                    elseif ($sz === 'm') $sizes['m'] += $stk;
                    elseif ($sz === 'g' || $sz === 'l') $sizes['g'] += $stk;
                    elseif ($sz === 'xg' || $sz === 'xl') $sizes['xl'] += $stk;
                    elseif (!empty($sz)) $sizes[$sz] = $stk;
                    $totalStock += $stk;
                }
            }
            if ($totalStock === 0) {
                $totalStock = (int) ($snapshot['stock'] ?? 8);
                if ($totalStock <= 0) $totalStock = 8;
                $sizes = [
                    'ch' => (int) round($totalStock * 0.2),
                    'm' => (int) round($totalStock * 0.4),
                    'g' => (int) round($totalStock * 0.3),
                    'xl' => (int) round($totalStock * 0.1)
                ];
            }

            // Precios y Costos
            $extPrice = (float) ($sp->price && $sp->price > 0 ? $sp->price : 180.00);
            $scrapedCost = round($extPrice * 0.5, 2);
            $curatedPrice = $extPrice;

            ScrapedPendingItem::updateOrCreate(
                [
                    'external_sku' => $sp->supplier_sku,
                ],
                [
                    'supplier_id' => $sp->supplier_id ?: 4,
                    'supplier_product_id' => $sp->id,
                    'external_sku' => $sp->supplier_sku,
                    'external_title' => $cleanTitle,
                    'suggested_category' => $suggestedCategory,
                    'suggested_subcategory' => 'Básicos',
                    'suggested_fabric' => $suggestedFabric,
                    'detected_color_name' => $detectedColorName,
                    'detected_color_hex' => $detectedColorHex,
                    'color_type' => $colorType,
                    'external_price' => $extPrice,
                    'scraped_cost' => $scrapedCost,
                    'curated_price' => $curatedPrice,
                    'supplier_stock' => $totalStock,
                    'sizes' => $sizes,
                    'external_image_url' => $mainImage,
                    'images' => $cleanImages,
                    'origin_url' => $sp->supplier_url ?: 'https://torzo.kexe.mx',
                    'curated_title' => $cleanTitle,
                    'curated_brand' => 'Torzo',
                    'curated_fabric' => $suggestedFabric,
                    'curated_color' => $detectedColorName,
                    'curated_design_type' => str_contains(strtolower($cleanTitle), 'estamp') ? 'con_diseno' : 'sin_diseno',
                    'curated_website_section' => 'basicos',
                    'curation_status' => 'pending',
                    'raw_snapshot' => $snapshot,
                    'scraped_description' => $desc,
                ]
            );

            $count++;
        }

        $this->command?->info("Poblados exitosamente {$count} productos scrapeados en la bandeja de curaduría.");
    }
}
