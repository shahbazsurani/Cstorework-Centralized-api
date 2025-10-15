<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderRecurringType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderRecurringTypeFactory extends Factory
{
    protected $model = ReminderRecurringType::class;

    public function definition(): array
    {
        $types = ['Daily','Weekly','Monthly','Yearly','Custom'];
        return [
            'type' => $this->faker->randomElement($types),
            'is_active' => true,
        ];
    }
}
