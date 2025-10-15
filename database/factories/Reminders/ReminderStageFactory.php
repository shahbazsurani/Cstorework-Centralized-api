<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderStageFactory extends Factory
{
    protected $model = ReminderStage::class;

    public function definition(): array
    {
        return [
            'name' => 'Stage '.$this->faker->unique()->word(),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
