<?php


namespace App\Http\Controllers\Cabinet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends BaseCabinetController
{
    public function index()
    {
        $user = auth()->user();
        return view('cabinet.profile.index', compact('user'));
    }

    // Обновление данных (телефон, имя, фамилия)

    public function update(Request $request)
    {
        $user = auth()->user();

        // Валидация с учетом новых полей
        $validated = $request->validate([
            'phone' => [
                'nullable',
                'string',
                'unique:users,phone,' . $user->id
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        // 1. Обновляем телефон в модели User
        $user->phone = $request->phone;
        $user->save();

        // 2. Обновляем профиль с новыми полями
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $validated['name'],
                'patronymic' => $validated['patronymic'],
                'last_name' => $validated['last_name'],
                'additional_info' => $request->input('additional_info', []),
            ]
        );

        return back()->with('success', 'Данные профиля обновлены');
    }

    // Смена пароля
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Пароль успешно изменен');
    }
}
