<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetPermission extends Model
{
    protected $fillable = ['user_id', 'widget_id', 'site_id', 'permissions'];

    protected $casts = [
        'permissions' => 'array', // Храним массив действий: ['view', 'edit', 'stats']
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Проверка наличия конкретного действия
     */
    public function can(string $action): bool
    {
        return in_array($action, $this->permissions ?? []);
    }
}
