<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderStageHistory extends Model
{
    use HasFactory;

    protected $table = 'Reminder_StageHistory';

    protected $guarded = [];
}
