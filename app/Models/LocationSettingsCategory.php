<?php

namespace App\Models;

class LocationSettingsCategory extends BaseModel
{
    protected $hidden = ['id'];
    protected $fillable = ['location_id', 'parent_id', 'category_name', 'is_active'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function parent()
    {
        return $this->belongsTo(LocationSettingsCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LocationSettingsCategory::class, 'parent_id');
    }

    public function settings()
    {
        return $this->hasMany(LocationSetting::class, 'category_id');
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
