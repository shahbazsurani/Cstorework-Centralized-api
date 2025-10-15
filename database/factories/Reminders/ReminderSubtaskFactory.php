<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderSubtask;
use App\Reminders\Models\ReminderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderSubtaskFactory extends Factory
{
    protected $model = ReminderSubtask::class;

    public function definition(): array
    {
        return [
            'item_id' => ReminderItem::factory(),
            'name' => 'Subtask: '.$this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'is_completed' => false,
            'is_active' => true,
        ];
    }
}
