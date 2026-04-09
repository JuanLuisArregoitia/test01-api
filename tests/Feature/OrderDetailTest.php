<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderDetailTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsUser(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_can_list_order_details(): void
    {
        OrderDetail::factory()->count(2)->create();

        $this->actingAsUser()
            ->getJson('/api/v1/order-details')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_list_order_details_requires_authentication(): void
    {
        $this->getJson('/api/v1/order-details')->assertStatus(401);
    }

    public function test_can_create_order_detail(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/v1/order-details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 49.99,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.price', '49.99');
        $this->assertDatabaseHas('order_details', ['order_id' => $order->id, 'product_id' => $product->id]);
    }

    public function test_create_order_detail_fails_without_required_fields(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/order-details', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'product_id', 'price']);
    }

    public function test_create_order_detail_fails_with_nonexistent_order(): void
    {
        $product = Product::factory()->create();

        $this->actingAsUser()
            ->postJson('/api/v1/order-details', [
                'order_id' => 9999,
                'product_id' => $product->id,
                'price' => 10.00,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_can_show_order_detail(): void
    {
        $detail = OrderDetail::factory()->create();

        $this->actingAsUser()
            ->getJson("/api/v1/order-details/{$detail->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $detail->id);
    }

    public function test_can_update_order_detail(): void
    {
        $detail = OrderDetail::factory()->create();

        $this->actingAsUser()
            ->putJson("/api/v1/order-details/{$detail->id}", ['price' => 99.99])
            ->assertOk()
            ->assertJsonPath('data.price', '99.99');
    }

    public function test_can_delete_order_detail(): void
    {
        $detail = OrderDetail::factory()->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/order-details/{$detail->id}")
            ->assertOk();

        $this->assertSoftDeleted('order_details', ['id' => $detail->id]);
    }
}
