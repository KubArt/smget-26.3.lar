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
use App\Services\Crm\Adapters\TildaAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadCaptureController extends Controller
{
    public function capture(Request $request, $source)
    {
        // 1. Аутентификация
        $token = $request->header('X-Api-Key') ?? $request->token;

        if ($token === 'widget') {
            $site = $this->authenticateWidgetRequest($request);
            if (!$site) {
                return response()->json(['error' => 'Invalid widget authentication'], 403);
            }
        } else {
            $integration = SiteService::where('api_key', $token)
                ->where('is_enabled', true)
                ->with(['site', 'service'])
                ->first();

            if (!$integration || $integration->service->slug !== $source) {
                return response()->json(['error' => 'Invalid or inactive integration token'], 403);
            }
            $site = $integration->site;
        }

        if (!$site) {
            return response()->json(['error' => 'Invalid API Key'], 403);
        }

        // 2. Выбор адаптера
        $adapter = match($source) {
            'tilda'         => new TildaAdapter(),
            'widget'        => new InternalWidgetAdapter(),
            'fortune-wheel' => new FortuneWheelAdapter(),
            default         => new InternalWidgetAdapter(),
        };

        $parsedData = $adapter->parse($request->all());

        // 3. Проверка на ошибку от адаптера
        if (isset($parsedData['error'])) {
            return response()->json([
                'error' => $parsedData['error'],
                'code' => $parsedData['code'] ?? 'VALIDATION_ERROR'
            ], $parsedData['http_code'] ?? 422);
        }

        // 4. Проверка обязательного поля phone (если есть)
        if ($source !== 'fortune-wheel' && empty($parsedData['phone'])) {
            return response()->json(['error' => 'Phone is required'], 422);
        }

        // 5. Создание клиента (если есть phone)
        $client = null;
        if (!empty($parsedData['phone'])) {
            $client = Client::firstOrCreate(
                ['phone' => $parsedData['phone'], 'site_id' => $site->id],
                [
                    'name' => $parsedData['name'] ?? 'Аноним',
                    'email' => $parsedData['email'] ?? null
                ]
            );
        }

        // 6. Создание лида
        $leadData = [
            'site_id'    => $site->id,
            'client_id'  => $client?->id,
            'widget_id'  => $parsedData['widget_id'] ?? null,
            'status'     => 'new',
            'source'     => $source,
            'phone'      => $parsedData['phone'] ?? null,
            'email'      => $parsedData['email'] ?? null,
            'utm_source'   => $parsedData['utm_source'] ?? null,
            'utm_medium'   => $parsedData['utm_medium'] ?? null,
            'utm_campaign' => $parsedData['utm_campaign'] ?? null,
            'utm_term'     => $parsedData['utm_term'] ?? null,
            'utm_content'  => $parsedData['utm_content'] ?? null,
            'utm_referrer' => $parsedData['utm_referrer'] ?? null,
            'page_url'   => $parsedData['page_url'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'form_data'  => $parsedData['form_data'] ?? $request->all(),
        ];

        // Добавляем данные ваучера, если есть
        if (isset($parsedData['vaucher_name'])) {
            $leadData['vaucher_name'] = $parsedData['vaucher_name'];
            $leadData['vaucher_code'] = $parsedData['vaucher_code'];
            $leadData['vaucher_end_date'] = $parsedData['vaucher_end_date'];
            $leadData['vaucher_is_active'] = $parsedData['vaucher_is_active'] ?? true;
        }

        $lead = Lead::create($leadData);

        // 7. Создание приза (если адаптер вернул данные для приза)
        $prize = null;
        if (isset($parsedData['prize_data'])) {
            $prizeData = $parsedData['prize_data'];
            $expiryDays = $prizeData['expiry_days'] ?? 30;
            $expiresAt = $expiryDays > 0 ? now()->addDays($expiryDays) : null;

            // Генерируем уникальный промокод
            $uniqueCode = $this->generateUniqueCode($prizeData['code'], $parsedData['widget_id'] ?? 0);

            $prize = Prize::create([
                'site_id' => $site->id,
                'lead_id' => $lead->id,
                'widget_id' => $parsedData['widget_id'] ?? null,
                'client_id' => $client?->id,
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

            // Логируем успешную попытку (если есть контакт)
            if (!empty($parsedData['contact'])) {
                PrizeAttempt::log(
                    $site->id,
                    $parsedData['contact'],
                    $prizeData['code'],
                    $parsedData['widget_id'] ?? null,
                    $prize->id,
                    true
                );
            }

            // Обновляем лид с ID приза
            $lead->update(['prize_id' => $prize->id]);
        }

        // 8. Формирование ответа
        $response = [
            'status' => 'success',
            'lead_id' => $lead->id
        ];

        // Добавляем данные приза в ответ (если есть)
        if ($prize) {
            $response['prize'] = [
                'code' => $prize->code,
                'name' => $prize->name,
                'description' => $prize->description,
                'expires_at' => $prize->expires_at?->toISOString(),
            ];
        }

        // Добавляем дополнительные данные из адаптера
        if (isset($parsedData['extra'])) {
            $response = array_merge($response, $parsedData['extra']);
        }

        // Специальное сообщение для fortune-wheel
        if ($source === 'fortune-wheel' && isset($parsedData['success_message_template'])) {
            $message = str_replace('{CODE}', $prize?->code ?? '', $parsedData['success_message_template']);
            $message = str_replace('{NAME}', $prize?->name ?? '', $message);
            $response['message'] = $message;
        }

        return response()->json($response, 201);
    }

    /**
     * Аутентификация для своих виджетов
     */
    protected function authenticateWidgetRequest(Request $request): ?Site
    {
        $widgetId = $request->input('widget_id');
        if (!$widgetId) {
            return null;
        }

        $widget = \App\Models\Widget::with('site')->find($widgetId);
        if (!$widget || !$widget->site) {
            return null;
        }

        return $widget->site;
    }

    /**
     * Генерация уникального промокода
     */
    protected function generateUniqueCode(string $baseCode, int $widgetId): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $baseCode));
        if (strlen($prefix) < 3) {
            $prefix = 'PROMO';
        }

        $suffix = strtoupper(Str::random(6));
        $code = $prefix . '_' . $suffix;

        while (Prize::where('code', $code)->exists()) {
            $suffix = strtoupper(Str::random(6));
            $code = $prefix . '_' . $suffix;
        }

        return $code;
    }
}
