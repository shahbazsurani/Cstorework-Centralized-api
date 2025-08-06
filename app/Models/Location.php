<?php

namespace App\Models;


class Location extends BaseModel
{
    protected $hidden = ['id'];
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'location_user');
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
