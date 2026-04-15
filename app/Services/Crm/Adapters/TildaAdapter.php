<?php

namespace App\Services\Crm\Adapters;

class TildaAdapter implements LeadAdapterInterface {
    public function parse(array $data): array {
        // Тильда шлет данные в своем формате (напр. 'Phone', 'Name')
        return [
            'phone' => $data['Phone'] ?? $data['phone'] ?? null,
            'name'  => $data['Name'] ?? 'Лид с Тильды',
            'payload' => $data, // Сохраняем всё что пришло
        ];
    }
}
