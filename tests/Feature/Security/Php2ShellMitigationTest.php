<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Php2ShellMitigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_request_with_php_session_upload_progress_in_post_body(): void
    {
        $response = $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'cart_test_123',
            'PHP_SESSION_UPLOAD_PROGRESS' => 'exploit_payload_session_id',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'detail' => 'Vector de inyección de progreso de sesión bloqueado por política de seguridad.',
        ]);
    }

    public function test_blocks_request_with_php_session_upload_progress_in_query_string(): void
    {
        $response = $this->getJson('/api/v1/store/products?PHP_SESSION_UPLOAD_PROGRESS=evil_trigger');

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'detail' => 'Vector de inyección de progreso de sesión bloqueado por política de seguridad.',
        ]);
    }

    public function test_blocks_request_with_malformed_excessive_multipart_boundary(): void
    {
        $excessiveBoundary = str_repeat('A', 300);

        $response = $this->call(
            'POST',
            '/api/v1/store/cart/items',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => "multipart/form-data; boundary={$excessiveBoundary}",
                'HTTP_ACCEPT' => 'application/json',
            ],
            "--{$excessiveBoundary}--\r\n"
        );

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'detail' => 'Cabecera de contenido multipart no permitida por política de integridad.',
        ]);
    }

    public function test_allows_legitimate_application_requests_without_interference(): void
    {
        $response = $this->get('/up');
        $response->assertStatus(200);
    }

    public function test_allows_legitimate_file_upload_without_false_positive(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('producto_muestra.png', 400, 400);

        // Hacemos una llamada multipart normal sin PHP_SESSION_UPLOAD_PROGRESS
        $response = $this->post('/api/v1/store/cart/items', [
            'cart_id' => 'cart_valid_999',
            'attachment' => $file,
        ]);

        // No debe ser 403 (será 422 si falta validación de campos del carrito, o 200/otros, pero NUNCA 403)
        $this->assertNotSame(403, $response->getStatusCode());
    }
}
