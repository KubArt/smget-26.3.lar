<?php


namespace App\Http\Controllers\Cabinet;

use App\Models\Integration\Service;
use App\Models\Integration\SiteService;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteIntegrationController extends BaseCabinetController
{
    /**
     * Список всех сервисов (Маркетплейс интеграций)
     */
    public function index(Site $site)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета

        // Получаем все активные сервисы из справочника
        $services = Service::where('is_active', true)->get();

        // Получаем уже подключенные сервисы для этого сайта
        $connectedServices = SiteService::where('site_id', $site->id)
            ->pluck('service_id')
            ->toArray();

        return view('cabinet.sites.integrations.index', compact('site', 'services', 'connectedServices'));
    }

    /**
     * Подключение/настройка конкретного сервиса
     */
    public function configure(Site $site, Service $service)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета
        // Ищем существующую связь или создаем новую
        $siteService = SiteService::firstOrCreate(
            ['site_id' => $site->id, 'service_id' => $service->id]
        );

        return view('cabinet.sites.integrations.config', compact('site', 'service', 'siteService'));
    }

    /**
     * Сохранение настроек интеграции (например, API ключа стороннего сервиса)
     */
    public function update(Request $request, Site $site, Service $service)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета

        $siteService = SiteService::where('site_id', $site->id)
            ->where('service_id', $service->id)
            ->firstOrFail();

        $validated = $request->validate([
            'api_key' => 'nullable|string|max:255',
            'is_enabled' => 'boolean'
        ]);

        $siteService->update([
            'api_key' => $validated['api_key'] ?? $siteService->api_key,
            'is_enabled' => $request->has('is_enabled')
        ]);

        return redirect()
            ->route('cabinet.sites.integrations.index', $site)
            ->with('success', "Интеграция с {$service->name} обновлена");
    }

    public function regenerateToken(Site $site, Service $service)
    {
        $siteService = SiteService::where('site_id', $site->id)
            ->where('service_id', $service->id)
            ->firstOrFail();

        // Генерируем новый уникальный токен
        $siteService->update([
            'api_key' => 'ss_' . bin2hex(random_bytes(16))
        ]);

        return redirect()
            ->route('cabinet.sites.integrations.config', [$site, $service])
            ->with('success', "Токен интеграции с {$service->name} успешно обновлен. Не забудьте обновить URL в настройках стороннего сервиса!");
    }

}
