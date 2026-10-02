<?php

namespace App\Services\Inventory;

use App\Services\Ai\GeminiService;
use Illuminate\Support\Str;

class ProductAiCopilotService
{
    public function __construct(
        protected ?GeminiService $gemini = null
    ) {
        $this->gemini = $gemini ?? app(GeminiService::class);
    }

    /**
     * Genera contenido comercial persuasivo, especificaciones tecnicas y tags SEO
     * a partir de los atributos tecnicos del producto.
     *
     * @param array $attributes [
     *     'name' => string,
     *     'title' => string|null,
     *     'category' => string|null,
     *     'brand' => string|null,
     *     'fabric_type' => string|null,
     *     'color_name' => string|null,
     *     'design_type' => string|null,
     *     'target_audience' => string|null,
     *     'pvp' => float|numeric|null,
     * ]
     * @return array
     */
    public function generateCommercialContent(array $attributes): array
    {
        $name = trim((string) ($attributes['name'] ?? ($attributes['title'] ?? 'Prenda de Ropa Interior')));
        $category = trim((string) ($attributes['category'] ?? 'Ropa Interior'));
        $brand = trim((string) ($attributes['brand'] ?? 'Torzo'));
        $fabric = trim((string) ($attributes['fabric_type'] ?? 'Algodon Pima & Modal'));
        $color = trim((string) ($attributes['color_name'] ?? 'Negro Clasico'));
        $designType = trim((string) ($attributes['design_type'] ?? 'sin_diseno'));
        $audience = trim((string) ($attributes['target_audience'] ?? 'Marte'));
        $price = isset($attributes['pvp']) && is_numeric($attributes['pvp']) ? (float) $attributes['pvp'] : null;

        // Intentar generación inteligente enriquecida mediante Google Gemini AI
        if ($this->gemini && $this->gemini->isConfigured()) {
            $geminiPrompt = "Eres un redactor experto en moda íntima masculina y E-Commerce D2C de alta conversión para 'Mi Tienda Online'.
Genera contenido comercial y SEO para la siguiente prenda de ropa interior:
- Nombre base: {$name}
- Categoría: {$category}
- Marca: {$brand}
- Tipo de Tela / Composición: {$fabric}
- Color: {$color}
- Acabado: {$designType}
- Audiencia: {$audience}" . ($price ? " - Precio: MXN \${$price}" : "") . "

Genera un JSON con esta estructura exacta:
{
  \"optimized_title\": \"Título limpio y vendedor (ej: {$brand} - {$name})\",
  \"commercial_description\": \"Descripción persuasiva de dos párrafos redactada con tono moderno y sofisticado, destacando soporte anatómico, elasticidad, frescura termorreguladora y durabilidad.\",
  \"bullet_points\": [\"Punto destacado 1\", \"Punto destacado 2\", \"Punto destacado 3\", \"Punto destacado 4\"],
  \"seo_tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\", \"tag6\"]
}";

            $aiResult = $this->gemini->generateJson($geminiPrompt, [
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if (is_array($aiResult) && !empty($aiResult['commercial_description'])) {
                $optimizedTitle = trim((string) ($aiResult['optimized_title'] ?? $this->buildOptimizedTitle($name, $category, $brand, $fabric)));
                $commercialDesc = trim((string) $aiResult['commercial_description']);
                $bulletPoints = is_array($aiResult['bullet_points'] ?? null) && count($aiResult['bullet_points']) > 0
                    ? $aiResult['bullet_points']
                    : $this->buildTechnicalBulletPoints($category, $fabric, $color, $designType);
                $seoTags = is_array($aiResult['seo_tags'] ?? null) && count($aiResult['seo_tags']) > 0
                    ? $aiResult['seo_tags']
                    : $this->buildSeoTags($name, $category, $brand, $fabric, $color, $audience);

                return [
                    'title' => $optimizedTitle,
                    'optimized_title' => $optimizedTitle,
                    'description' => $commercialDesc,
                    'commercial_description' => $commercialDesc,
                    'bullet_points' => $bulletPoints,
                    'technical_bullet_points' => $bulletPoints,
                    'seo_tags' => $seoTags,
                    'suggested_tags' => implode(', ', $seoTags),
                    'category' => $category,
                    'brand' => $brand,
                    'metadata' => [
                        'fabric' => $fabric,
                        'color' => $color,
                        'ai_provider' => 'gemini-flash',
                        'ai_model' => $this->gemini->getModel(),
                    ],
                    'generated_at' => now()->toIso8601String(),
                ];
            }
        }

        // Fallback robusto determinístico si la IA no está disponible
        // 1. Titulo optimizado para conversion
        $optimizedTitle = $this->buildOptimizedTitle($name, $category, $brand, $fabric);

        // 2. Descripcion persuasiva en 2 parrafos
        $commercialDesc = $this->buildCommercialDescription($name, $category, $brand, $fabric, $color, $designType, $audience);

        // 3. Puntos tecnicos destacados
        $bulletPoints = $this->buildTechnicalBulletPoints($category, $fabric, $color, $designType);

        // 4. Tags SEO invisibles para storefront
        $seoTags = $this->buildSeoTags($name, $category, $brand, $fabric, $color, $audience);

        return [
            'title' => $optimizedTitle,
            'optimized_title' => $optimizedTitle,
            'description' => $commercialDesc,
            'commercial_description' => $commercialDesc,
            'bullet_points' => $bulletPoints,
            'technical_bullet_points' => $bulletPoints,
            'seo_tags' => $seoTags,
            'suggested_tags' => implode(', ', $seoTags),
            'category' => $category,
            'brand' => $brand,
            'metadata' => [
                'fabric' => $fabric,
                'color' => $color,
                'ai_provider' => 'heuristic_fallback',
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Construye un titulo limpio y optimizado para SEO D2C.
     */
    protected function buildOptimizedTitle(string $name, string $category, string $brand, string $fabric): string
    {
        $cleanName = trim($name);
        if (stripos($cleanName, $brand) === false && strtolower($brand) !== 'generico' && strtolower($brand) !== 'sin marca') {
            return "{$brand} - {$cleanName}";
        }
        return $cleanName;
    }

    /**
     * Redacta una descripcion comercial persuasiva de dos parrafos estructurados.
     */
    protected function buildCommercialDescription(
        string $name,
        string $category,
        string $brand,
        string $fabric,
        string $color,
        string $designType,
        string $audience
    ): string {
        $p1 = "Experimenta el maximo confort y soporte anatomico con {$name} de {$brand}. Confeccionado minuciosamente a partir de {$fabric}, este diseno en tonalidad {$color} ofrece una sensacion tactil ultrasuave de segunda piel y una transpirabilidad termoreguladora que se adapta a cada uno de tus movimientos cotidianos sin presiones innecesarias.";

        $designNote = ($designType === 'con_diseno')
            ? 'Su acabado exclusivo con patrones estampados de autor resalta tu identidad con elegancia vanguardista.'
            : 'Su silueta limpia y patronaje ergonomico sin costuras visibles garantiza discrecion absoluta bajo cualquier prenda exterior.';

        $p2 = "{$designNote} Incorpora elastico reforzado con memoria que evita enrollamientos indeseados y costuras planas anti-friccion, consolidandose como una pieza imprescindible de alta durabilidad para tu guardarropa intimo.";

        return "{$p1}\n\n{$p2}";
    }

    /**
     * Genera especificaciones tecnicas en forma de vinetas.
     */
    protected function buildTechnicalBulletPoints(
        string $category,
        string $fabric,
        string $color,
        string $designType
    ): array {
        return [
            "Composicion de alto desempeno: {$fabric} con elastano multidireccional.",
            "Coloracion duradera: Tinte ecologico en tono {$color} resistente a multiples ciclos de lavado.",
            "Patronaje ergonomico con copa de soporte anatomico y ajuste anti-friccion.",
            "Pretina elastica suave de 38mm con memoria termica que no marca la cintura.",
            "Cuidado facil: Lavado a maquina con agua fria, secado a baja temperatura sin deformacion.",
        ];
    }

    /**
     * Genera etiquetas SEO y de busqueda para el Storefront.
     */
    protected function buildSeoTags(
        string $name,
        string $category,
        string $brand,
        string $fabric,
        string $color,
        string $audience
    ): array {
        $tags = [
            strtolower(Str::slug($category, ' ')),
            strtolower(Str::slug($brand, ' ')),
            strtolower(Str::slug($fabric, ' ')),
            strtolower(Str::slug($color, ' ')),
            'ropa interior',
            'confort premium',
            'soporte ergonomico',
            'anti friccion',
            'transpirable',
            'alta durabilidad',
        ];

        if (strtolower($audience) === 'marte') {
            $tags[] = 'ropa interior masculina';
            $tags[] = 'boxer hombre';
        } elseif (strtolower($audience) === 'venus') {
            $tags[] = 'lenceria femenina';
            $tags[] = 'moda intima mujer';
        } elseif (strtolower($audience) === 'tiresias') {
            $tags[] = 'prendas afirmativas';
            $tags[] = 'ropa interior no binaria';
        }

        // Agregar palabras clave individuales del nombre
        $words = explode(' ', strtolower(Str::ascii($name)));
        foreach ($words as $w) {
            $w = trim(preg_replace('/[^a-z0-9]/', '', $w));
            if (strlen($w) >= 4 && !in_array($w, $tags, true)) {
                $tags[] = $w;
            }
        }

        return array_values(array_unique(array_filter($tags)));
    }
}
