<?php

namespace App\Reminders\Models;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReminderAccessGrant extends Model
{
    use HasFactory;

    protected $table = 'Reminder_AccessGrants';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
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

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grantor_user_id');
    }

    public function grantee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grantee_user_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReminderItem::class, 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
