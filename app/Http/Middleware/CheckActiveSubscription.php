<?php
namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->route('site');

        if (is_string($site)) {
            // Загружаем сайт вместе с активной подпиской и планом за один запрос
            $site = Site::with('activeSubscription.plan')->find($site);
        }

        if ($site) {
            // 1. Получаем объект подписки или создаем "пустой" объект для Free-режима
            $subscription = $site->activeSubscription;

            // 2. Извлекаем фичи. Если подписки нет — берем дефолты для бесплатного тарифа
            $features = $subscription && $subscription->plan
                ? $subscription->plan->features
                : $this->getDefaultFreeFeatures();

            // 3. ПРИКРЕПЛЯЕМ к объекту сайта динамические свойства
            // Теперь в любом месте кода можно будет вызвать $site->plan_features
            $site->setAttribute('plan_features', $features);
            $site->setAttribute('is_subscribed', (bool)$subscription);

            // Обновляем модель в маршруте, чтобы контроллер получил уже "заряженный" объект
            $request->route()->setParameter('site', $site);
        }

        return $next($request);
    }

    /**
     * Дефолтные лимиты для сайтов без оплаты
     */
    protected function getDefaultFreeFeatures(): array
    {
        return [
            'leads_limit' => 5,
            'hide_contacts' => true,
            'allowed_widgets' => ['form_simple'],
            'has_crm_tasks' => false
        ];
    }

}
