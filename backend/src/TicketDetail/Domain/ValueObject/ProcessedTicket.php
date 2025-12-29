<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\ValueObject;

final readonly class ProcessedTicket
{
    public function __construct(
        public int $ticketId,
        public string $status, // success|skipped|failed
        public ?string $filePath,
        public ?string $errorMessage,
    ) {
    }
}
