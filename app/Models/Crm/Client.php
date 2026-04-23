<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Site;

class Client extends Model
{
    protected $fillable = [
        'site_id',
        'phone',
        'name',
        'last_name',
        'patronymic',
        'email',
        'is_blocked'
    ];
    protected $casts = [
        'is_blocked' => 'boolean'
    ];
    /**
     * Сайт, к которому привязан клиент
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Все обращения (лиды) данного клиента
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Глобальные заметки о клиенте
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class, 'client_id');
    }
}
