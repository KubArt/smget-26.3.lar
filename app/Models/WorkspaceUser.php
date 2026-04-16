<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkspaceUser extends Pivot
{
    // Указываем, что это Pivot для корректной работы связей
    protected $table = 'workspace_user';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',        // admin, manager, specialist
        'permissions', // JSON с детальными правами
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Проверка наличия конкретного права у сотрудника в этом кабинете
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin' || $this->role === 'owner') {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }
}
