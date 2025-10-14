<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReminderActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false; // we'll manage created_at manually

    protected $table = 'Reminder_ActivityLogs';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->hash)) {
                $model->hash = (string) Str::ulid();
            }
            // ensure created_at exists
            if (empty($model->created_at)) {
                $model->created_at = now('UTC');
            }
        });
    }
}
