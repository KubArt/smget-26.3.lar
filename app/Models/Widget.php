<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Widget extends Model
{
    protected $fillable = ['site_id', 'type', 'name', 'settings', 'is_enabled'];

    protected $casts = [
        'settings' => 'array', // Laravel сам сделает json_decode/encode
        'is_enabled' => 'boolean',
    ];

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
