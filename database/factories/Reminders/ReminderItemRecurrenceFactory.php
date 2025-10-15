<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderItemRecurrence;
use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderRecurringType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderItemRecurrenceFactory extends Factory
{
    protected $model = ReminderItemRecurrence::class;

    public function definition(): array
    {
        return [
            'item_id' => ReminderItem::factory(),
            'recurring_type_id' => ReminderRecurringType::factory(),
            'interval_value' => $this->faker->numberBetween(1, 4),
            'anchor_date' => Carbon::now('UTC')->toDateString(),
            'end_date' => null,
            'timezone' => 'UTC',
            'is_active' => true,
        ];
    }
}
