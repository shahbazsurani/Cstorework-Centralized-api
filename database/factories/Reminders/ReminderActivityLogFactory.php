<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderActivityLog;
use App\Reminders\Models\ReminderItem;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderActivityLogFactory extends Factory
{
    protected $model = ReminderActivityLog::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'item_id' => ReminderItem::factory(),
            'actor_user_id' => User::factory(),
            'action' => $this->faker->randomElement(['item.created','item.updated','item.deleted','item.completed','item.spawned','item.reopened']),
            'meta' => null,
            'created_at' => Carbon::now('UTC'),
        ];
    }
}
