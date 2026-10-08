<?php

namespace App\Services\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class SkuGeneratorService
{
    /**
     * Mapeo canonico de prefijos de categoria para el portal RevOps.
     */
    protected array $categoryPrefixMap = [
        'textil' => 'TXT',
        'ropa-interior' => 'TXT',
        'ropa_interior' => 'TXT',
        'boxers' => 'TXT',
        'calzones' => 'TXT',
        'panties' => 'TXT',
        'lenceria' => 'TXT',
        'bras' => 'TXT',
        'tops' => 'TXT',
        'apparel' => 'TXT',
        'trajes-de-bano' => 'TXT',
        'cosmetica' => 'CSM',
        'maquillaje' => 'CSM',
        'labiales' => 'CSM',
        'bases' => 'CSM',
        'correctores' => 'CSM',
        'skincare' => 'CSM',
        'cuidado-facial' => 'CSM',
        'perfumeria' => 'FRG',
        'fragancias' => 'FRG',
        'colonias' => 'FRG',
        'lociones' => 'FRG',
        'aromas' => 'FRG',
        'accesorios' => 'ACC',
        'arneses' => 'ACC',
        'fajas' => 'ACC',
        'calcetines' => 'ACC',
    ];

    /**
     * Genera un codigo SKU estructurado y garantizado como unico.
     *
     * @param array $params [
     *     'category' => string|int|null,
     *     'brand' => string|null,
     *     'title' => string|null,
     *     'variant_code' => string|array|null,
     *     'prefix_override' => string|null,
     * ]
     * @return array
     */
    public function generateSku(array $params = []): array
    {
        $prefix = $this->resolveCategoryPrefix(
            $params['category'] ?? ($params['title'] ?? ($params['name'] ?? null)),
            $params['prefix_override'] ?? null
        );

        $brandCandidate = $params['brand'] ?? null;
        if (empty($brandCandidate) && !empty($params['supplier_id'])) {
            $sup = \App\Models\Supplier::find($params['supplier_id']);
            if ($sup) {
                $brandCandidate = $sup->name;
            }
        } elseif (empty($brandCandidate) && !empty($params['supplier'])) {
            $brandCandidate = $params['supplier'];
        }

        $brandCode = $this->resolveBrandCode($brandCandidate);

        // Resolver codigo de tela opcional (Regla 4 y 5)
        $fabricCandidate = $params['fabric'] ?? ($params['fabric_type'] ?? null);
        $fabricCode = $this->resolveFabricCode($fabricCandidate);

        // Resolver sufijo opcional de variante o color (Regla 4)
        $variantCandidate = $params['variant_code'] ?? ($params['variant'] ?? null);
        if (empty($variantCandidate) && !empty($params['color_name'])) {
            $variantCandidate = $this->resolveColorCode($params['color_name']);
        }
        $variantSuffix = $this->resolveVariantSuffix($variantCandidate);

        $sequenceData = $this->resolveNextConsecutiveSequence($prefix, $brandCode);
        $nextSeq = $sequenceData['sequence'];
        $formattedSeq = $sequenceData['formatted_sequence'];

        $skuParts = [$prefix, $brandCode];
        if (!empty($fabricCode)) {
            $skuParts[] = $fabricCode;
        }
        $skuParts[] = $formattedSeq;
        $baseSku = implode('-', $skuParts);

        $finalSku = $variantSuffix ? "{$baseSku}-{$variantSuffix}" : $baseSku;

        // Validar unicidad total
        $guardIterations = 0;
        while ($this->skuExistsInDatabase($finalSku) && $guardIterations < 100) {
            $nextSeq++;
            $formattedSeq = str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
            $skuParts = [$prefix, $brandCode];
            if (!empty($fabricCode)) {
                $skuParts[] = $fabricCode;
            }
            $skuParts[] = $formattedSeq;
            $baseSku = implode('-', $skuParts);
            $finalSku = $variantSuffix ? "{$baseSku}-{$variantSuffix}" : $baseSku;
            $guardIterations++;
        }

        return [
            'sku' => $finalSku,
            'base_sku' => $baseSku,
            'category_prefix' => $prefix,
            'brand_code' => $brandCode,
            'sequence' => $nextSeq,
            'formatted_sequence' => $formattedSeq,
            'variant_code' => $variantSuffix ?: null,
        ];
    }

    /**
     * Resuelve el prefijo de 3 a 4 letras de la categoria.
     */
    public function resolveCategoryPrefix(mixed $category, ?string $override = null): string
    {
        if (!empty($override)) {
            $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $override));
            if (!empty($clean)) {
                return substr($clean, 0, 4);
            }
        }

        $categorySlug = '';
        $categoryName = '';

        if ($category instanceof Category) {
            $categorySlug = $category->slug ?? '';
            $categoryName = $category->name ?? '';
        } elseif (is_numeric($category)) {
            $catModel = Category::find((int) $category);
            if ($catModel) {
                $categorySlug = $catModel->slug ?? '';
                $categoryName = $catModel->name ?? '';
            }
        } elseif (is_string($category)) {
            $categorySlug = Str::slug($category);
            $categoryName = $category;
        }

        // Buscar coincidencia en diccionario canonico
        if (!empty($categorySlug) && isset($this->categoryPrefixMap[$categorySlug])) {
            return $this->categoryPrefixMap[$categorySlug];
        }

        // Busqueda por palabras clave en slug o nombre
        $searchTarget = strtolower($categorySlug . ' ' . $categoryName);
        if (preg_match('/(ropa|textil|boxer|calzon|panty|lenceria|bra|top|intimo|trusa)/', $searchTarget)) {
            return 'TXT';
        }
        if (preg_match('/(cosmet|maquill|labial|base|corrector|skincare|derm|piel)/', $searchTarget)) {
            return 'CSM';
        }
        if (preg_match('/(perfum|fraganc|coloni|locion|aroma|edp|edt)/', $searchTarget)) {
            return 'FRG';
        }
        if (preg_match('/(acces|arnes|faja|calcetin|complemento)/', $searchTarget)) {
            return 'ACC';
        }

        // Si no hay coincidencia, extraer siglas alfanumericas o fallback PRD
        if (!empty($categoryName)) {
            $words = preg_split('/\s+/', trim($categoryName));
            if (count($words) >= 2) {
                $initials = '';
                foreach (array_slice($words, 0, 3) as $w) {
                    $initials .= strtoupper(substr($w, 0, 1));
                }
                if (strlen($initials) >= 2) {
                    return str_pad($initials, 3, 'X', STR_PAD_RIGHT);
                }
            }
            $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $categoryName));
            if (strlen($cleanName) >= 3) {
                return substr($cleanName, 0, 3);
            }
        }

        return 'TXT'; // Categoria primaria del portal por defecto
    }

    /**
     * Resuelve las siglas estandarizadas de la marca (2 a 4 caracteres).
     */
    public function resolveBrandCode(?string $brand): string
    {
        if (empty($brand)) {
            return 'GEN'; // Generico / Sin marca
        }

        $cleanBrand = trim($brand);
        $normalized = strtolower(Str::ascii($cleanBrand));

        if (in_array($normalized, ['generico', 'sin marca', 'n/a', 'varios', 'white label', 'white-label'])) {
            return 'GEN';
        }

        // Mapeos conocidos de marcas frecuentes
        $brandMap = [
            'torzo' => 'TRZ',
            'calvin klein' => 'CK',
            'calvin-klein' => 'CK',
            'victoria secret' => 'VS',
            'victoria-secret' => 'VS',
            'terra' => 'TRA',
            'marte' => 'MRT',
            'venus' => 'VNS',
            'tiresias' => 'TRS',
            'afrodita' => 'AFR',
            'rounderwear' => 'RDW',
            'andrew christian' => 'AC',
            'andrew-christian' => 'AC',
            'aussiebum' => 'AB',
            'nike' => 'NIKE',
            'puma' => 'PUMA',
            'adidas' => 'ADD',
            'diesel' => 'DSL',
            'tommy hilfiger' => 'TH',
            'hugo boss' => 'HB',
        ];

        if (isset($brandMap[$normalized])) {
            return $brandMap[$normalized];
        }

        // Si contiene multiples palabras (ej: "Calvin Klein" o "Andrew Christian")
        $words = preg_split('/[\s\-_]+/', $cleanBrand);
        if (count($words) >= 2) {
            $initials = '';
            foreach ($words as $w) {
                $firstChar = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($w, 0, 1)));
                if (!empty($firstChar)) {
                    $initials .= $firstChar;
                }
            }
            if (strlen($initials) >= 2 && strlen($initials) <= 4) {
                return $initials;
            }
            if (strlen($initials) > 4) {
                return substr($initials, 0, 4);
            }
        }

        // Palabra unica: si longitud <= 4, usar directamente (ej: "PUMA", "NIKE", "ZEUS")
        $singleClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($cleanBrand)));
        if (strlen($singleClean) <= 4 && strlen($singleClean) >= 2) {
            return $singleClean;
        }

        // Extraer consonantes para marcas de palabra unica (ej: "Torzo" -> "TRZ", "Diesel" -> "DSL")
        $consonants = preg_replace('/[AEIOUaeiou]/', '', $singleClean);
        if (strlen($consonants) >= 3) {
            return substr($consonants, 0, 3);
        }

        return substr($singleClean, 0, 3);
    }

    /**
     * Obtiene el proximo consecutivo de 4 digitos para el prefijo y marca dados.
     */
    public function resolveNextConsecutiveSequence(string $prefix, string $brandCode): array
    {
        $searchPrefix = "{$prefix}-{$brandCode}-";

        // Obtener SKUs existentes de productos
        $existingProductSkus = Product::where('sku', 'like', "{$searchPrefix}%")
            ->pluck('sku')
            ->toArray();

        // Obtener SKUs existentes de variantes
        $existingVariantSkus = ProductVariant::where('sku', 'like', "{$searchPrefix}%")
            ->pluck('sku')
            ->toArray();

        $allSkus = array_unique(array_merge($existingProductSkus, $existingVariantSkus));
        $maxSeq = 0;

        foreach ($allSkus as $sku) {
            // Extraer los 4 digitos consecutivos que siguen al prefijo y marca
            if (preg_match('/^' . preg_quote($searchPrefix, '/') . '(\d{4})/i', $sku, $matches)) {
                $currentSeq = (int) $matches[1];
                if ($currentSeq > $maxSeq) {
                    $maxSeq = $currentSeq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $formattedSeq = str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

        return [
            'sequence' => $nextSeq,
            'formatted_sequence' => $formattedSeq,
        ];
    }

    /**
     * Resuelve el sufijo para variantes (tallas, volumenes, tonos o colores).
     */
    public function resolveVariantSuffix(mixed $variant): ?string
    {
        if (empty($variant)) {
            return null;
        }

        if (is_array($variant)) {
            $parts = [];
            // Si tiene claves especificas
            if (!empty($variant['size'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['size']));
            } elseif (!empty($variant['talla'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['talla']));
            }

            if (!empty($variant['color'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['color']));
            } elseif (!empty($variant['shade'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['shade']));
            }

            if (!empty($variant['volume'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['volume']));
            } elseif (!empty($variant['volumen'])) {
                $parts[] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant['volumen']));
            }

            if (!empty($parts)) {
                return implode('-', array_filter($parts));
            }

            // Si es un arreglo simple de valores
            $simpleParts = array_map(function ($val) {
                return is_scalar($val) ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $val)) : '';
            }, $variant);

            $filtered = array_filter($simpleParts);
            return !empty($filtered) ? implode('-', $filtered) : null;
        }

        if (is_string($variant)) {
            $clean = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', trim($variant)));
            return !empty($clean) ? $clean : null;
        }

        return null;
    }

    /**
     * Resuelve las siglas estandarizadas del tipo de tela (2 a 3 caracteres).
     */
    public function resolveFabricCode(?string $fabric): ?string
    {
        if (empty($fabric)) {
            return null;
        }

        $clean = strtolower(Str::ascii(trim($fabric)));

        $fabricMap = [
            'modal' => 'MOD',
            'modal seamless' => 'MOD',
            'algodon' => 'ALG',
            'algodon pima' => 'ALG',
            'seda' => 'SED',
            'satin' => 'SED',
            'microfibra' => 'MIC',
            'malla' => 'MAL',
            'mesh' => 'MSH',
            'spandex' => 'SPX',
            'elastano' => 'ELA',
            'licra' => 'LIC',
            'lycra' => 'LIC',
            'encaje' => 'ENC',
            'poliester' => 'POL',
            'lino' => 'LIN',
            'viscosa' => 'VIS',
        ];

        foreach ($fabricMap as $key => $code) {
            if (str_contains($clean, $key)) {
                return $code;
            }
        }

        $letters = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $clean));
        return strlen($letters) >= 3 ? substr($letters, 0, 3) : (strlen($letters) > 0 ? $letters : null);
    }

    /**
     * Resuelve las siglas estandarizadas del color (2 a 3 caracteres).
     */
    public function resolveColorCode(?string $color): ?string
    {
        if (empty($color)) {
            return null;
        }

        $clean = strtolower(Str::ascii(trim($color)));

        $colorMap = [
            'negro' => 'NG',
            'blanco' => 'BL',
            'rojo' => 'RJ',
            'azul' => 'AZ',
            'gris' => 'GR',
            'verde' => 'VD',
            'amarillo' => 'AM',
            'rosa' => 'RS',
            'morado' => 'MR',
            'vino' => 'VN',
            'marino' => 'MN',
            'nude' => 'ND',
            'beige' => 'BG',
            'cafe' => 'CF',
            'plata' => 'PL',
            'dorado' => 'OR',
        ];

        foreach ($colorMap as $key => $code) {
            if (str_contains($clean, $key)) {
                return $code;
            }
        }

        $letters = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $clean));
        return strlen($letters) >= 2 ? substr($letters, 0, 2) : (strlen($letters) > 0 ? $letters : null);
    }

    /**
     * Comprueba si un SKU existe en la tabla de productos o de variantes.
     */
    public function skuExistsInDatabase(string $sku): bool
    {
        return Product::where('sku', $sku)->exists() || ProductVariant::where('sku', $sku)->exists();
    }
}
