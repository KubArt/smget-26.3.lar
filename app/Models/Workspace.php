<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = [
        'name',
        'owner_id', // ID главного владельца
        'slug',     // Уникальный идентификатор для URL или API
    ];

    /**
     * Главный владелец кабинета (создатель)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Все сотрудники этого кабинета (включая владельца)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    /**
     * Сайты, принадлежащие этому кабинету
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
