<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsUser(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_can_list_suppliers(): void
    {
        Supplier::factory()->count(3)->create();

        $response = $this->actingAsUser()->getJson('/api/v1/suppliers');

        $response->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_list_suppliers_requires_authentication(): void
    {
        $this->getJson('/api/v1/suppliers')->assertStatus(401);
    }

    public function test_can_create_supplier(): void
    {
        $response = $this->actingAsUser()
            ->postJson('/api/v1/suppliers', ['name' => 'ACME Corp']);

        $response->assertStatus(201)->assertJsonPath('data.name', 'ACME Corp');
        $this->assertDatabaseHas('suppliers', ['name' => 'ACME Corp']);
    }

    public function test_create_supplier_fails_without_name(): void
    {
        $this->actingAsUser()
            ->postJson('/api/v1/suppliers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAsUser()
            ->getJson("/api/v1/suppliers/{$supplier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $supplier->id);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAsUser()
            ->putJson("/api/v1/suppliers/{$supplier->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAsUser()
            ->deleteJson("/api/v1/suppliers/{$supplier->id}")
            ->assertOk();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
