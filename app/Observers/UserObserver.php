<?php
namespace App\Observers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Срабатывает сразу после создания нового пользователя
     */
    public function created(User $user): void
    {
        /**
         * УСЛОВИЕ: Если пользователь уже привязан к какому-либо кабинету
         * (например, был приглашен как сотрудник по email),
         * то личный кабинет по умолчанию не создаем.
         */
        if ($user->workspaces()->exists()) {
            return;
        }

        // 1. Создаем персональный кабинет (только для самостоятельной регистрации)
        $workspace = Workspace::create([
            'name' => 'Мой кабинет',
            'owner_id' => $user->id,
            'slug' => Str::slug($user->email) . '-' . Str::random(5),
        ]);

        // 2. Привязываем пользователя к кабинету с ролью владельца
        $workspace->users()->attach($user->id, [
            'role' => 'owner',
            'permissions' => json_encode(['*']), // Полный доступ
        ]);
    }
}
