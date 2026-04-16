<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class SiteService extends Model
{
    protected $fillable = ['site_id', 'service_id', 'api_key', 'settings', 'is_enabled'];

    protected $casts = [
        'settings' => 'array',
        'is_enabled' => 'boolean'
    ];

    // app/Models/Crm/SiteService.php
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->api_key)) {
                // Генерируем уникальный префикс + случайную строку
                $model->api_key = 'ss_' . bin2hex(random_bytes(16));
            }
        });
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
