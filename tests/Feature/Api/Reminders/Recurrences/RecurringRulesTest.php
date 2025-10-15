<?php

namespace Tests\Feature\Api\Reminders\Recurrences;

use App\Enums\Role;
use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderRecurringType;
use App\Reminders\Models\ReminderItemRecurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\ActsAs;
use Tests\TestCase;

class RecurringRulesTest extends TestCase
{
    use RefreshDatabase, ActsAs;

    public function test_daily_recurrence_spawns_next_on_complete(): void
    {
        $this->loginAsRole(Role::ReadWrite->value);

        $due = Carbon::create(2025, 10, 14, 12, 0, 0, 'UTC');
        $item = ReminderItem::factory()->create([
            'due_at' => $due,
        ]);

        $type = ReminderRecurringType::firstOrCreate(['type' => 'Daily'], ['is_active' => true]);
        ReminderItemRecurrence::create([
            'item_id' => $item->id,
            'recurring_type_id' => $type->id,
            'interval_value' => 1,
            'anchor_date' => $due->toDateString(),
            'end_date' => null,
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $res = $this->postJson("/api/reminders/items/{$item->hash}/complete")
            ->assertOk()
            ->json();

        // Original item is completed
        $this->assertTrue($res['is_completed']);

        // A new item should exist with due_at +1 day
        $this->assertDatabaseCount('Reminder_Items', 2);

        $next = ReminderItem::query()->where('id', '!=', $item->id)->first();
        $this->assertNotNull($next);
        $this->assertEquals($due->copy()->addDay()->toIso8601String(), optional($next->due_at)?->toIso8601String());
    }
}
