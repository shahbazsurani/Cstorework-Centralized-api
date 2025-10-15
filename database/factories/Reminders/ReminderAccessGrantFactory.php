<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderAccessGrant;
use App\Reminders\Models\ReminderItem;
use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderAccessGrantFactory extends Factory
{
    protected $model = ReminderAccessGrant::class;

    public function definition(): array
    {
        $hasItem = $this->faker->boolean();
        return [
            'grantor_user_id' => User::factory(),
            'grantee_user_id' => User::factory(),
            'item_id' => $hasItem ? ReminderItem::factory() : null,
            'location_id' => $hasItem ? null : Location::factory(),
            'permission' => $this->faker->randomElement(['view','comment','complete','manage']),
            'expires_at' => Carbon::now('UTC')->addDays(7),
            'is_active' => true,
        ];
    }
}
