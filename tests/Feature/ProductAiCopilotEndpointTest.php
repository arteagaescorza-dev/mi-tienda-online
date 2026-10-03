<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAiCopilotEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_description_endpoint_can_be_called()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/v1/inventory/products/generate-description', [
            'name' => 'Bóxer Modal Seamless Azul',
            'category' => 'Boxers',
            'brand' => 'Torzo',
            'fabric_type' => 'Modal',
            'color_name' => 'Azul',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'description',
                'bullet_points',
                'seo_tags',
            ],
        ]);
        $this->assertEquals('success', $response->json('status'));
        $this->assertNotEmpty($response->json('data.description'));
        $this->assertIsArray($response->json('data.bullet_points'));
        $this->assertIsArray($response->json('data.seo_tags'));
    }

    public function test_generate_description_unauthenticated_behavior()
    {
        $response = $this->postJson('/api/v1/inventory/products/generate-description', [
            'name' => 'Bóxer Modal Seamless Azul',
        ]);

        dump($response->status(), $response->getContent());
        // We will see what status code unauthenticated returns
    }
}
