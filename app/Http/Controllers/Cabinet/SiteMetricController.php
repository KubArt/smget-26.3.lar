<?php


namespace App\Http\Controllers\Cabinet;

use App\Models\Site;
use App\Models\Metrics\SiteMetric;
use App\Metrics\MetricsManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SiteMetricController extends BaseCabinetController
{
    /**
     * Список всех доступных метрик
     */
    public function index(Site $site)
    {
        $this->authorizeAccess($site);

        // Список всех доступных метрик (хардкод или из конфига)
        $availableMetrics = config('metrics.available', [
            'yandex-metrika' => [
                'name' => 'Яндекс.Метрика',
                'icon' => 'fab fa-yandex',
                'description' => 'Отправка конверсий в Яндекс.Метрику',
                'driver' => \App\Metrics\Drivers\YandexMetrikaDriver::class
            ],
            /*
            'vk-pixel' => [
                'name' => 'VK Реклама',
                'icon' => 'fab fa-vk',
                'description' => 'VK Pixel для отслеживания конверсий',
                'driver' => \App\Metrics\Drivers\VkPixelDriver::class
            ],
            'google-analytics' => [
                'name' => 'Google Analytics 4',
                'icon' => 'fab fa-google',
                'description' => 'Отправка событий в GA4',
                'driver' => \App\Metrics\Drivers\GoogleAnalyticsDriver::class
            ]
            //*/
        ]);

        // Подключенные метрики для этого сайта
        $connectedMetrics = SiteMetric::where('site_id', $site->id)->get();

        return view('cabinet.sites.metrics.index', compact(
            'site',
            'availableMetrics',
            'connectedMetrics'
        ));
    }

    /**
     * Форма подключения/настройки метрики
     */
    public function configure(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $metricConfig = config("metrics.available.{$metricSlug}");

        if (!$metricConfig) {
            abort(404, 'Метрика не найдена');
        }

        // Ищем или создаем запись
        $siteMetric = SiteMetric::firstOrCreate([
            'site_id' => $site->id,
            'type' => $metricSlug
        ]);
        $syncedGoals = app(MetricsManager::class)->getPendingGoals($site);
        return view('cabinet.sites.metrics.config', compact(
            'site',
            'metricSlug',
            'metricConfig',
            'syncedGoals',
            'siteMetric'
        ));
    }

    /**
     * Сохранение настроек метрики
     */
    // В методе update исправить сохранение настроек
    public function update(Request $request, Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->firstOrFail();

        $validated = $request->validate([
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        // ✅ Важно: сохраняем settings как массив, даже если он пустой
        $currentSettings = $siteMetric->settings ?? [];
        $newSettings = $request->input('settings', []);

        // ✅ Специфичная валидация для разных метрик
        $this->validateMetricSettings($metricSlug, $newSettings);

        // ✅ Объединяем настройки
        $mergedSettings = array_merge($currentSettings, $newSettings);

        $siteMetric->update([
            'is_active' => $request->has('is_active'),
            'settings' => $mergedSettings  // ✅ Всегда сохраняем массив
        ]);

        // Если метрика активна - синхронизируем цели
        if ($siteMetric->is_active) {
            app(MetricsManager::class)->syncGoalsForSite($site, $metricSlug);
        }

        $metricConfig = config("metrics.available.{$metricSlug}");
        $metricName = $metricConfig['name'] ?? $metricSlug;

        return redirect()
            ->route('cabinet.sites.metrics.index', $site)
            ->with('success', "Настройки {$metricName} сохранены");
    }

    protected function validateMetricSettings(string $metricSlug, array $settings)
    {
        $rules = [
            'yandex-metrika' => [
                'counter_id' => 'nullable|string',  // ✅ Сделал nullable
                'token' => 'nullable|string'        // ✅ Сделал nullable
            ],
            'vk-pixel' => [
                'pixel_id' => 'nullable|string'
            ],
            'google-analytics' => [
                'measurement_id' => 'nullable|string',
                'api_secret' => 'nullable|string'
            ]
        ];

        if (isset($rules[$metricSlug])) {
            $validator = validator($settings, $rules[$metricSlug]);
            if ($validator->fails()) {
                abort(422, $validator->errors()->first());
            }
        }
    }

    /**
     * Отключение метрики
     */
    public function destroy(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->firstOrFail();

        $siteMetric->delete();

        return back()->with('success', 'Метрика отключена');
    }

    /**
     * Проверка подключения метрики
     */
    public function test(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->first();

        if (!$siteMetric || !$siteMetric->is_active) {
            return response()->json(['success' => false, 'message' => 'Метрика не активна']);
        }

        $manager = new MetricsManager();
        $result = $manager->testConnection($site, $metricSlug);

        return response()->json($result);
    }


    /**
     * Вынети в отдельный контроллер
     */

    /**
     * Редирект на OAuth провайдера
     */
    public function redirectToProvider(Request $request, Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);
        $metricConfig = config("metrics.available.{$metricSlug}");

        if (!$metricConfig || !isset($metricConfig['oauth'])) {
            abort(404, 'OAuth не настроен');
        }

        $oauth = $metricConfig['oauth'];

        session([
            'oauth_site_id' => $site->id,
            'oauth_metric_slug' => $metricSlug,
            'oauth_state' => $state = bin2hex(random_bytes(16))
        ]);

        $params = [
            'response_type' => 'code',
            'client_id'     => $oauth['client_id'],
            'redirect_uri'  => route('oauth.callback'), // Используем новый роут
            'state'         => $state,
        ];

        return redirect($oauth['auth_url'] . '?' . http_build_query($params));
    }

    public function handleProviderCallback(Request $request)
    {
        $siteId = session('oauth_site_id');
        $metricSlug = session('oauth_metric_slug');
        $savedState = session('oauth_state');

        if (!$siteId || !$metricSlug || $request->state !== $savedState) {
            return $this->closeWithPayload(['error' => 'Ошибка безопасности или сессия истекла']);
        }

        $site = Site::findOrFail($siteId);
        $metricConfig = config("metrics.available.{$metricSlug}");

        $tokenData = $this->exchangeCodeForToken($request->code, $metricConfig['oauth']);

        if (!$tokenData) {
            return $this->closeWithPayload(['error' => 'Не удалось получить токен']);
        }

        $siteMetric = SiteMetric::firstOrCreate(
            ['site_id' => $site->id, 'type' => $metricSlug],
            ['settings' => [], 'is_active' => false]
        );

        $settings = array_merge($siteMetric->settings ?? [], [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600)->toDateTimeString(),
        ]);

        // Работа со счетчиками
        if ($metricSlug === 'yandex-metrika') {
            $counters = $this->getYandexCounters($tokenData['access_token']);

            if (empty($counters)) {
                return $this->closeWithPayload(['error' => 'У вас нет доступных счетчиков в Яндекс.Метрике']);
            }

            // Сохраняем в сессию под ключом, который ожидает selectCounter
            session(["yandex_counters_{$site->id}" => $counters]);

            $siteMetric->update(['settings' => $settings]);

            // Если счетчик один — сразу привязываем
            if (count($counters) === 1) {
                return $this->autoBindSingleCounter($site, $siteMetric, $counters[0]);
            }

            // Если много — отправляем родительское окно на выбор
            return $this->closeWithPayload([
                'redirect' => route('cabinet.sites.metrics.select-counter', [$site, $metricSlug])
            ]);
        }

        return $this->closeWithPayload(['success' => true]);
    }

    private function autoBindSingleCounter($site, $siteMetric, $counter) {
        $settings = $siteMetric->settings;
        $settings['counter_id'] = $counter['id'];
        $siteMetric->update(['settings' => $settings, 'is_active' => true]);

        app(MetricsManager::class)->syncAllWidgetsWithMetric($site, $siteMetric);
        return $this->closeWithPayload(['success' => true]);
    }

    /**
     * Вспомогательный метод для закрытия Popup
     */
    private function closeWithPayload($data) {
        return view('cabinet.sites.metrics.oauth_close', compact('data'));
    }

    /**
     * Выбор счетчика (если их несколько)
     */
    public function selectCounter(Site $site, string $metricSlug)
    {
        $counters = session("yandex_counters_{$site->id}");

        if (!$counters) {
            return redirect()
                ->route('cabinet.sites.metrics.index', $site)
                ->with('error', 'Список счетчиков не найден');
        }

        return view('cabinet.sites.metrics.select-counter', compact('site', 'metricSlug', 'counters'));
    }

    /**
     * Сохранение выбранного счетчика
     */
    public function saveCounter(Request $request, Site $site, string $metricSlug)
    {
        $siteMetric = SiteMetric::where('site_id', $site->id)->where('type', $metricSlug)->firstOrFail();

        $settings = $siteMetric->settings;
        $settings['counter_id'] = $request->counter_id;

        $siteMetric->update([
            'settings' => $settings,
            'is_active' => true
        ]);

        // Вместо моментальной синхронизации получаем список целей
        $manager = app(MetricsManager::class);
        $goals = $manager->getPendingGoals($site);

        // Показываем страницу подтверждения
        return view('cabinet.sites.metrics.confirm-goals', compact('site', 'metricSlug', 'goals', 'siteMetric'));
    }
    public function finalSync(Request $request, Site $site, string $metricSlug)
    {
        $siteMetric = SiteMetric::where('site_id', $site->id)->where('type', $metricSlug)->firstOrFail();
        // Вот теперь вызываем созданный метод
        app(MetricsManager::class)->syncAllWidgetsWithMetric($site, $siteMetric);
        return redirect()->route('cabinet.sites.metrics.index', $site)
            ->with('success', 'Метрика подключена, цели созданы.');
    }
    public function OLD_saveCounter(Request $request, Site $site, string $metricSlug)
    {
        $request->validate([
            'counter_id' => 'required|string'
        ]);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->firstOrFail();

        $settings = $siteMetric->settings ?? [];
        $settings['counter_id'] = $request->counter_id;

        $siteMetric->update([
            'settings' => $settings,
            'is_active' => true
        ]);

        // Синхронизируем цели
        $metricsManager = app(MetricsManager::class);
        $metricsManager->syncAllWidgetsWithMetric($site, $siteMetric);

        session()->forget("yandex_counters_{$site->id}");

        return redirect()
            ->route('cabinet.sites.metrics.index', $site)
            ->with('success', 'Счетчик выбран, метрика активирована!');
    }

    /**
     * Обмен code на token
     */
    protected function exchangeCodeForToken(string $code, array $oauth): ?array
    {
        try {
            $response = Http::asForm()->post($oauth['token_url'], [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $oauth['client_id'],
                'client_secret' => $oauth['client_secret'],
                'redirect_uri' => $oauth['redirect_uri']
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            \Log::error('Token exchange failed', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('Token exchange exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение списка счетчиков Яндекс.Метрики
     */
    protected function getYandexCounters(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://api-metrika.yandex.net/management/v1/counters');

            if ($response->successful()) {
                $counters = $response->json()['counters'] ?? [];

                return array_map(function($counter) {
                    return [
                        'id' => $counter['id'],
                        'name' => $counter['name'],
                        'site' => $counter['site'] ?? '',
                        'status' => $counter['status'] ?? 'active'
                    ];
                }, $counters);
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('Get counters error: ' . $e->getMessage());
            return [];
        }
    }
}
