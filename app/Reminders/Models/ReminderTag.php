<?php

namespace App\Reminders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Reminder_Tags';

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
