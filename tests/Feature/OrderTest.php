<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsUser(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(2)->create();

        $this->actingAsUser()
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_list_orders_requires_authentication(): void
    {
        $this->getJson('/api/v1/orders')->assertStatus(401);
    }

    public function test_can_create_order(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/v1/orders', [
            'order_number' => 'ORD-0001',
            'status_id' => 1,
            'client_id' => $client->id,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.order_number', 'ORD-0001');
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD-0001']);
    }

    public function test_create_order_fails_with_duplicate_order_number(): void
    {
        $client = Client::factory()->create();
        Order::factory()->create(['order_number' => 'ORD-DUP']);

        $this->actingAsUser()
            ->postJson('/api/v1/orders', [
                'order_number' => 'ORD-DUP',
                'status_id' => 1,
                'client_id' => $client->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['order_number']);
    }

    public function test_create_order_fails_with_nonexistent_client(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/orders', [
                'order_number' => 'ORD-X',
                'status_id' => 1,
                'client_id' => 9999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_can_show_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAsUser()
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_can_update_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAsUser()
            ->putJson("/api/v1/orders/{$order->id}", ['status_id' => 2])
            ->assertOk()
            ->assertJsonPath('data.status_id', 2);
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/orders/{$order->id}")
            ->assertOk();

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
