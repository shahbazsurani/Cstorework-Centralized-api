<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderStageHistory;
use App\Reminders\Models\ReminderItem;
use App\Reminders\Models\ReminderStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderStageHistoryFactory extends Factory
{
    protected $model = ReminderStageHistory::class;

    public function definition(): array
    {
        return [
            'item_id' => ReminderItem::factory(),
            'stage_id' => ReminderStage::factory(),
            'changed_by' => User::factory(),
            'changed_at' => Carbon::now('UTC'),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
