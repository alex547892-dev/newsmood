<?php
// config.php
return [
    'db_path' => __DIR__ . '/database.sqlite',
    'ai_api' => [
        'key' => 'ВАШ_API_КЛЮЧ_ЗДЕСЬ',
        'url' => 'https://api.z.ai/api/coding/paas/v4/chat/completions',
        'model' => 'glm-4.5-Flash',
    ],
    'gnews_api_key' => 'ВАШ_GNEWS_API_КЛЮЧ_ЗДЕСЬ',
];