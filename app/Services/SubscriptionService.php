<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Crm\Lead;
use Carbon\Carbon;

class SubscriptionService
{
    protected $site;
    protected $features;

    public function __construct(Site $site)
    {
        $this->site = $site;
        // Загружаем лимиты один раз при инициализации
        $this->features = $this->loadFeatures();
    }

    /**
     * Загружает фичи из активного плана или дает дефолты
     */
    public function loadFeatures(): array
    {
        $plan = $this->site->activeSubscription?->plan;
        return $plan ? $plan->features : [
            'leads_limit' => 5,
            'widgets_limit' => 1,
            'allowed_widget_types' => ['simple_form'],
            'hide_contacts' => true
        ];
    }

    /**
     * Проверка: можно ли создать новый лид (не превышен ли лимит)
     */
    public function canCaptureNewLead(): bool
    {

        $limit = $this->features['leads_limit'] ?? 0;
        $currentCount = $this->site->leads()->count(); // Или счетчик за период
        return $currentCount < $limit;
    }

    /**
     * Проверка: разрешен ли конкретный тип виджета по тарифу
     */
    public function isWidgetTypeAllowed(string $type): bool
    {
        return in_array($type, $this->features['allowed_widget_types'] ?? []);
    }

    /**
     * Проверка: не достигнут ли общий лимит по количеству виджетов
     */
    public function canCreateMoreWidgets(): bool
    {
        $limit = $this->features['widgets_limit'] ?? 0;
        return $this->site->widgets()->count() < $limit;
    }

    /**
     * Сводка по всем ограничениям
     */
    public function getLimitsSummary(): array
    {
        return [
            'leads' => [
                'current' => $this->site->leads()->count(),
                'limit' => $this->features['leads_limit'] ?? 0,
                'is_exceeded' => $this->site->leads()->count() >= ($this->features['leads_limit'] ?? 0),
            ],
            'widgets' => [
                'current' => $this->site->widgets()->count(),
                'limit' => $this->features['widgets_limit'] ?? 0,
                'is_exceeded' => $this->site->widgets()->count() >= ($this->features['widgets_limit'] ?? 0),
            ],
            'features' => $this->features
        ];
    }


    /*

    Где и как ты будешь его использовать:
        1. В LeadCaptureController (при создании лида):
        PHP

        $subService = new SubscriptionService($site);
        $isBlocked = !$subService->canCaptureNewLead();

        Lead::create([..., 'is_blocked' => $isBlocked]);

        2. В API для фронтенда (отображение виджета на сайте):

        Перед тем как отдать конфиг виджета скрипту, проверяем:
        PHP

        if (!$subService->isWidgetTypeAllowed($widget->type)) {
            return response()->json(['error' => 'Widget disabled by plan'], 403);
        }

        3. В интерфейсе кабинета (редактирование):

        В контроллере виджетов, прежде чем открыть страницу редактирования или нажать "Сохранить":
        PHP

        public function edit(Site $site, Widget $widget) {
            $subService = new SubscriptionService($site);

            return view('widgets.edit', [
                'widget' => $widget,
                'isReadOnly' => !$subService->isWidgetTypeAllowed($widget->type)
            ]);
        }

    //*/
}
