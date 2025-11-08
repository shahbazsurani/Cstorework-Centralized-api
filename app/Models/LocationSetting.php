<?php

namespace App\Models;

class LocationSetting extends BaseModel
{
    protected $hidden = ['id'];
    protected $fillable = ['category_id', 'key', 'value', 'is_active'];

    public function category()
    {
        return $this->belongsTo(LocationSettingsCategory::class, 'category_id');
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
