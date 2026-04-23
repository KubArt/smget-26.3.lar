<?php
namespace App\Models;

use App\Models\Crm\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Site extends Model
{
    use Notifiable, SoftDeletes;
    protected $fillable = ['name', 'domain', 'email', 'api_key', 'is_active', 'is_verified', 'verified_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Автоматическая генерация UUID при создании сайта
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($site) {
            $site->api_key = (string) Str::uuid();
        });
    }

    /**
     * Все пользователи, имеющие доступ к сайту
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'site_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Владелец сайта (Owner)
     */
    public function owner()
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }


    // Тарифы и подписки для сайтов
    // Все подписки сайта (история)
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Billing\Subscription::class, 'site_id');
    }
    // Текущая активная подписка (одна)
    public function activeSubscription()
    {
        return $this->hasOne(\App\Models\Billing\Subscription::class)
            ->where('expires_at', '>', now())
            ->latestOfMany(); // Используем встроенный метод Laravel для последней записи
    }

    // Удобный аксессор для получения самого плана
    public function getPlanAttribute()
    {
        return $this->activeSubscription?->plan;
    }



    /**
     * Виджеты (модули), установленные на этом сайте
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }


}
