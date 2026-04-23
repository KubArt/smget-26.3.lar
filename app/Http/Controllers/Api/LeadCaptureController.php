<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration\SiteService;
use App\Models\Site;
use App\Models\Crm\Lead;
use App\Models\Crm\Client;
use App\Models\Crm\Prize;
use App\Models\Crm\PrizeAttempt;
use App\Services\Crm\Adapters\FortuneWheelAdapter;
use App\Services\Crm\Adapters\InternalWidgetAdapter;
use App\Services\Crm\Adapters\LidUpAdapter;
use App\Services\Crm\Adapters\TildaAdapter;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * API контроллер для приема лидов (заявок) из внешних источников
 *
 * ============================================================================
 * ПРИНЦИП РАБОТЫ
 * ============================================================================
 *
 * 1. Контроллер принимает POST запрос на endpoint: /api/v1/capture/{source}
 * 2. Аутентифицирует запрос по X-Api-Key заголовку или параметру token
 * 3. Выбирает адаптер в зависимости от source
 * 4. Адаптер парсит и валидирует входные данные
 * 5. Контроллер сохраняет данные в БД (Client, Lead, Prize)
 * 6. Возвращает JSON ответ с результатом
 *
 * ============================================================================
 * МАРШРУТЫ
 * ============================================================================
 *
 * POST /api/v1/capture/tilda          - для интеграции с Tilda
 * POST /api/v1/capture/widget         - для внутренних виджетов
 * POST /api/v1/capture/fortune-wheel  - для колеса фортуны
 *
 * ============================================================================
 * АУТЕНТИФИКАЦИЯ
 * ============================================================================
 *
 * Для СВОИХ ВИДЖЕТОВ (widget, fortune-wheel):
 *   X-Api-Key: widget
 *   Передается widget_id в теле запроса
 *   → Контроллер находит сайт по виджету
 *
 * Для ВНЕШНИХ ИНТЕГРАЦИЙ (tilda и др.):
 *   X-Api-Key: {api_key из таблицы site_services}
 *   → Контроллер проверяет интеграцию и находит сайт
 *
 * ============================================================================
 * ПОТОК ДАННЫХ
 * ============================================================================
 *
 * ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
 * │  Входные    │────▶│  Адаптер    │────▶│ Контроллер  │────▶│     БД      │
 * │  данные     │     │  (парсинг)  │     │ (сохранение)│     │             │
 * └─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
 *
 * АДАПТЕР:
 *   - Валидирует обязательные поля
 *   - Преобразует данные в единый формат
 *   - Может вернуть ошибку (поле 'error')
 *   - НЕ создает записи в БД
 *
 * КОНТРОЛЛЕР:
 *   - Создает/находит Клиента (Client)
 *   - Создает Лид (Lead)
 *   - Создает Приз (Prize) если адаптер передал prize_data
 *   - Формирует ответ
 *
 * ============================================================================
 * ФОРМАТ ВХОДНЫХ ДАННЫХ
 * ============================================================================
 *
 * Обязательные поля (хотя бы одно):
 *   - phone - телефон в любом формате (будет очищен)
 *   - email - email адрес
 *
 * Опциональные поля:
 *   - name - имя клиента (по умолчанию 'Аноним')
 *   - widget_id - ID виджета (для своих виджетов)
 *   - page_url - URL страницы отправки
 *   - utm_source, utm_medium, utm_campaign, utm_term, utm_content, utm_referrer - UTM метки
 *
 * Для виджетов с призами (fortune-wheel):
 *   - prize_data - массив с данными о призе
 *   - message - сообщение для пользователя
 *
 * ============================================================================
 * ФОРМАТ ОТВЕТА
 * ============================================================================
 *
 * УСПЕХ (201 Created):
 *   {
 *     "status": "success",
 *     "lead_id": 123,
 *     "message": "Заявка успешно отправлена",
 *     "prize": {                               // если был создан приз
 *       "code": "PROMO_ABC123",
 *       "name": "Скидка 10%",
 *       "description": "...",
 *       "expires_at": "2024-12-31T23:59:59.000000Z"
 *     }
 *   }
 *
 * ОШИБКА (4xx):
 *   {
 *     "error": "Описание ошибки",
 *     "code": "ERROR_CODE"
 *   }
 *
 * ============================================================================
 * КОДЫ ОШИБОК
 * ============================================================================
 *
 * 403 - Invalid authentication          - неверный токен аутентификации
 * 403 - Invalid widget authentication    - виджет не найден или неактивен
 * 422 - Phone or email is required       - не передан ни телефон, ни email
 * 422 - {code} из адаптера               - ошибка валидации адаптера
 *
 * ============================================================================
 * ТРЕБОВАНИЯ К БАЗЕ ДАННЫХ
 * ============================================================================
 *
 * Телефоны хранятся как bigint без спецсимволов (только цифры, + для международных)
 *   Пример: '+79991234567' или '79991234567'
 *
 * Таблицы:
 *   - clients        - клиенты (уникальны по phone+site_id или email+site_id)
 *   - leads          - лиды (заявки)
 *   - prizes         - призы (промокоды)
 *   - prize_attempts - лог попыток получения призов
 *
 * ============================================================================
 * ДОБАВЛЕНИЕ НОВОГО ИСТОЧНИКА
 * ============================================================================
 *
 * 1. Создать адаптер в App\Services\Crm\Adapters\
 * 2. Реализовать интерфейс LeadAdapterInterface
 * 3. Добавить в match-конструкцию в методе capture()
 * 4. При необходимости добавить аутентификацию в authenticate()
 *
 * ============================================================================
 * ПРИМЕР ЗАПРОСА (для колеса фортуны)
 * ============================================================================
 *
 * POST /api/v1/capture/fortune-wheel
 * Headers:
 *   X-Api-Key: widget
 *   Content-Type: application/json
 *
 * Body:
 *   {
 *     "widget_id": 6,
 *     "phone": "+79991234567",
 *     "name": "Иван",
 *     "page_url": "http://site.ru/gift",
 *     "prize_data": {
 *       "code": "DISCOUNT10",
 *       "name": "Скидка 10%",
 *       "expiry_days": 30
 *     },
 *     "utm_source": "google",
 *     "utm_campaign": "new_year"
 *   }
 *
 * ============================================================================
 */
class LeadCaptureController extends Controller
{
    /**
     * Основной метод приема лидов
     *
     * @param Request $request
     * @param string $source - источник (tilda, widget, fortune-wheel)
     * @return \Illuminate\Http\JsonResponse
     */
    public function capture(Request $request, $source)
    {
        // ====================================================================
        // ШАГ 1: АУТЕНТИФИКАЦИЯ
        // ====================================================================
        // Определяем сайт по токену или widget_id
        // Для своих виджетов: X-Api-Key: widget + widget_id в теле
        // Для внешних интеграций: X-Api-Key из таблицы site_services
        // ====================================================================
        $site = $this->authenticate($request, $source);
        if (!$site) {
            return response()->json(['error' => 'Invalid authentication'], 403);
        }

        // ====================================================================
        // ШАГ 2: ВЫБОР АДАПТЕРА
        // ====================================================================
        // Адаптер отвечает за:
        // - парсинг входных данных
        // - валидацию
        // - приведение к единому формату
        // - НЕ СОЗДАЕТ записи в БД
        // ====================================================================
        $adapter = match($source) {
            'tilda'         => new TildaAdapter(),
            'widget'        => new InternalWidgetAdapter(),
            'fortune-wheel' => new FortuneWheelAdapter(),
            'lidup'         => new LidUpAdapter(),  // ← добавляем
            default         => new InternalWidgetAdapter(),
        };

        $parsedData = $adapter->parse($request->all());

        // ====================================================================
        // ШАГ 3: ПРОВЕРКА ОШИБОК АДАПТЕРА
        // ====================================================================
        // Адаптер может вернуть массив с полем 'error'
        // HTTP код берется из 'http_code' (по умолчанию 422)
        // ====================================================================
        if (isset($parsedData['error'])) {
            return response()->json([
                'error' => $parsedData['error'],
                'code' => $parsedData['code'] ?? 'VALIDATION_ERROR'
            ], $parsedData['http_code'] ?? 422);
        }

        // ====================================================================
        // ШАГ 4: ОПРЕДЕЛЕНИЕ КОНТАКТА
        // ====================================================================
        // Приоритет: phone → email
        // Телефон очищается от спецсимволов
        // ====================================================================
        $contact = $this->extractContact($parsedData);
        if (!$contact) {
            return response()->json(['error' => 'Phone or email is required'], 422);
        }

        // ====================================================================
        // ШАГ 5: СОЗДАНИЕ/ПОИСК КЛИЕНТА
        // ====================================================================
        // Клиент уникален по (phone + site_id) или (email + site_id)
        // ====================================================================
        $client = $this->findOrCreateClient($site->id, $contact, $parsedData);

        // ====================================================================
        // ШАГ 6: СОЗДАНИЕ ЛИДА
        // ====================================================================
        // Лид - основная запись о заявке
        // Содержит все UTM метки, IP, User-Agent и т.д.
        // ====================================================================
        $lead = $this->createLead($site->id, $client->id, $parsedData, $request);

        // ====================================================================
        // ШАГ 7: СОЗДАНИЕ ПРИЗА (опционально)
        // ====================================================================
        // Если адаптер передал prize_data - создаем промокод
        // Приз привязывается к лиду и клиенту
        // ====================================================================
        $prize = null;
        if (isset($parsedData['prize_data'])) {
            $prize = $this->createPrize($site->id, $lead->id, $client->id, $parsedData);

            // Логируем успешное получение приза (для антифрода)
            if (!empty($parsedData['contact'])) {
                PrizeAttempt::log(
                    $site->id,
                    $parsedData['contact'],
                    $parsedData['prize_data']['code'] ?? null,
                    $parsedData['widget_id'] ?? null,
                    $prize->id,
                    true
                );
            }
        }

        // ====================================================================
        // ШАГ 8: ФОРМИРОВАНИЕ ОТВЕТА
        // ====================================================================
        // Возвращаем ID лида и данные о призе (если есть)
        // Сообщение берется из адаптера или стандартное
        // ====================================================================
        return $this->buildResponse($lead, $prize, $parsedData);
    }

    /**
     * Аутентификация запроса
     *
     * Два режима:
     * 1. Свои виджеты: X-Api-Key = 'widget', ищем сайт по widget_id из тела
     * 2. Внешние: X-Api-Key из таблицы site_services, проверяем интеграцию
     *
     * @return Site|null
     */
    protected function authenticate(Request $request, string $source): ?Site
    {
        $token = $request->header('X-Api-Key') ?? $request->token;

        // === РЕЖИМ 1: СВОИ ВИДЖЕТЫ ===
        if ($token === 'widget') {
            return $this->authenticateWidgetRequest($request);
        }

        // === РЕЖИМ 2: ВНЕШНИЕ ИНТЕГРАЦИИ ===
        $integration = SiteService::where('api_key', $token)
            ->where('is_enabled', true)
            ->with('site')
            ->first();

        if (!$integration || $integration->service->slug !== $source) {
            return null;
        }

        return $integration->site;
    }

    /**
     * Аутентификация для своих виджетов
     *
     * Проверяет существование виджета и возвращает его сайт
     *
     * @return Site|null
     */
    protected function authenticateWidgetRequest(Request $request): ?Site
    {
        $widgetId = $request->input('widget_id');
        if (!$widgetId) {
            return null;
        }

        $widget = \App\Models\Widget::with('site')->find($widgetId);
        return $widget?->site;
    }

    /**
     * Извлечение контакта из данных
     *
     * Приоритет: phone → email
     * Телефон очищается от спецсимволов (остаются только цифры и +)
     *
     * @return array|null ['type' => 'phone'|'email', 'value' => string]
     */
    protected function extractContact(array $data): ?array
    {
        // === ТЕЛЕФОН ===
        if (!empty($data['phone'])) {
            // Очищаем: оставляем только цифры и знак +
            $phone = preg_replace('/[^\d+]/', '', $data['phone']);
            return ['type' => 'phone', 'value' => format_phoneToInt($phone) ];
        }

        // === EMAIL ===
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['type' => 'email', 'value' => $data['email']];
        }

        return null;
    }

    /**
     * Поиск или создание клиента
     *
     * Клиент уникален по паре (phone + site_id) или (email + site_id)
     * Если клиент не найден - создается новый
     *
     * @param int $siteId
     * @param array $contact ['type' => 'phone'|'email', 'value' => string]
     * @param array $data
     * @return Client
     */
    protected function findOrCreateClient(int $siteId, array $contact, array $data): Client
    {
        $isBlocked = $this->checkIsBlocked($siteId);

        $searchField = $contact['type'];
        $searchValue = $contact['value'];

        $client = Client::where($searchField, $searchValue)
            ->where('site_id', $siteId)
            ->first();

        if (!$client) {
            $clientData = [
                'site_id' => $siteId,
                $searchField => $searchValue,
                'name' => $data['name'] ?? 'Аноним',
                'email' => $data['email'] ?? null,
                'is_blocked' => $isBlocked
            ];

            // Если нашли телефон, но есть и email - добавляем
            if ($contact['type'] === 'phone' && !empty($data['email'])) {
                $clientData['email'] = $data['email'];
            }

            $client = Client::create($clientData);
        }

        return $client;
    }

    /**
     * Создание лида
     *
     * Лид содержит:
     * - контактные данные (дублируются для быстрого поиска)
     * - UTM метки
     * - техническую информацию (IP, User-Agent, URL)
     * - исходные данные формы
     *
     * @return Lead
     */
    protected function createLead(int $siteId, int $clientId, array $data, Request $request): Lead
    {
        $isBlocked = $this->checkIsBlocked($siteId);


        $leadData = [
            'site_id'    => $siteId,
            'client_id'  => $clientId,
            'widget_id'  => $data['widget_id'] ?? null,
            'status'     => 'new',
            'source'     => $data['source'] ?? $request->route('source'),

            // Контактные данные (дублируем для быстрого поиска)
            'phone'      => format_phoneToInt($data['phone']) ?? null,
            'email'      => $data['email'] ?? null,

            // UTM метки
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term'     => $data['utm_term'] ?? null,
            'utm_content'  => $data['utm_content'] ?? null,
            'utm_referrer' => $data['utm_referrer'] ?? null,

            // Технические данные
            'page_url'   => $data['page_url'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),

            // Исходные данные (для отладки и аудита)
            'form_data'  => $data['form_data'] ?? $request->all(),
            'is_blocked' => $isBlocked
        ];

        return Lead::create($leadData);
    }

    /**
     * Создание приза (промокода)
     *
     * Вызывается только если адаптер передал prize_data
     * Генерирует уникальный код, сохраняет приз в БД
     *
     * @return Prize
     */
    protected function createPrize(int $siteId, int $leadId, int $clientId, array $data): Prize
    {
        $prizeData = $data['prize_data'];
        $expiryDays = $prizeData['expiry_days'] ?? 30;
        $expiresAt = $expiryDays > 0 ? now()->addDays($expiryDays) : null;

        // Генерируем уникальный промокод
        $uniqueCode = $this->generateUniqueCode(
            $prizeData['code'],
            $data['widget_id'] ?? 0
        );

        return Prize::create([
            'site_id' => $siteId,
            'lead_id' => $leadId,
            'client_id' => $clientId,
            'widget_id' => $data['widget_id'] ?? null,
            'code' => $uniqueCode,
            'name' => $prizeData['name'],
            'description' => $prizeData['description'] ?? null,
            'type' => 'discount',
            'meta' => [
                'original_code' => $prizeData['code'],
                'segment_data' => $prizeData['segment_data'] ?? null
            ],
            'expires_at' => $expiresAt,
            'is_active' => true,
            'is_limited' => $expiryDays > 0,
        ]);
    }

    /**
     * Формирование JSON ответа
     *
     * @param Lead $lead
     * @param Prize|null $prize
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildResponse(Lead $lead, ?Prize $prize, array $data): \Illuminate\Http\JsonResponse
    {
        // Базовый ответ
        $response = [
            'status' => 'success',
            'lead_id' => $lead->id,
            'message' => $data['message'] ?? 'Заявка успешно отправлена',
        ];

        // Добавляем данные о призе
        if ($prize) {
            $response['prize'] = [
                'code' => $prize->code,
                'name' => $prize->name,
                'description' => $prize->description,
                'expires_at' => $prize->expires_at?->toISOString(),
                'user_message' => $prize->message,
            ];
        }

        // Добавляем дополнительные данные из адаптера
        if (isset($data['extra'])) {
            $response = array_merge($response, $data['extra']);
        }

        return response()->json($response, 201);
    }

    /**
     * Генерация уникального промокода
     *
     * Формат: {PREFIX}_{RANDOM_6}
     * Гарантирует уникальность в таблице prizes
     *
     * @param string $baseCode - исходный код приза (например DISCOUNT10)
     * @param int $widgetId
     * @return string
     */
    protected function generateUniqueCode(string $baseCode, int $widgetId): string
    {
        // Извлекаем префикс из кода (удаляем спецсимволы)
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $baseCode));
        if (strlen($prefix) < 3) {
            $prefix = 'PROMO';
        }

        // Генерируем уникальный код
        do {
            $suffix = strtoupper(Str::random(6));
            $code = $prefix . '_' . $suffix;
        } while (Prize::where('code', $code)->exists());

        return $code;
    }


    /**
     * Логика определения: должен ли новый лид быть заблокирован
     */
    protected function checkIsBlocked(int $siteId): bool
    {
        // 1. Находим сайт. Активность проверяем через scope или where.
        $site = Site::findOrFail($siteId);

        // 2. Используем сервис (он уже умеет парсить JSON и знает дефолты)
        $subService = new \App\Services\SubscriptionService($site);
        $features = $subService->loadFeatures();
        // 3. Получаем подписку для определения временных рамок
        $subscription = $site->activeSubscription;
        // Если подписки нет — применяем жесткий лимит Free (5 лидов за всё время)
//*
        if (!$subscription) {
            return $site->leads()->count() >= 5;
        }
//*/
        // 4. Берем лимит из JSON.
        // Если в JSON "leads_limit" не задан, используем 0 (или другое безопасное число)
        $limit = isset($features['leads_limit']) ? (int)$features['leads_limit'] : 0;

        // 5. Проверка на "Безлимит" (если вы решите ставить -1 для бесконечности)
        if ($limit === -1) {
            return false;
        }

        // 6. Считаем лиды только за период действия текущей подписки
        // Это важно: когда клиент продлевает тариф, счетчик для него "обнуляется"
        $currentLeadsCount = $site->leads()
            ->where('created_at', '>=', $subscription->starts_at)
            ->count();

//        dd($currentLeadsCount,   $limit);

        return $currentLeadsCount >= $limit;
    }

}
