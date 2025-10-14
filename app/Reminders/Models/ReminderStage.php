<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReminderStage extends Model
{
    use HasFactory;

    protected $table = 'Reminder_Stages';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->hash)) {
                $model->hash = (string) Str::ulid();
            }
            if (! isset($model->is_active)) {
                $model->is_active = true;
            }
        });

        static::addGlobalScope('is_active', function ($builder) {
            $builder->where($builder->getModel()->getTable().'.is_active', true);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'hash';
    }
}
