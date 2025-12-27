<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\Response;

use DateTimeInterface;

final readonly class SaveTicketDetailResponse
{
    public function __construct(
        public int $ticketId,
        public string $status, // 'success'|'partial'|'failed'
        public string $filePath,
        public DateTimeInterface $savedAt,
        public ?string $errorMessage = null,
    ) {
    }
}
