<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration\SiteService;
use App\Models\Site;
use App\Models\Crm\Lead;
use App\Models\Crm\Client;
use App\Services\Crm\Adapters\InternalWidgetAdapter;
use App\Services\Crm\Adapters\TildaAdapter;
use Illuminate\Http\Request;

class LeadCaptureController extends Controller
{
    public function capture(Request $request, $source)
    {
        // Ищем конкретную интеграцию по токену в URL или заголовке
        $token = $request->header('X-Api-Key') ?? $request->token;

        $integration = SiteService::where('api_key', $token)
            ->where('is_enabled', true)
            ->with(['site', 'service'])
            ->first();

        if (!$integration || $integration->service->slug !== $source) {
            return response()->json(['error' => 'Invalid or inactive integration token'], 403);
        }

        $site = $integration->site;

        if (!$site) {
            return response()->json(['error' => 'Invalid API Key'], 403);
        }

        // 2. Выбираем адаптер в зависимости от источника в URL {source}

            $adapter = match($source) {
                'tilda'  => new TildaAdapter(),
                'widget' => new InternalWidgetAdapter(),
                default  => new InternalWidgetAdapter(),
            };

        $parsedData = $adapter->parse($request->all());

        if (empty($parsedData['phone'])) {
            return response()->json(['error' => 'Phone is required'], 422);
        }

        // 3. Логика Client (склейка)
        $client = Client::firstOrCreate(
            ['phone' => $parsedData['phone'], 'site_id' => $site->id],
            ['name' => $parsedData['name'], 'email' => $parsedData['email'] ?? null]
        );

        dd($parsedData);

        // 4. Создание Лида
        // Создание лида со всеми мета-данными
        $lead = Lead::create([
            'site_id'    => $site->id,
            'client_id'  => $client->id,
            'status'     => 'new',
            'source'     => $source,

            // Контактные данные
            'phone'      => $parsedData['phone'],
            'email'      => $parsedData['email'],

            // UTM-метки
            'utm_source'   => $parsedData['utm_source'],
            'utm_medium'   => $parsedData['utm_medium'],
            'utm_campaign' => $parsedData['utm_campaign'],
            'utm_term'     => $parsedData['utm_term'],
            'utm_content'  => $parsedData['utm_content'],
            'utm_referrer' => $parsedData['utm_referrer'],

            // Локация и устройство (определяются автоматически)
            'page_url'   => $parsedData['page_url'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),

            // Исходный массив данных
            'form_data'  => $parsedData['form_data'],
        ]);

        return response()->json([
            'status' => 'success',
            'lead_id' => $lead->id
        ], 201);
    }
}
