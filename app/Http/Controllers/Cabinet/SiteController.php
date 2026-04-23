<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SiteController extends BaseCabinetController
{

    public function index()
    {

        $workspace = auth()->user()->currentWorkspace();

        $sites = $workspace->sites()
            ->with(['activeSubscription.plan', 'widgets.widgetType'])
            ->withCount(['notifications as unread_count' => function($query) {
                $query->whereDoesntHave('readStates', function($q) {
                    $q->where('user_id', auth()->id());
                });
            }])->get();

        return view('cabinet.sites.index', compact('sites', 'workspace'));
    }


    public function show(Site $site)
    {
        $this->authorizeAccess($site);

        // Подгружаем данные подписки для детальной страницы
        $site->load(['activeSubscription.plan']);
        $limits = (new SubscriptionService($site))->getLimitsSummary();

        return view('cabinet.sites.show', compact('site', 'limits'));
    }

    public function edit(Site $site)
    {
        $this->authorizeAccess($site);
        return view('cabinet.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $this->authorizeAccess($site);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        $site->update($validated);
        return redirect()->route('cabinet.sites.index')->with('success', 'Настройки сайта обновлены');
    }


    /**
     * Форма создания нового сайта
     */
    public function create()
    {
        return view('cabinet.sites.create');
    }

    /**
     * Сохранение сайта в базе
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255', // желательно добавить уникальность или проверку формата
            'email' => 'required|email',
        ]);

        // Получаем текущий кабинет пользователя
        $workspace = auth()->user()->currentWorkspace();

        if (!$workspace) {
            return back()->with('error', 'Рабочий кабинет не найден. Пожалуйста, обратитесь в поддержку.');
        }

        // Создаем сайт, привязанный к кабинету
        $site = $workspace->sites()->create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'email' => $validated['email'],
            'is_active' => true,
        ]);

        // Опционально: если вы все еще используете таблицу site_user для ролей
        $site->users()->attach(auth()->id(), ['role' => 'owner']);

        return redirect()->route('cabinet.sites.show', $site)
            ->with('success', 'Проект успешно создан в кабинете: ' . $workspace->name);
    }



    public function verify($id)
    {
        $site = auth()->user()->sites()->findOrFail($id);

        // Формируем URL (убеждаемся, что есть протокол)
        $url = str_starts_with($site->domain, 'http') ? $site->domain : 'https://' . $site->domain;

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful() && str_contains($response->body(), $site->api_key)) {
                $site->update([
                    'is_verified' => true,
                    'verified_at' => now()
                ]);
                return back()->with('success', 'Права на домен подтверждены!');
            }

            return back()->with('error', 'API ключ не найден в исходном коде сайта.');
        } catch (\Exception $e) {
            return back()->with('error', 'Не удалось достучаться до сайта: ' . $e->getMessage());
        }
    }
    public function verifyAjax(Site $site)
    {
        // Проверка прав доступа через пивот
        if (!$site->users->contains(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $url = str_starts_with($site->domain, 'http') ? $site->domain : 'https://' . $site->domain;

        try {
            // Таймаут 10 секунд, чтобы не вешать сервер
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);

            if ($response->successful() && str_contains($response->body(), $site->api_key)) {
                $site->update([
                    'is_verified' => true,
                    'verified_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Код успешно обнаружен! Сайт подтвержден.',
                    'verified_at' => $site->verified_at->format('d.m.Y H:i')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Код с вашим API-ключом не найден на главной странице.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка подключения к сайту. Проверьте доступность домена.'
            ]);
        }
    }

    public function notifications(Site $site)
    {
        $this->authorizeAccess($site);

        $notifications = $site->notifications()
            // Проверяем, существует ли запись о прочтении этим пользователем
            ->withExists(['readStates as is_read_by_me' => function($query) {
                $query->where('user_id', auth()->id());
            }])
            ->latest()
            ->paginate(20);

        return view('cabinet.sites.notifications', compact('site', 'notifications'));
    }

}
