<?php

namespace App\Services\Inventory;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DuplicateDetectionService
{
    /**
     * Evalua si un producto candidato tiene duplicados potenciales en el catalogo.
     *
     * @param array $params [
     *     'title' => string,
     *     'name' => string|null,
     *     'brand' => string|null,
     *     'category_id' => int|null,
     *     'price' => float|numeric|null,
     *     'sku' => string|null,
     *     'exclude_product_id' => int|null,
     *     'threshold_warning' => int|null,
     *     'threshold_match' => int|null,
     * ]
     * @return array
     */
    public function checkDuplicate(array $params): array
    {
        $candidateTitle = trim((string) ($params['title'] ?? ($params['name'] ?? '')));
        $candidateBrand = trim((string) ($params['brand'] ?? ''));
        $candidateCategoryId = !empty($params['category_id']) ? (int) $params['category_id'] : null;
        $candidatePrice = isset($params['price']) && is_numeric($params['price']) ? (float) $params['price'] : null;
        $candidateSku = trim((string) ($params['sku'] ?? ''));
        $excludeId = !empty($params['exclude_product_id']) ? (int) $params['exclude_product_id'] : null;

        $thresholdWarning = (int) ($params['threshold_warning'] ?? 50);
        $thresholdMatch = (int) ($params['threshold_match'] ?? 80);

        if (empty($candidateTitle) && empty($candidateSku)) {
            return [
                'has_potential_duplicate' => false,
                'highest_score' => 0.0,
                'risk_level' => 'none',
                'candidate_evaluated' => [
                    'title' => $candidateTitle,
                    'brand' => $candidateBrand,
                    'sku' => $candidateSku,
                ],
                'matches' => [],
            ];
        }

        // Obtener productos para comparacion
        $query = Product::with('categories');
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $products = $query->get();
        $matches = [];

        foreach ($products as $product) {
            $eval = $this->evaluatePair(
                candidate: [
                    'title' => $candidateTitle,
                    'brand' => $candidateBrand,
                    'category_id' => $candidateCategoryId,
                    'price' => $candidatePrice,
                    'sku' => $candidateSku,
                ],
                existing: $product
            );

            if ($eval['similarity_score'] >= $thresholdWarning) {
                $matches[] = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'brand' => $product->brand,
                    'price' => (float) $product->price,
                    'similarity_score' => round($eval['similarity_score'], 1),
                    'match_reasons' => $eval['reasons'],
                ];
            }
        }

        // Ordenar por similitud descendente
        usort($matches, function ($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        $topMatches = array_slice($matches, 0, 5);
        $highestScore = !empty($topMatches) ? (float) $topMatches[0]['similarity_score'] : 0.0;

        $riskLevel = 'none';
        if ($highestScore >= $thresholdMatch) {
            $riskLevel = 'duplicate_match';
        } elseif ($highestScore >= $thresholdWarning) {
            $riskLevel = 'warning';
        }

        return [
            'has_potential_duplicate' => $highestScore >= $thresholdWarning,
            'highest_score' => $highestScore,
            'risk_level' => $riskLevel,
            'candidate_evaluated' => [
                'title' => $candidateTitle,
                'brand' => $candidateBrand,
                'sku' => $candidateSku,
            ],
            'matches' => $topMatches,
        ];
    }

    /**
     * Evalua la similitud entre un producto candidato y un producto existente.
     */
    protected function evaluatePair(array $candidate, Product $existing): array
    {
        $reasons = [];

        // 1. Coincidencia exacta de SKU
        if (!empty($candidate['sku']) && !empty($existing->sku)) {
            if (strcasecmp($candidate['sku'], $existing->sku) === 0) {
                return [
                    'similarity_score' => 100.0,
                    'reasons' => ['Coincidencia exacta de SKU: ' . $existing->sku],
                ];
            }
        }

        $cleanCandidateTitle = $this->normalizeString($candidate['title']);
        $cleanExistingTitle = $this->normalizeString($existing->title ?? '');

        // 2. Coincidencia exacta de titulo normalizado
        if (!empty($cleanCandidateTitle) && $cleanCandidateTitle === $cleanExistingTitle) {
            $score = 95.0;
            $reasons[] = 'Titulo identico en catalogo';

            // Bonus de misma marca
            if (!empty($candidate['brand']) && !empty($existing->brand)) {
                if ($this->normalizeString($candidate['brand']) === $this->normalizeString($existing->brand)) {
                    $score = 100.0;
                    $reasons[] = 'Misma marca: ' . $existing->brand;
                }
            }

            return [
                'similarity_score' => $score,
                'reasons' => $reasons,
            ];
        }

        // 3. Similitud textual de titulo
        $titleScore = $this->calculateTextSimilarity($cleanCandidateTitle, $cleanExistingTitle);

        if ($titleScore >= 75) {
            $reasons[] = sprintf('Similitud de titulo alta (%d%%)', round($titleScore));
        } elseif ($titleScore >= 50) {
            $reasons[] = sprintf('Similitud de titulo parcial (%d%%)', round($titleScore));
        }

        // Ponderacion base: 60% titulo
        $finalScore = $titleScore * 0.60;

        // 4. Evaluacion de Marca (+20% si coincide)
        $brandMatch = false;
        if (!empty($candidate['brand']) && !empty($existing->brand)) {
            $cleanCandBrand = $this->normalizeString($candidate['brand']);
            $cleanExistBrand = $this->normalizeString($existing->brand);
            if ($cleanCandBrand === $cleanExistBrand) {
                $finalScore += 20.0;
                $brandMatch = true;
                $reasons[] = 'Misma marca: ' . $existing->brand;
            } elseif ($titleScore > 60) {
                // Penalizacion leve si el titulo es muy similar pero la marca es marcadamente distinta
                $finalScore -= 5.0;
            }
        } elseif ($titleScore >= 60) {
            // Si una de las dos marcas no esta definida pero el titulo es muy alto, dar un punto neutro
            $finalScore += 10.0;
        }

        // 5. Evaluacion de Categoria (+15% si coincide)
        if (!empty($candidate['category_id'])) {
            $existingCatIds = $existing->categories->pluck('id')->toArray();
            if (in_array((int) $candidate['category_id'], $existingCatIds, true)) {
                $finalScore += 15.0;
                $reasons[] = 'Misma categoria de producto';
            }
        }

        // 6. Proximidad de Precio (+5% si esta dentro de +/- 10%)
        if (!empty($candidate['price']) && (float) $existing->price > 0) {
            $diff = abs((float) $candidate['price'] - (float) $existing->price);
            $maxP = max((float) $candidate['price'], (float) $existing->price);
            if ($diff / $maxP <= 0.10) {
                $finalScore += 5.0;
                $reasons[] = 'Rango de precio muy similar';
            }
        }

        // Si el titulo tiene similitud alta (>=75%) y la marca coincide, calificar como duplicate_match (>=85%)
        if ($titleScore >= 75 && $brandMatch) {
            $finalScore = max($finalScore, 85.0);
        } elseif ($titleScore >= 90 && $brandMatch) {
            $finalScore = max($finalScore, 95.0);
        }

        // Si la similitud de titulo es muy baja (< 35%), limitar el puntaje maximo
        if ($titleScore < 35) {
            $finalScore = min($finalScore, 35.0);
        }

        $clampedScore = max(0.0, min(100.0, $finalScore));

        return [
            'similarity_score' => $clampedScore,
            'reasons' => $reasons,
        ];
    }

    /**
     * Calcula similitud entre dos cadenas de texto combinando n-gramas, tokens y Levenshtein.
     */
    protected function calculateTextSimilarity(string $s1, string $s2): float
    {
        if (empty($s1) || empty($s2)) {
            return 0.0;
        }

        if ($s1 === $s2) {
            return 100.0;
        }

        // 1. similar_text de PHP
        similar_text($s1, $s2, $similarPercent);

        // 2. Levenshtein ratio
        $maxLen = max(strlen($s1), strlen($s2));
        $levDist = levenshtein($s1, $s2);
        $levPercent = $maxLen > 0 ? (1.0 - ($levDist / $maxLen)) * 100.0 : 0.0;

        // 3. Jaccard Index de Tokens de palabras
        $tokens1 = array_unique(array_filter(explode(' ', $s1)));
        $tokens2 = array_unique(array_filter(explode(' ', $s2)));

        $intersection = count(array_intersect($tokens1, $tokens2));
        $union = count(array_unique(array_merge($tokens1, $tokens2)));
        $jaccardPercent = $union > 0 ? ($intersection / $union) * 100.0 : 0.0;

        // Si todos o casi todos los tokens coinciden exactamente (ej. palabras en diferente orden)
        if ($jaccardPercent >= 90) {
            $composite = max($jaccardPercent, ($jaccardPercent * 0.60) + ($similarPercent * 0.25) + ($levPercent * 0.15));
        } else {
            // Ponderar: 40% Jaccard tokens + 35% similar_text + 25% Levenshtein
            $composite = ($jaccardPercent * 0.40) + ($similarPercent * 0.35) + ($levPercent * 0.25);
        }

        return max(0.0, min(100.0, $composite));
    }

    /**
     * Normaliza una cadena para comparaciones insensibles a mayusculas, acentos y puntuacion.
     */
    protected function normalizeString(string $str): string
    {
        $ascii = Str::ascii($str);
        $lower = strtolower($ascii);
        // Quitar signos de puntuacion y caracteres no alfanumericos
        $clean = preg_replace('/[^a-z0-9\s]/', ' ', $lower);
        // Colapsar multiples espacios
        return trim(preg_replace('/\s+/', ' ', $clean));
    }
}
