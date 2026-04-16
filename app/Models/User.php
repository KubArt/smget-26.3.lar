<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Billing\Transaction;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'phone',
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
     * Аксессор и мутатор для телефона
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
        // Геттер: преобразует 79189678793 в +7 (918) 967-87-93
            get: function ($value) {
            if (!$value) return $value;

            // Очищаем на случай, если в базе всё же затесался мусор
            $value = preg_replace('/[^0-9]/', '', $value);

            // Если номер соответствует формату (11 цифр)
            if (strlen($value) == 11) {
                return sprintf(
                    '+%s (%s) %s-%s-%s',
                    substr($value, 0, 1),
                    substr($value, 1, 3),
                    substr($value, 4, 3),
                    substr($value, 7, 2),
                    substr($value, 9, 2)
                );
            }

            return $value;
        },
            // Сеттер: всегда сохраняет в базу только цифры
            set: fn ($value) => preg_replace('/[^0-9]/', '', $value),
        );
    }

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
    public function sites()
    {
        return $this->belongsToMany(\App\Models\Site::class, 'site_user', 'user_id', 'site_id')
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

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // Добавляем поле баланса виртуально или через миграцию table->integer('balance')->default(0)):
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function getBalanceAttribute() {
        return $this->transactions()->sum('amount');
    }


    // Добавить в relations
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    /**
     * Кабинеты, которыми владеет пользователь
     */
    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Получить текущий рабочий кабинет (логика может быть через сессию)
     */
    public function currentWorkspace()
    {
        // Временная логика: возвращаем первый доступный или личный
        return $this->workspaces()->first();
    }

}
