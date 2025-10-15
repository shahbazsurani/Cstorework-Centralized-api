<?php

namespace Database\Factories\Reminders\Models;

use App\Reminders\Models\ReminderDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderDocumentFactory extends Factory
{
    protected $model = ReminderDocument::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->filePath();
        return [
            'storage_path' => 'reminders/documents/'.$this->faker->uuid.'.txt',
            'original_name' => basename($name),
            'mime_type' => 'text/plain',
            'size_bytes' => $this->faker->numberBetween(10, 10000),
            'uploaded_by' => User::factory(),
            'is_active' => true,
        ];
    }
}
