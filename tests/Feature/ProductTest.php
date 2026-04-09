<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsUser(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $this->actingAsUser()
            ->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_list_products_requires_authentication(): void
    {
        $this->getJson('/api/v1/products')->assertStatus(401);
    }

    public function test_can_create_product(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/products', [
            'name' => 'Widget',
            'description' => 'A useful widget',
            'price' => 9.99,
            'quantity' => 100,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'Widget');
        $this->assertDatabaseHas('products', ['name' => 'Widget']);
    }

    public function test_create_product_fails_without_required_fields(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/products', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'quantity']);
    }

    public function test_create_product_fails_with_negative_price(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/products', [
                'name' => 'Widget', 'description' => null, 'price' => -1, 'quantity' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAsUser()
            ->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAsUser()
            ->putJson("/api/v1/products/{$product->id}", ['quantity' => 999])
            ->assertOk()
            ->assertJsonPath('data.quantity', 999);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/products/{$product->id}")
            ->assertOk();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
