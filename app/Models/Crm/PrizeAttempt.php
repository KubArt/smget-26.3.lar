<?php

namespace App\Models\Crm;

use App\Models\Site;
use App\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeAttempt extends Model
{
    protected $table = 'prize_attempts';

    protected $fillable = [
        'prize_id', 'site_id', 'widget_id',
        'contact', 'prize_code', 'is_success',
        'error_code', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'is_success' => 'boolean',
    ];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Проверка лимита попыток для контакта
     */
    public static function getAttemptsCount(int $siteId, string $contact, ?int $widgetId = null, int $hours = 24): int
    {
        $query = self::where('site_id', $siteId)
            ->where('contact', $contact)
            ->where('created_at', '>', now()->subHours($hours));

        if ($widgetId) {
            $query->where('widget_id', $widgetId);
        }

        return $query->count();
    }

    /**
     * Создать запись о попытке
     */
    public static function log(int $siteId, string $contact, string $prizeCode,
                               ?int $widgetId = null, ?int $prizeId = null,
                               bool $isSuccess = false, ?string $errorCode = null): self
    {
        return self::create([
            'site_id' => $siteId,
            'widget_id' => $widgetId,
            'prize_id' => $prizeId,
            'contact' => $contact,
            'prize_code' => $prizeCode,
            'is_success' => $isSuccess,
            'error_code' => $errorCode,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
