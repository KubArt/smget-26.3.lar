<?php

namespace App\Services\Crm\Adapters;

class InternalWidgetAdapter implements LeadAdapterInterface {
    public function parse(array $data): array {
        // Ищем телефон и почту во всем массиве данных
        $phone = $this->findField($data, ['phone', 'tel', 'contact', 'telephone', 'user_phone', 'user_tel']);
        $email = $this->findField($data, ['email', 'mail', 'user_email', 'e-mail']);
        $name  = $this->findField($data, ['name', 'fio', 'username', 'full_name']) ?? 'Аноним';

        return [
            'phone' => $phone,
            'email' => $email,
            'name'  => $name,
            // UTM-метки
            'utm_source'   => $data['utm_source'] ?? $data['extra']['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? $data['extra']['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? $data['extra']['utm_campaign'] ?? null,
            'utm_term'     => $data['utm_term'] ?? $data['extra']['utm_term'] ?? null,
            'utm_content'  => $data['utm_content'] ?? $data['extra']['utm_content'] ?? null,
            'utm_referrer' => $data['utm_referrer'] ?? $data['extra']['utm_referrer'] ?? null,
            // Технические данные
            'page_url'     => $data['page_url'] ?? $data['extra']['page_url'] ?? null,
            // Исходные данные формы
            'form_data'    => $data,
        ];
    }

    /**
     * Универсальный поиск поля по списку возможных ключей
     */
    protected function findField(array $data, array $needles) {
        foreach ($data as $key => $value) {
            // Переводим ключ в нижний регистр для надежности
            $lowKey = strtolower($key);
            foreach ($needles as $needle) {
                if (str_contains($lowKey, $needle)) {
                    return $value;
                }
            }
        }
        return null;
    }

}
