<?php

namespace App\Models;

use App\Models\Widgets\WidgetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Widget extends Model
{
    protected $fillable = [
        'site_id',
        'widget_type_id', // Добавлено
        'type',
        'name',
        'custom_name',
        'privacy_config',
        'target_paths',
        'target_utm',
        'settings',
        'behavior',
        'target_time',
        'is_active' // Если переименовали в миграции
    ];

    protected $casts = [
        'settings' => 'array',
        'privacy_config' => 'array',
        'target_utm' => 'array',
        'target_paths' => 'array',
        'target_time' => 'array',
        'behavior' => 'array',
        'is_active' => 'boolean',
    ];

    public function widgetType(): BelongsTo
    {
        return $this->belongsTo(WidgetType::class, 'widget_type_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Права пользователей на этот конкретный виджет
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(WidgetPermission::class);
    }
}
