<?php

declare(strict_types=1);

return [
    'domain' => env('FRESHDESK_DOMAIN', ''),
    'api_key' => env('FRESHDESK_API_KEY', ''),
    'storage_path' => env('FRESHDESK_STORAGE_PATH', 'freshdesk'),
];
