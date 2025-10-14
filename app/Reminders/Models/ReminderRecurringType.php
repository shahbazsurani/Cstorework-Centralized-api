<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderRecurringType extends Model
{
    use HasFactory;

    protected $table = 'Reminder_RecurringTypes';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('is_active', function ($builder) {
            $builder->where($builder->getModel()->getTable().'.is_active', true);
        });
    }
}
