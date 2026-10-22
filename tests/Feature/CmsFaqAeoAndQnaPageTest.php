<?php

namespace Tests\Feature;

use App\Models\CmsFaq;
use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsFaqAeoAndQnaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_faqs_and_they_natively_sync_with_qna_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Crear una pregunta frecuente
        $payload = [
            'question' => '¿Cuál es la política de envíos discretos?',
            'answer' => 'Todos nuestros envíos se despachan en empaque opaco sin leyendas comerciales exteriores, protegiendo 100% la confidencialidad de tu compra.',
            'category' => 'Envíos & Entregas',
            'ai_keywords' => 'empaque discreto, privacidad envio, paqueteria neutra',
            'is_active' => 1,
            'sort_order' => 1,
        ];

        $createResp = $this->actingAs($admin)->post(route('portal.store.faqs.store'), $payload);
        $createResp->assertRedirect();
        $createResp->assertSessionHas('success');

        $this->assertDatabaseHas('cms_faqs', [
            'question' => '¿Cuál es la política de envíos discretos?',
            'category' => 'Envíos & Entregas',
            'is_active' => true,
        ]);

        $faq = CmsFaq::where('question', '¿Cuál es la política de envíos discretos?')->first();
        $this->assertNotNull($faq);

        // Verificar que se sincronizó la página /pagina/q&a
        $qnaPage = CmsPage::where('slug', 'q&a')->first();
        $this->assertNotNull($qnaPage);
        $this->assertStringContainsString('¿Cuál es la política de envíos discretos?', $qnaPage->content_html);

        // 2. Actualizar pregunta frecuente
        $updateResp = $this->actingAs($admin)->put(route('portal.store.faqs.update', $faq->id), [
            'question' => '¿Cuál es la política oficial de envíos 100% discretos?',
            'answer' => 'Empaque totalmente neutro y sellado de seguridad para máxima privacidad.',
            'category' => 'Envíos & Entregas',
            'ai_keywords' => 'privacidad total, empaque neutro',
            'is_active' => 1,
            'sort_order' => 1,
        ]);
        $updateResp->assertRedirect();
        $faq->refresh();
        $this->assertEquals('¿Cuál es la política oficial de envíos 100% discretos?', $faq->question);

        // 3. Alternar estado activo / pausado
        $toggleResp = $this->actingAs($admin)->post(route('portal.store.faqs.toggle-status', $faq->id));
        $toggleResp->assertRedirect();
        $faq->refresh();
        $this->assertFalse($faq->is_active);

        // Reactivar
        $this->actingAs($admin)->post(route('portal.store.faqs.toggle-status', $faq->id));
        $faq->refresh();
        $this->assertTrue($faq->is_active);

        // 4. Eliminar pregunta frecuente
        $deleteResp = $this->actingAs($admin)->delete(route('portal.store.faqs.destroy', $faq->id));
        $deleteResp->assertRedirect();
        $this->assertDatabaseMissing('cms_faqs', ['id' => $faq->id]);
    }

    public function test_qna_storefront_page_renders_with_schema_org_faqpage_and_aeo_features()
    {
        // Crear preguntas de prueba
        CmsFaq::create([
            'question' => '¿Aceptan pagos en efectivo en OXXO?',
            'answer' => 'Sí, generamos una referencia de pago Paynet/OXXO con acreditación inmediata.',
            'category' => 'Pagos & Seguridad',
            'is_active' => true,
            'sort_order' => 1,
            'ai_keywords' => 'pago oxxo, efectivo, acreditacion inmediata',
        ]);

        CmsFaq::create([
            'question' => '¿Tienen cobertura de entrega en toda la República Mexicana?',
            'answer' => 'Entregamos en los 32 estados de México mediante FedEx y Estafeta.',
            'category' => 'Envíos & Entregas',
            'is_active' => true,
            'sort_order' => 2,
            'ai_keywords' => 'cobertura mexico, fedex estafeta republica',
        ]);

        CmsFaq::syncToQnaPage();

        // Acceder a /pagina/q&a
        $response = $this->get('/pagina/q&a');
        $response->assertStatus(200);

        // Verificar microformato JSON-LD AEO FAQPage
        $response->assertSee('"@type": "FAQPage"', false);
        $response->assertSee('¿Aceptan pagos en efectivo en OXXO?', false);
        $response->assertSee('¿Tienen cobertura de entrega en toda la República Mexicana?', false);
        $response->assertSee('AEO // CENTRO OFICIAL DE RESPUESTAS DIRECTAS', false);
        $response->assertSee('Pagos &amp; Seguridad', false);
        $response->assertSee('Envíos &amp; Entregas', false);
        $response->assertSee('id="aeoSearchInput"', false);
    }

    public function test_unauthorized_user_cannot_manage_faqs()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->post(route('portal.store.faqs.store'), [
                'question' => 'Hack question',
                'answer' => 'Hack answer',
                'category' => 'General',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_configure_qna_hero_with_gradient_fullwidth_and_custom_styling()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Crear una pregunta y sincronizar página
        CmsFaq::create([
            'question' => '¿Cuál es el horario de atención?',
            'answer' => 'Atención digital 24/7 y soporte humano de 9am a 6pm.',
            'category' => 'General',
            'is_active' => true,
        ]);
        CmsFaq::syncToQnaPage();

        // Actualizar el hero de Q&A
        $response = $this->actingAs($admin)->post(route('portal.store.qna-hero.update'), [
            'title' => 'Centro Oficial Q&A Torzo',
            'seo_description' => 'Respuestas instantáneas verificadas para inteligencia artificial.',
            'overlay_color_start' => '#0f172a',
            'overlay_color_end' => '#1e1b4b',
            'overlay_opacity_start' => 45,
            'overlay_opacity_end' => 90,
            'text_color' => '#ffffff',
            'is_fullwidth' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'q&a',
            'title' => 'Centro Oficial Q&A Torzo',
            'is_fullwidth' => true,
            'overlay_color_start' => '#0f172a',
            'overlay_color_end' => '#1e1b4b',
            'text_color' => '#ffffff',
        ]);

        // Verificar vista Storefront
        $viewResp = $this->get('/pagina/q&a');
        $viewResp->assertStatus(200);
        $viewResp->assertSee('Centro Oficial Q&A Torzo');
        $viewResp->assertSee('aeo-hero-box is-fullwidth', false);
        $viewResp->assertSee('aeo-hero-overlay', false);
    }
}
