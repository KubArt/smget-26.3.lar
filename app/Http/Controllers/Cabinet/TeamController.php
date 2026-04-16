<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamController extends \App\Http\Controllers\Cabinet\BaseCabinetController
{
    public function index()
    {

        $workspace = auth()->user()->currentWorkspace();
        //$members = $workspace->users()->withPivot('role')->get();
        $members = $workspace->users()->withPivot('role')->with(['profile'])->get();

        return view('cabinet.team.index', compact('workspace', 'members'));
    }

    public function store(Request $request)
    {
        $workspace = auth()->user()->currentWorkspace();

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,specialist',
        ]);

        // 1. Создаем основного пользователя
        $user = \App\Models\User::create([
            'name' => $validated['name'], // Оставляем для совместимости системы
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'USER',
        ]);

        // 2. Создаем или обновляем профиль с ФИО
        $user->profile()->create([
            'last_name' => $validated['last_name'],
            'name' => $validated['name'],
            'patronymic' => $validated['patronymic'],
        ]);

        // 3. Привязываем к кабинету
        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $validated['role']]
        ]);

        return back()->with('success', 'Сотрудник успешно создан и добавлен в кабинет');
    }

    public function destroy(User $user)
    {
        $workspace = auth()->user()->currentWorkspace();

        if ($user->id === $workspace->owner_id) {
            return back()->with('error', 'Нельзя удалить владельца кабинета');
        }

        $workspace->users()->detach($user->id);
        return back()->with('success', 'Сотрудник удален из кабинета');
    }
}
