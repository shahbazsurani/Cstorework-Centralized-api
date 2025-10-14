<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderItemRecurrence extends Model
{
    use HasFactory;

    protected $table = 'Reminder_ItemRecurrences';

    protected $guarded = [];

    protected $casts = [
        'interval_value' => 'integer',
        'anchor_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('is_active', function ($builder) {
            $builder->where($builder->getModel()->getTable().'.is_active', true);
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReminderItem::class, 'item_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReminderRecurringType::class, 'recurring_type_id');
    }
}
