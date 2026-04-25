<?php

namespace App\Models\Metrics;

use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteMetric extends Model
{
    protected $fillable = [
        'site_id',
        'type',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // ✅ Добавляем атрибуты по умолчанию
    protected $attributes = [
        'settings' => '{}',
        'is_active' => false,
    ];


    // ✅ Аксессор для безопасного получения настроек
    public function getSettingsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return is_array($value) ? $value : [];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
