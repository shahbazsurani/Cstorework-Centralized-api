<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderNotification extends Model
{
    use HasFactory;

    protected $table = 'Reminder_Notifications';

    protected $guarded = [];

    protected $casts = [
        'notify_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReminderItem::class, 'item_id');
    }
}
