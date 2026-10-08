<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\GeminiService;
use App\Services\Inventory\ProductAiCopilotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeminiAiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_gemini@mitienda.test'],
            [
                'name' => 'Admin Gemini Tester',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );
    }

    /**
     * Verifica que el servicio centralizado de Gemini está configurado y responde a la prueba de conexión.
     */
    public function test_gemini_service_is_configured_and_can_test_connection()
    {
        $gemini = app(GeminiService::class);

        $this->assertTrue($gemini->isConfigured(), 'Gemini API Key debe estar configurada en variables de entorno.');
        $this->assertNotEmpty($gemini->getModel());

        $status = $gemini->testConnection();
        $this->assertTrue($status['success'], 'La prueba de conexión con Google Gemini debe ser exitosa.');
        $this->assertNotEmpty($status['reply']);
        $this->assertGreaterThan(0, $status['latency_ms']);
    }

    /**
     * Verifica la generación de texto simple con Gemini.
     */
    public function test_gemini_service_generates_text()
    {
        $gemini = app(GeminiService::class);
        $text = $gemini->generateText('Responde únicamente la palabra: TIENDA_ACTIVA');

        $this->assertNotNull($text);
        $this->assertStringContainsString('TIENDA_ACTIVA', $text);
    }

    /**
     * Verifica la generación de JSON estructurado con Gemini.
     */
    public function test_gemini_service_generates_structured_json()
    {
        $gemini = app(GeminiService::class);
        $json = $gemini->generateJson('Genera un JSON con {"status": "success", "platform": "Mi Tienda Online"}');

        $this->assertIsArray($json);
        $this->assertEquals('success', $json['status'] ?? null);
    }

    /**
     * Verifica que el Copiloto IA de Producto genera contenido comercial, bullets y tags SEO con Gemini.
     */
    public function test_product_ai_copilot_service_uses_gemini_to_generate_commercial_copy()
    {
        $copilot = app(ProductAiCopilotService::class);

        $result = $copilot->generateCommercialContent([
            'name' => 'Boxer Trunks Modal Seamless',
            'category' => 'Boxers',
            'brand' => 'Torzo',
            'fabric_type' => 'Modal Seamless Ultra Soft',
            'color_name' => 'Negro Profundo',
            'design_type' => 'sin_diseno',
            'target_audience' => 'Marte',
            'pvp' => 289.00,
        ]);

        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['description']);
        $this->assertIsArray($result['bullet_points']);
        $this->assertGreaterThanOrEqual(2, count($result['bullet_points']));
        $this->assertIsArray($result['seo_tags']);
        $this->assertGreaterThanOrEqual(3, count($result['seo_tags']));
        $this->assertContains($result['metadata']['ai_provider'], ['gemini-flash', 'gemini-flash-lite-latest', 'gemini-flash-latest', 'heuristic_fallback']);
    }

    /**
     * Verifica el endpoint RESTful del Chat Estratégico del Copiloto IA en el Dashboard.
     */
    public function test_dashboard_copilot_chat_api_endpoint()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/dashboard/copilot/chat', [
                'message' => '¿Cuál es la estrategia recomendada para mejorar el margen EBITDA este mes?',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'reply',
            'action_chips',
            'ai_provider',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('reply'));

        $this->assertDatabaseHas('ai_copilot_chat_messages', [
            'sender' => 'user',
            'message' => '¿Cuál es la estrategia recomendada para mejorar el margen EBITDA este mes?',
        ]);

        $this->assertDatabaseHas('ai_copilot_chat_messages', [
            'sender' => 'assistant',
        ]);
    }

    /**
     * Verifica el endpoint de estado y telemetría de Gemini AI.
     */
    public function test_copilot_status_api_endpoint()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/dashboard/copilot/status');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'provider' => 'Google Generative AI (Gemini)',
                'is_connected' => true,
            ],
        ]);
        $this->assertGreaterThan(0, $response->json('data.latency_ms'));
    }
}
