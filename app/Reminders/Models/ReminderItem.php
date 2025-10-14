<?php

namespace App\Reminders\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Reminder_Items';

    protected $guarded = [];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
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

        // Global scope for active records
        static::addGlobalScope('is_active', function ($builder) {
            $builder->where($builder->getModel()->getTable().'.is_active', true);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'hash';
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ReminderSubtask::class, 'item_id');
    }

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(ReminderStage::class, 'Reminder_ItemStage', 'item_id', 'stage_id')->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ReminderTag::class, 'Reminder_ItemTag', 'item_id', 'tag_id')->withTimestamps();
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(ReminderDocument::class, 'Reminder_ItemDocument', 'item_id', 'document_id')->withTimestamps();
    }

    public function recurrences(): HasMany
    {
        return $this->hasMany(ReminderItemRecurrence::class, 'item_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ReminderNotification::class, 'item_id');
    }
}
