<?php

namespace App\Models;


class Location extends BaseModel
{
    protected $hidden = ['id'];
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getRouteKeyName()
    {
        return 'hash';
    }
}
