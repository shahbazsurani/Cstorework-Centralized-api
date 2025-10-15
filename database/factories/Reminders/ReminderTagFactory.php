<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderTagFactory extends Factory
{
    protected $model = ReminderTag::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
