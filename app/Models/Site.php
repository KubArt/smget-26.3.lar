<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    /**
     * Виджеты (модули), установленные на этом сайте
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }
}
