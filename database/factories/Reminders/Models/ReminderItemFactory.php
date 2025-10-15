<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderItem;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderItemFactory extends Factory
{
    protected $model = ReminderItem::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'name' => 'Task: '.$this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'due_at' => Carbon::now('UTC')->addDays(rand(0, 10)),
            'is_completed' => false,
            'is_active' => true,
        ];
    }
}
