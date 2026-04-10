<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // Добавь 'role' в $fillable

    /**
     * Проверка на супер-админа
     */
    public function isGod(): bool
    {
        return $this->role === 'GOD';
    }

    /**
     * Сайты, к которым у пользователя есть доступ
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Все индивидуальные права пользователя на виджеты
     */
    public function widgetPermissions(): HasMany
    {
        return $this->hasMany(WidgetPermission::class);
    }

}
