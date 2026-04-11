<?php

return [
    'paths' => ['api/*', 'v1/*', 'widgets/*'], // Добавь все пути, по которым стучится скрипт
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Для разработки ставим звездочку (разрешить всем)
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Поменяй на true, так как у тебя ругается на куки
];
