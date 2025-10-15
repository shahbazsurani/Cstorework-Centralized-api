<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderNotification;
use App\Reminders\Models\ReminderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderNotificationFactory extends Factory
{
    protected $model = ReminderNotification::class;

    public function definition(): array
    {
        return [
            'item_id' => ReminderItem::factory(),
            'channel' => $this->faker->randomElement(['email','sms','webhook']),
            'notify_at' => Carbon::now('UTC')->addDay(),
            'sent_at' => null,
            'status' => 'pending',
            'payload' => null,
        ];
    }
}
