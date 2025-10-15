<?php

namespace Tests\Feature\Api\Locations;

use App\Enums\Role;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ActsAs;
use Tests\TestCase;

class LocationsCrudAndScopeTest extends TestCase
{
    use RefreshDatabase, ActsAs;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/locations')->assertStatus(401);
    }

    public function test_index_requires_allowed_role(): void
    {
        $user = $this->loginAsRole(Role::ReadOnly->value);
        $this->getJson('/api/locations')->assertStatus(403);
    }

    public function test_superadmin_can_list_all_locations(): void
    {
        $this->loginAsRole(Role::SuperAdmin->value);
        Location::factory()->count(3)->create();

        $this->getJson('/api/locations')
            ->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_location_admin_sees_only_assigned_locations(): void
    {
        $locA = Location::factory()->create(['name' => 'A']);
        $locB = Location::factory()->create(['name' => 'B']);

        $user = $this->loginAsRole(Role::LocationAdmin->value, [$locA->id]);

        $this->getJson('/api/locations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertSee('A')
            ->assertDontSee('B');
    }

    public function test_superadmin_can_create_update_delete_location(): void
    {
        $this->loginAsRole(Role::SuperAdmin->value);

        // Create
        $create = $this->postJson('/api/locations', [
            'name' => 'Loc X',
            'address' => 'Addr',
        ])->assertStatus(201)->json('data');

        $hash = $create['hash'];
        $this->assertNotEmpty($hash);

        // Update
        $this->putJson("/api/locations/{$hash}", [
            'name' => 'Loc Y',
        ])->assertOk()->assertJsonPath('data.name', 'Loc Y');

        // Delete (soft via is_active=0)
        $this->deleteJson("/api/locations/{$hash}")
            ->assertOk();

        $this->assertDatabaseHas('locations', [
            'hash' => $hash,
            'is_active' => 0,
        ]);
    }

    public function test_location_admin_cannot_view_unassigned_location(): void
    {
        $locA = Location::factory()->create(['name' => 'A']);
        $locB = Location::factory()->create(['name' => 'B']);

        $user = $this->loginAsRole(Role::LocationAdmin->value, [$locA->id]);

        $this->getJson("/api/locations/{$locB->hash}")
            ->assertStatus(403);
    }
}
