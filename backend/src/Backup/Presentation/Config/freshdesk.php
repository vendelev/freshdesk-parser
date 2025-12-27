<?php

declare(strict_types=1);

return [
    'api_key' => env('FRESHDESK_API_KEY'),
    'domain' => env('FRESHDESK_DOMAIN'),
    'backup_storage_path' => env('BACKUP_STORAGE_PATH', 'storage/backups'),
];
