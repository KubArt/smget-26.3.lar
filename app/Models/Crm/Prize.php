<?php

namespace App\Models\Crm;

use App\Models\Site;
use App\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Collection\Collection;

class Prize extends Model
{
    protected $table = 'prizes';

    protected $fillable = [
        'site_id', 'lead_id', 'client_id', 'widget_id',
        'code', 'name', 'description', 'type', 'meta',
        'expires_at', 'activated_at', 'used_at',
        'is_active', 'is_used', 'is_limited',
        'used_by_contact', 'used_by_ip'
    ];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'used_at' => 'datetime',
        'is_active' => 'boolean',
        'is_used' => 'boolean',
        'is_limited' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Проверка, активен ли приз
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->is_used) return false;
        if ($this->expires_at && $this->expires_at < now()) return false;

        return true;
    }

    /**
     * Активировать приз для клиента
     */
    public function activateForClient(Client $client): bool
    {
        if (!$this->isValid()) return false;

        $this->update([
            'client_id' => $client->id,
            'activated_at' => now(),
            'is_active' => true,
        ]);

        return true;
    }

    /**
     * Использовать приз
     */
    public function markAsUsed(string $contact, ?string $ip = null): bool
    {
        if (!$this->isValid()) return false;

        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_by_contact' => $contact,
            'used_by_ip' => $ip,
        ]);

        return true;
    }

    /**
     * Получить активный приз по коду
     */
    public static function findByCode(string $code, int $siteId): ?self
    {
        return self::where('code', $code)
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->where('is_used', false)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Получить все активные призы клиента
     */
    public static function getActiveForClient(int $clientId): Collection
    {
        return self::where('client_id', $clientId)
            ->where('is_used', false)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();
    }
}
