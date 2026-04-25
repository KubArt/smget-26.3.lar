<?php

namespace App\Metrics\Contracts;

interface MetricDriverInterface
{
    public function sendEvent(string $event, array $params): void;
    public function syncGoals(array $goals): void;
}
