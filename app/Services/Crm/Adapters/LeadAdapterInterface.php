<?php

namespace App\Services\Crm\Adapters;

interface LeadAdapterInterface {
    public function parse(array $data): array;
}
