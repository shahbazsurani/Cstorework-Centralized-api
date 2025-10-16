<?php

namespace App\Models;

class Application extends BaseModel
{
    protected $fillable = [
        'slug',
        'name',
        'url_local',
        'url_production',
        'url_staging',
        'login_path',
        'target_param',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function baseUrlForEnv(?string $env = null): ?string
    {
        $env = $env ?: config('app.env');
        $env = strtolower((string) $env);
        return match ($env) {
            'production', 'prod' => $this->url_production ?: $this->url_local,
            'staging', 'stage', 'stg' => $this->url_staging ?: ($this->url_production ?: $this->url_local),
            default => $this->url_local ?: ($this->url_production ?: $this->url_staging),
        };
    }
}
