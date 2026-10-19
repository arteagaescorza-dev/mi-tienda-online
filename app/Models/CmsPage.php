<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use HasFactory;

    protected $table = 'cms_pages';

    protected $fillable = [
        'slug',
        'title',
        'icon',
        'image_url',
        'overlay_gradient',
        'text_color',
        'image_source_mode',
        'gallery_images',
        'overlay_color_start',
        'overlay_color_end',
        'overlay_opacity_start',
        'overlay_opacity_end',
        'content_html',
        'show_in_navbar',
        'show_in_mobile',
        'filter_universe',
        'filter_category',
        'filter_tags',
        'filter_supplier',
        'navbar_order',
        'show_in_footer',
        'footer_order',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'status',
    ];

    protected $casts = [
        'show_in_navbar' => 'boolean',
        'show_in_mobile' => 'boolean',
        'show_in_footer' => 'boolean',
        'navbar_order' => 'integer',
        'footer_order' => 'integer',
        'gallery_images' => 'array',
        'overlay_opacity_start' => 'integer',
        'overlay_opacity_end' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInNavbar($query)
    {
        return $query->active()
            ->where('show_in_navbar', true)
            ->orderBy('navbar_order');
    }

    public function scopeInMobileNavbar($query)
    {
        return $query->active()
            ->where('show_in_mobile', true)
            ->orderBy('navbar_order');
    }

    public function scopeInFooter($query)
    {
        return $query->active()
            ->where('show_in_footer', true)
            ->orderBy('footer_order');
    }

    public static function hexToRgba(?string $hex, int $opacityPercent = 100): string
    {
        $hex = ltrim($hex ?? '#000000', '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            $hex = '000000';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $alpha = round(max(0, min(100, $opacityPercent)) / 100, 2);

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    public function getTextColor(): string
    {
        return !empty($this->text_color) ? $this->text_color : '#ffffff';
    }

    public function getOverlayGradientCss(): string
    {
        $val = trim($this->overlay_gradient ?? '');

        if (!empty($val) && in_array($val, ['indigo', 'rose', 'emerald', 'amber', 'subtle'])) {
            return match ($val) {
                'indigo' => 'linear-gradient(180deg, rgba(30, 27, 75, 0.3) 0%, rgba(49, 46, 129, 0.88) 100%)',
                'rose' => 'linear-gradient(180deg, rgba(76, 5, 25, 0.3) 0%, rgba(136, 19, 55, 0.88) 100%)',
                'emerald' => 'linear-gradient(180deg, rgba(2, 44, 34, 0.3) 0%, rgba(6, 78, 59, 0.88) 100%)',
                'amber' => 'linear-gradient(180deg, rgba(69, 26, 3, 0.3) 0%, rgba(120, 53, 15, 0.88) 100%)',
                'subtle' => 'linear-gradient(180deg, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.65) 100%)',
            };
        }

        if (str_starts_with($val, 'linear-gradient') || str_starts_with($val, 'radial-gradient')) {
            return $val;
        }

        // Si el usuario configuró colores y opacidades custom directamente
        if (!empty($this->overlay_color_start) && !empty($this->overlay_color_end)) {
            $rgbaStart = static::hexToRgba($this->overlay_color_start, $this->overlay_opacity_start ?? 25);
            $rgbaEnd = static::hexToRgba($this->overlay_color_end, $this->overlay_opacity_end ?? 85);
            return "linear-gradient(180deg, {$rgbaStart} 0%, {$rgbaEnd} 100%)";
        }

        return 'linear-gradient(180deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.88) 100%)';
    }

    /**
     * Resuelve la lista de imágenes para carrusel automático o imagen estática.
     * Permite conmutación automática desde las prendas del catálogo según filtros.
     */
    public function getResolvedImages(): array
    {
        $images = [];

        // Modo Catálogo: Extraer dinámicamente imágenes de productos del catálogo
        if ($this->image_source_mode === 'catalog') {
            $query = Product::where('visible', true);

            if (!empty($this->filter_universe) && $this->filter_universe !== 'all') {
                $univ = strtolower($this->filter_universe);
                $query->where(function ($q) use ($univ) {
                    $q->where('meta->website_section', $univ)
                      ->orWhereRaw("LOWER(brand) LIKE ?", ["%{$univ}%"]);
                });
            }

            if (!empty($this->filter_category) && $this->filter_category !== 'all') {
                $cat = strtolower($this->filter_category);
                $query->where(function ($q) use ($cat) {
                    $q->where('meta->category', 'like', "%{$cat}%")
                      ->orWhereRaw("LOWER(title) LIKE ?", ["%{$cat}%"]);
                });
            }

            $products = $query->limit(6)->get();

            foreach ($products as $prod) {
                $meta = $prod->meta ?? [];
                if (!empty($meta['image']) && is_string($meta['image'])) {
                    $images[] = $meta['image'];
                } elseif (!empty($meta['images']) && is_array($meta['images'])) {
                    foreach ($meta['images'] as $img) {
                        if (is_string($img) && !empty($img)) {
                            $images[] = $img;
                        }
                    }
                }
            }

            $images = array_values(array_unique(array_filter($images)));
        }

        // Si es la página de convenios/aliados, extraer posters de eventos activos en Hub 7
        if (empty($images) && in_array($this->slug, ['aliados', 'convenios', 'convenios-con-aliados'])) {
            $campaignBanners = \App\Models\AllyCampaignDirA::where('status', 'active')
                ->whereNotNull('banner_image')
                ->where('banner_image', '!=', '')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->pluck('banner_image')
                ->toArray();
            if (!empty($campaignBanners)) {
                $images = array_values(array_unique(array_filter($campaignBanners)));
            }
        }

        // Si no es modo catálogo o no arrojó imágenes, usar galería manual o image_url principal
        if (empty($images)) {
            if (!empty($this->gallery_images) && is_array($this->gallery_images)) {
                $images = array_values(array_filter($this->gallery_images));
            }
            if (empty($images) && !empty($this->image_url)) {
                $images = [$this->image_url];
            }
        }

        return $images;
    }

    /**
     * Determina si la página tiene configuración reactiva de catálogo.
     */
    public function hasCatalogFilters(): bool
    {
        if (in_array($this->slug, ['aliados', 'convenios', 'convenios-con-aliados'])) {
            return false;
        }

        return (!empty($this->filter_category) && $this->filter_category !== 'all')
            || (!empty($this->filter_universe) && $this->filter_universe !== 'all')
            || (!empty($this->filter_supplier) && $this->filter_supplier !== 'all')
            || !empty($this->filter_tags)
            || ($this->image_source_mode === 'catalog');
    }

    /**
     * Obtiene la categoría efectiva (manual o inferida de slug/título para prendas conocidas).
     */
    public function getEffectiveCategory(): ?string
    {
        if (!empty($this->filter_category) && $this->filter_category !== 'all') {
            return $this->filter_category;
        }

        $knownCategories = ['tanga', 'tangas', 'boxer', 'boxers', 'jock', 'jocks', 'jockstrap', 'jockstraps', 'bikini', 'bikinis', 'tanktop', 'tanktops', 'tank-top', 'swimwear'];
        $slug = strtolower($this->slug ?? '');
        $title = strtolower($this->title ?? '');

        foreach ($knownCategories as $cat) {
            if ($slug === $cat || str_contains($slug, $cat) || str_contains($title, $cat)) {
                return $cat;
            }
        }

        return null;
    }

    /**
     * Parámetros de consulta reactivos para guiar al catálogo filtrado.
     */
    public function getCatalogParams(?string $fallbackUniverse = null): array
    {
        $params = ['tab' => 'catalog'];

        $cat = $this->getEffectiveCategory();
        if (!empty($cat)) {
            $params['categoria'] = $cat;
        }

        if (!empty($this->filter_universe) && $this->filter_universe !== 'all') {
            $params['universo'] = $this->filter_universe;
        } elseif ($fallbackUniverse && $fallbackUniverse !== 'all' && $fallbackUniverse !== 'terra') {
            $params['universo'] = $fallbackUniverse;
        }

        if (!empty($this->filter_supplier) && $this->filter_supplier !== 'all') {
            $params['proveedor'] = $this->filter_supplier;
        }

        if (!empty($this->filter_tags)) {
            $params['tags'] = $this->filter_tags;
        }

        return $params;
    }

    /**
     * Genera la URL canónica hacia el catálogo con los filtros aplicados.
     */
    public function getCatalogUrl(?string $fallbackUniverse = null): string
    {
        return route('store.catalog', $this->getCatalogParams($fallbackUniverse));
    }
}
