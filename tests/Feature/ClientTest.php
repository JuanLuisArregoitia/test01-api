<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsUser(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    // ─── Index ──────────────────────────────────────────────────

    public function test_can_list_clients(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->actingAsUser()
            ->getJson('/api/v1/clients');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_list_clients_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/clients');

        $response->assertStatus(401);
    }

    // ─── Store ──────────────────────────────────────────────────

    public function test_can_create_client(): void
    {
        $response = $this->actingAsUser()
            ->postJson('/api/v1/clients', [
                'name' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane@example.com',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('clients', ['email' => 'jane@example.com']);
    }

    public function test_create_client_fails_with_duplicate_email(): void
    {
        Client::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAsUser()
            ->postJson('/api/v1/clients', [
                'name' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_client_fails_without_required_fields(): void
    {
        $response = $this->actingAsUser()
            ->postJson('/api/v1/clients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'lastname', 'email']);
    }

    // ─── Show ───────────────────────────────────────────────────

    public function test_can_show_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()
            ->getJson("/api/v1/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $client->id);
    }

    public function test_show_returns_404_for_missing_client(): void
    {
        $response = $this->actingAsUser()
            ->getJson('/api/v1/clients/999');

        $response->assertStatus(404);
    }

    // ─── Update ─────────────────────────────────────────────────

    public function test_can_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()
            ->putJson("/api/v1/clients/{$client->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Name']);
    }

    public function test_update_email_unique_ignores_own_email(): void
    {
        $client = Client::factory()->create(['email' => 'own@example.com']);

        $response = $this->actingAsUser()
            ->putJson("/api/v1/clients/{$client->id}", [
                'email' => 'own@example.com',
            ]);

        $response->assertOk();
    }

    // ─── Destroy ────────────────────────────────────────────────

    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()
            ->deleteJson("/api/v1/clients/{$client->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Client deleted successfully']);

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_delete_returns_404_for_missing_client(): void
    {
        $response = $this->actingAsUser()
            ->deleteJson('/api/v1/clients/999');

        $response->assertStatus(404);
    }
}
