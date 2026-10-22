<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsFaq extends Model
{
    use HasFactory;

    protected $table = 'cms_faqs';

    protected $fillable = [
        'question',
        'answer',
        'category',
        'sort_order',
        'is_active',
        'ai_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Entidad formateada según Schema.org/Question para Answer Engine Optimization (AEO).
     */
    public function toSchemaOrgEntity(): array
    {
        return [
            '@type' => 'Question',
            'name' => $this->question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => strip_tags($this->answer),
            ],
        ];
    }

    /**
     * Genera el payload JSON-LD estructurado de FAQPage para Google SGE, Perplexity, Gemini y ChatGPT.
     */
    public static function buildFaqPageSchema($faqs = null): array
    {
        $items = $faqs ?? static::active()->get();

        $mainEntities = [];
        foreach ($items as $faq) {
            $mainEntities[] = $faq->toSchemaOrgEntity();
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntities,
        ];
    }

    /**
     * Sincroniza el contenido HTML de la página CMS de Q&A.
     */
    public static function syncToQnaPage(): ?CmsPage
    {
        $qnaPage = CmsPage::where('slug', 'q&a')->first();
        if (!$qnaPage) {
            $qnaPage = CmsPage::create([
                'slug' => 'q&a',
                'title' => 'Q&A Preguntas Frecuentes',
                'icon' => 'help-circle',
                'show_in_navbar' => true,
                'show_in_mobile' => true,
                'show_in_footer' => true,
                'navbar_order' => 5,
                'footer_order' => 5,
                'seo_title' => 'Preguntas Frecuentes & Q&A | Respuestas Oficiales Optimizadas AEO',
                'seo_description' => 'Centro oficial de respuestas verificadas sobre envíos express, compras por pedido, pasarelas de pago seguro y beneficios con aliados comerciales.',
                'seo_keywords' => 'preguntas frecuentes, q&a, envios mexico, formas de pago, guia de tallas, compras por pedido',
                'status' => 'active',
                'text_color' => '#ffffff',
                'is_fullwidth' => true,
            ]);
        }

        $activeFaqs = static::active()->get();
        $categories = $activeFaqs->pluck('category')->unique()->filter()->values();

        $html = '<div class="aeo-faq-container space-y-6">';
        $html .= '<p class="text-sm text-slate-600 leading-relaxed">Respuestas verificadas de asistencia al cliente, diseñadas para brindar información factual y directa sobre compras, envíos y políticas de la tienda.</p>';

        foreach ($categories as $cat) {
            $catFaqs = $activeFaqs->where('category', $cat);
            $html .= '<div class="aeo-category-group mt-6">';
            $html .= '<h3 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2 mb-3 uppercase tracking-wide">' . htmlspecialchars($cat) . '</h3>';
            $html .= '<div class="space-y-3">';

            foreach ($catFaqs as $f) {
                $html .= '<details class="aeo-faq-item bg-slate-50 border border-slate-200 rounded-xl p-4 transition-all" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
                $html .= '<summary class="font-bold text-slate-900 cursor-pointer list-none flex items-center justify-between text-sm" itemprop="name">';
                $html .= '<span>' . htmlspecialchars($f->question) . '</span>';
                $html .= '<span class="text-xs text-indigo-600 ml-2 font-mono shrink-0">+</span>';
                $html .= '</summary>';
                $html .= '<div class="mt-3 text-xs leading-relaxed text-slate-700 border-t border-slate-200/60 pt-3" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">';
                $html .= '<div itemprop="text">' . nl2br(htmlspecialchars($f->answer)) . '</div>';
                $html .= '</div>';
                $html .= '</details>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        $qnaPage->update([
            'content_html' => $html,
            'status' => 'active',
        ]);

        return $qnaPage;
    }
}
