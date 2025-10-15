<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['is_active'];

    protected static function booted()
    {
        // Global scope to filter only active
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', 1);
        });

        // Auto-generate hash if column exists
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'hash')) {
                $model->hash = $model->hash ?? bin2hex(random_bytes(127)); // 254 characters
            }
        });
    }

    // Soft delete by setting is_active to 0
    public function delete()
    {
        $this->update(['is_active' => 0]);
    }

    // Use forceDelete() from Eloquent if you want actual deletion
}
