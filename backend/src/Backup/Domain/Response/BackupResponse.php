<?php

declare(strict_types=1);

namespace Parser\Backup\Domain\Response;

final readonly class BackupResponse
{
    public function __construct(
        public string $status,
        public string $message,
        public ?int $totalTickets = null,
        public ?string $filePath = null,
        public ?string $errorDetails = null,
    ) {
    }
}
