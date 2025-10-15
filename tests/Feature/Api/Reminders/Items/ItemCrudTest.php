<?php

namespace Tests\Feature\Api\Reminders\Items;

use App\Enums\Role;
use App\Models\Location;
use App\Reminders\Models\ReminderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\ActsAs;
use Tests\TestCase;

class ItemCrudTest extends TestCase
{
    use RefreshDatabase, ActsAs;

    public function test_items_index_requires_auth(): void
    {
        $this->getJson('/api/reminders/items')->assertStatus(401);
    }

    public function test_can_create_show_update_delete_item(): void
    {
        $this->loginAsRole(Role::ReadWrite->value);
        $loc = Location::factory()->create();

        // Validation
        $this->postJson('/api/reminders/items', [])->assertStatus(422);

        // Create
        $payload = [
            'location_id' => $loc->id,
            'name' => 'My Task',
            'description' => 'Desc',
            'due_at' => Carbon::now('UTC')->addDay()->toIso8601String(),
        ];
        $created = $this->postJson('/api/reminders/items', $payload)
            ->assertStatus(201)
            ->json();

        $hash = $created['hash'];
        $this->assertNotEmpty($hash);

        // Show
        $this->getJson("/api/reminders/items/{$hash}")
            ->assertOk()
            ->assertJsonPath('hash', $hash)
            ->assertJsonPath('name', 'My Task');

        // Update
        $this->putJson("/api/reminders/items/{$hash}", [
            'name' => 'Renamed',
        ])->assertOk()->assertJsonPath('name', 'Renamed');

        // Complete and Reopen
        $this->postJson("/api/reminders/items/{$hash}/complete")
            ->assertOk()
            ->assertJsonPath('is_completed', true);

        $this->postJson("/api/reminders/items/{$hash}/reopen")
            ->assertOk()
            ->assertJsonPath('is_completed', false);

        // Delete
        $this->deleteJson("/api/reminders/items/{$hash}")
            ->assertOk();

        $this->assertSoftDeleted('Reminder_Items', ['hash' => $hash]);
    }
}
